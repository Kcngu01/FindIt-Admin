<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemMatch;
use App\Services\MatchNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageSimilarityController extends Controller
{
    // URL to your FastAPI service - would typically be in .env
    private $fastApiUrl = "https://Kcngu01-FindIt-api.hf.space"; // Update with actual Colab URL
    // private $fastApiUrl = "https://huggingface.co/spaces/Kcngu01/FindIt-api";
    private $hfToken; // Will be loaded from .env

    
    /**
     * MatchNotificationService for sending notifications
     */
    private $notificationService;

    /**
     * Constructor to inject dependencies
     */
    public function __construct(MatchNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->hfToken = env('HUGGINGFACE_API_TOKEN'); // Load from .env
    }
    
    /**
     * Process new item image for similarity matching
     * 
     * @param  Request  $request
     * @param  Item  $item
     * @return array
     */
    public function processItemImage(Request $request, Item $item)
    {
        try {
            // Validate image exists
            if (!$request->hasFile('image')) {
                Log::info('No image provided for similarity processing');
                
                // If this is a lost item, send a "no matches" notification immediately
                if ($item->type === 'lost') {
                    $this->notificationService->sendNoMatchesNotification($item);
                }
                
                return [
                    'success' => true,
                    'message' => 'No image provided for similarity processing',
                    'embedding' => null,
                    'matches' => []
                ];
            }
            
            // Get the image
            $file = $request->file('image');
            $itemType = $request->input('type');
            
            // Find related items for comparison (same category, location, and color, but different type)
            $relatedItems = Item::where('category_id', $request->input('category_id'))
                                ->where('location_id', $request->input('location_id'))
                                ->where('color_id', $request->input('color_id'))
                                ->where('type', '!=', $itemType) // Different type (lost vs found)
                                ->where('status', 'active')
                                ->whereNotNull('image_embeddings')
                                ->get();
            
            // If no related items, just get the patch embedding
            if ($relatedItems->isEmpty()) {
                Log::info('No related items found for comparison');
                
                try {
                    $embedding = $this->getImageEmbedding($file);
                } catch (\Exception $e) {
                    Log::warning('Failed to get image embedding, proceeding without it', [
                        'error' => $e->getMessage()
                    ]);
                    $embedding = null;
                }
                
                // If this is a lost item, send a "no matches" notification
                if ($item->type === 'lost') {
                    $this->notificationService->sendNoMatchesNotification($item);
                }
                
                return [
                    'success' => true,
                    'message' => 'No related items found for comparison',
                    'embedding' => $embedding,
                    'matches' => []
                ];
            }
            
            // Prepare embeddings for related items
            $storedEmbeddings = $relatedItems->map(function ($item) {
                return [
                    'item_id' => $item->id,
                    'embedding' => $item->image_embeddings,
                    'category_id' => $item->category_id,
                    'color_id' => $item->color_id,
                    'location_id' => $item->location_id,
                ];
            })->toArray();
            
            try {
                // Make request to FastAPI service with patch embeddings support
                $imageBase64 = $this->encodeImage($file);
                $matches = $this->compareSimilarity($imageBase64, $storedEmbeddings);
                
                // Create match records and send notifications if needed
                if (isset($matches['matches']) && !empty($matches['matches'])) {
                    $this->createMatches($item, $matches['matches']);
                } else if ($item->type === 'lost') {
                    // If this is a lost item and no matches, send notification
                    $this->notificationService->sendNoMatchesNotification($item);
                }
                
                return $matches;
            } catch (\Exception $e) {
                Log::warning('Failed to compare image similarity, falling back to basic matching', [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback: Use basic category, color and location matching without image similarity
                $fallbackMatches = [
                    'success' => true,
                    'message' => 'Using basic matching without image similarity due to service unavailability',
                    'embedding' => null,
                    'matches' => []
                ];
                
                // If this is a lost item, send a "no matches from image" notification
                if ($item->type === 'lost') {
                    $this->notificationService->sendNoMatchesNotification($item);
                    Log::info('No-match notification sent due to service unavailability', ['item_id' => $item->id]);
                }
                
                return $fallbackMatches;
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing image similarity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing image similarity: ' . $e->getMessage(),
                'embedding' => null,
                'matches' => []
            ];
        }
    }
    
    /**
     * Create matches in database based on similarity results
     *
     * @param  Item  $item
     * @param  array  $matches
     * @return void
     */
    public function createMatches(Item $item, array $matches)
    {
        try {
            $createdMatches = [];
            
            // Iterate through matches
            foreach ($matches as $match) {
                $matchData = [
                    'similarity_score' => $match['similarity'],
                ];
                
                // Set lost item and found item based on types
                if ($item->type === 'lost') {
                    $matchData['lost_item_id'] = $item->id;
                    $matchData['found_item_id'] = $match['item_id'];
                    
                    // Check if this lost item is involved in any pending matches or claims
                    $hasPendingMatches = \App\Models\ItemMatch::where('lost_item_id', $item->id)
                        ->whereIn('status', ['pending'])
                        ->exists();
                        
                    $hasPendingClaims = \App\Models\Claim::where('lost_item_id', $item->id)
                        ->whereIn('status', ['pending'])
                        ->exists();
                    
                    // Set status based on whether there are pending matches or claims
                    $matchData['status'] = ($hasPendingMatches || $hasPendingClaims) ? 'dismissed' : 'available';
                    
                } else {
                    // If this is a found item, check if the lost item it's being matched with has pending matches/claims
                    $matchData['lost_item_id'] = $match['item_id'];
                    $matchData['found_item_id'] = $item->id;
                    
                    // Check if the lost item is involved in any pending matches or claims
                    $hasPendingMatches = \App\Models\ItemMatch::where('lost_item_id', $match['item_id'])
                        ->whereIn('status', ['pending'])
                        ->exists();
                        
                    $hasPendingClaims = \App\Models\Claim::where('lost_item_id', $match['item_id'])
                        ->whereIn('status', ['pending'])
                        ->exists();
                    
                    // Set status based on whether there are pending matches or claims
                    $matchData['status'] = ($hasPendingMatches || $hasPendingClaims) ? 'dismissed' : 'available';
                }
                
                // Create the match record
                $createdMatch = ItemMatch::create($matchData);
                $createdMatches[] = $createdMatch;
                
                // Send notification to the student who reported the lost item
                $this->notificationService->sendMatchNotification($createdMatch);
            }
            
            Log::info('Created ' . count($matches) . ' match records for item #' . $item->id);
            
            return $createdMatches;
            
        } catch (\Exception $e) {
            Log::error('Error creating match records', [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get image embedding from FastAPI service
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array
     */
    private function getImageEmbedding($file)
    {
        try {
            // Convert image to base64
            $imageBase64 = $this->encodeImage($file);
            
            // Make request to FastAPI service with increased timeout and retry logic
            // This code makes an HTTP request to the FastAPI service with these configurations:
            // 1. Sets a timeout of 120 seconds (2 minutes) - allowing more time for the ML model to process
            // 2. Implements a retry mechanism that:
            //    - Will attempt the request up to 3 times
            //    - Waits 5000ms (5 seconds) between retry attempts
            //    - Only retries when either:
            //      a) A connection error occurs (service unreachable)
            //      b) The service returns a 5xx server error response
            $response = Http::timeout(120) // Increase timeout to 120 seconds (2 minutes)
                ->retry(3, 5000, function ($exception, $request) {
                    // Retry on connection errors or server errors (5xx responses)
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException || 
                           (optional($exception->response)->status() >= 500);
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->hfToken,
                    'Content-Type' => 'application/json',
                ])->post($this->fastApiUrl . '/compute_embedding', [
                    'image' => $imageBase64
                ]);
            
            // Check for successful response
            if ($response->successful()) {
                $data = $response->json();
                return $data['embedding'];
            } else {
                Log::error('Error from FastAPI service', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception('Error from FastAPI service: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error('Error getting image embedding', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Compare image similarity with FastAPI service
     *
     * @param  string  $imageBase64
     * @param  array  $storedEmbeddings
     * @param  float  $threshold
     * @return array
     */
    private function compareSimilarity($imageBase64, $storedEmbeddings, $threshold = 0.5)
    {
        try {
            // Make request to FastAPI service with increased timeout and retry logic
            $response = Http::timeout(120) // Increase timeout to 120 seconds (2 minutes)
                ->retry(3, 5000, function ($exception, $request) {
                    // Retry on connection errors or server errors (5xx responses)
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException || 
                           (optional($exception->response)->status() >= 500);
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->hfToken,
                    'Content-Type' => 'application/json',
                ])->post($this->fastApiUrl . '/compare_similarity', [
                    'new_image' => $imageBase64,
                    'stored_embeddings' => $storedEmbeddings,
                    'threshold' => $threshold
                ]);
            
            // Check for successful response
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Error from FastAPI service', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception('Error from FastAPI service: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error('Error comparing image similarity', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Encode image file to base64
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    private function encodeImage($file)
    {
        $imageData = file_get_contents($file->getRealPath());
        $imageBase64 = base64_encode($imageData);
        $mimeType = $file->getMimeType();
        
        return "data:{$mimeType};base64,{$imageBase64}";
    }
    
    /**
     * Set FastAPI URL (useful for testing or switching endpoints)
     * 
     * @param string $url
     * @return void
     */
    public function setFastApiUrl($url)
    {
        $this->fastApiUrl = $url;
    }
} 