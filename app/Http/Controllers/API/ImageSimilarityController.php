<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageSimilarityController extends Controller
{
    // URL to your FastAPI service - would typically be in .env
    private $fastApiUrl = "https://02f5-34-69-54-2.ngrok-free.app"; // Update with actual Colab URL
    
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
                                ->whereNotNull('image_embeddings')
                                ->get();
            
            // If no related items, just get the patch embedding
            if ($relatedItems->isEmpty()) {
                Log::info('No related items found for comparison');
                $embedding = $this->getImageEmbedding($file);
                
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
            
            // Make request to FastAPI service with patch embeddings support
            $imageBase64 = $this->encodeImage($file);
            $matches = $this->compareSimilarity($imageBase64, $storedEmbeddings);
            
            return $matches;
            
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
            // Iterate through matches
            foreach ($matches as $match) {
                $matchData = [
                    'similarity_score' => $match['similarity'],
                    'status' => 'available',
                ];
                
                // Set lost item and found item based on types
                if ($item->type === 'lost') {
                    $matchData['lost_item_id'] = $item->id;
                    $matchData['found_item_id'] = $match['item_id'];
                } else {
                    $matchData['lost_item_id'] = $match['item_id'];
                    $matchData['found_item_id'] = $item->id;
                }
                
                // Create the match record
                ItemMatch::create($matchData);
            }
            
            Log::info('Created ' . count($matches) . ' match records for item #' . $item->id);
            
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
            
            // Make request to FastAPI service
            $response = Http::post($this->fastApiUrl . '/compute_embedding', [
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
            // Make request to FastAPI service
            $response = Http::post($this->fastApiUrl . '/compare_similarity', [
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