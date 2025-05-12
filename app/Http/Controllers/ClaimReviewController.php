<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Claim;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;

class ClaimReviewController extends Controller
{
    //
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index(){
        // Get all found items that have been claimed
        $foundItems = Item::where('type','found')
            ->whereHas('claims', function($query) {
                $query->where('status', 'pending');
            })
            ->with(['category','color','location','student'])
            ->withCount(['claims' => function($query) {
                $query->where('status', 'pending');
            }])
            ->get();
        
        
        return view('claim-index', [
            'foundItems' => $foundItems, 
        ]);
    }

    public function review(int $id){
        $foundItem = Item::find($id);
        $claims = $foundItem->claims()
            ->where('status', 'pending')
            ->with(['lostItem','match','student'])
            ->get();

            //justification, similarity score
        return view('claim-review',[
            'foundItem' => $foundItem, 
            'claims' => $claims
        ]);
    }

    /**
     * Get data for comparison between found and lost items
     * 
     * @param int $claimId The claim ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComparisonData(int $claimId)
    {
        $claim = \App\Models\Claim::with([
            // 'foundItem.category', 
            // 'foundItem.color', 
            // 'foundItem.location', 
            'lostItem.category', 
            'lostItem.color', 
            'lostItem.location',
            'match'
        ])->findOrFail($claimId);
        
        // Check if lost item exists
        $hasLostItem = !is_null($claim->lostItem);
        
        // Format dates
        // $dateFound = $claim->foundItem->created_at ? $claim->foundItem->created_at->format('d/m/Y') : '-';
        $dateLost = $hasLostItem && $claim->lostItem->created_at ? $claim->lostItem->created_at->format('d/m/Y') : '-';
        
        // Calculate similarity score if not available
        $similarityScore = $claim->match ? $claim->match->similarity_score : 'N/A';
        
        // Prepare response data
        $response = [
            'has_lost_item' => $hasLostItem,
            'claim_id' => $claim->id
        ];
        
        // Add found item data
        // $response['found'] = [
        //     'id' => $claim->foundItem->id,
        //     'name' => $claim->foundItem->name,
        //     'description' => $claim->foundItem->description ?? '-',
        //     'category' => $claim->foundItem->category ? $claim->foundItem->category->name : '-',
        //     'color' => $claim->foundItem->color ? $claim->foundItem->color->name : '-',
        //     'location' => $claim->foundItem->location ? $claim->foundItem->location->name : '-',
        //     'date' => $dateFound,
        //     'image' => $claim->foundItem->image ? asset('storage/found_items/'.$claim->foundItem->image) : asset('images/placeholder.png')
        // ];
        
        // Add lost item data if it exists
        if ($hasLostItem) {
            $response['lost'] = [
                'id' => $claim->lostItem->id,
                'name' => $claim->lostItem->name,
                'description' => $claim->lostItem->description ?? '-',
                'category' => $claim->lostItem->category ? $claim->lostItem->category->name : '-',
                'color' => $claim->lostItem->color ? $claim->lostItem->color->name : '-',
                'location' => $claim->lostItem->location ? $claim->lostItem->location->name : '-',
                'date' => $dateLost,
                'image' => $claim->lostItem->image ? asset('storage/lost_items/'.$claim->lostItem->image) : 'no_image',
                'similarity_score' => $similarityScore,
                'justification' => $claim->student_justification ?? '-'
            ];
        } else {
            // Only send justification for claims without lost items
            $response['lost'] = [
                'id' => '-',
                'name' => '-',
                'description' => '-',
                'category' => '-',
                'color' => '-',
                'location' => '-',
                'date' => '-',
                'image' => 'no_image', // Special flag to indicate no image
                'similarity_score' => '-',
                'justification' => $claim->student_justification ?? '-'
            ];
        }
        
        return response()->json($response);
    }

    public function rejectClaim(Request $request){
        try {
            // Validate the request
            $validated = $request->validate([
                'claimId' => 'required|exists:claims,id',
                'adminJustification' => 'nullable|string|max:500'
            ]);
            
            $claim = Claim::findOrFail($request->claimId);
            $claim->status = 'rejected';
            $claim->admin_id = Auth::id(); // Get the authenticated admin's ID using Auth facade
            $claim->admin_justification = $request->adminJustification ?? null;
            $claim->save();
            
            // Send notification to user
            $this->sendClaimNotification($claim, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Claim has been rejected successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject claim: ' . $e->getMessage()
            ], 500);
        }
    }

    public function acceptClaim(Request $request){
        try {
            // Validate the request
            $validated = $request->validate([
                'claimId' => 'required|exists:claims,id',
                'adminJustification' => 'nullable|string|max:500'
            ]);
            
            $claim = Claim::findOrFail($request->claimId);
            $claim->status = 'approved';
            $claim->admin_id = Auth::id(); // Get the authenticated admin's ID using Auth facade
            $claim->admin_justification = $request->adminJustification ?? null;
            $claim->save();
            
            // Send notification to user
            $this->sendClaimNotification($claim, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Claim has been approved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve claim: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendClaimNotification(Claim $claim, string $status)
    {
        try {
            // Get the student who made the claim
            $student = $claim->student;
            if (!$student) {
                Log::error('Cannot send notification: Student not found for claim #' . $claim->id);
                return;
            }
            // Get user FCM tokens
            $tokens = FcmToken::where('student_id', $claim->student_id)
                        ->pluck('device_token')
                        ->toArray();
            
            if (empty($tokens)) {
                Log::info("No FCM tokens found for user {$claim->student_id}");
                return;
            }
            
            // Prepare notification content
            $foundItem = $claim->foundItem;
            $itemName = $foundItem ? $foundItem->name : 'item';
            if ($status === 'approved') {
                $title = 'Claim Approved';
                $body = "Your claim for the $itemName has been approved.";
            } else {
                $title = 'Claim Rejected';
                $body = "Your claim for the $itemName has been rejected.";
            }

            // Additional data for the app to process
            $data = [
                'claim_id' => (string) $claim->id,
                'item_id' => $foundItem ? (string) $foundItem->id : '',
                'status' => $status,
                'notification_type' => 'claim_update',
                'admin_justification' => $claim->admin_justification ?? ''
            ];
            
            // Send notification
            $result = $this->firebaseService->sendMulticastNotification($tokens, $title, $body, $data);
            
            // Store notification in the database
            \App\Models\StudentNotification::create([
                'student_id' => $claim->student_id,
                'title' => $title,
                'body' => $body,
                'type' => 'claim_update',
                'data' => $data,
                'status' => 'unread'
            ]);
            
            Log::info("Claim notification sent for claim #{$claim->id}", [
                'action' => $status,
                'student_id' => $claim->student_id,
                'token_count' => count($tokens),
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send claim notification: ' . $e->getMessage());
        }
    }
}
