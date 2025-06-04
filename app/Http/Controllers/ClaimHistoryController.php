<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use Illuminate\Support\Facades\Auth;

class ClaimHistoryController extends Controller
{
    //
    public function index(){
        $claims = Claim::where('status', 'approved')
            ->orWhere('status', 'rejected')
            ->orWhere('status', 'claimed')
            ->with(['student', 'foundItem.claimLocation'])
            ->get();
            
        // Get all faculties for filtering
        $faculties = \App\Models\Faculty::all();
            
        return view('claim-history',[
            'claims' => $claims,
            'faculties' => $faculties,
        ]);
    }
    
    /**
     * Display the details of a specific claim
     *
     * @param int $id The ID of the claim
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        // Find the claim with related data
        $claim = Claim::with([
            'foundItem.category', 
            'foundItem.color', 
            'foundItem.location',
            'foundItem.claimLocation',
            'lostItem.category', 
            'lostItem.color', 
            'lostItem.location',
            'student',
            'admin',
            'match'
        ])->findOrFail($id);
        
        // Ensure the claim has been processed (approved, rejected, or claimed)
        if ($claim->status !== 'approved' && $claim->status !== 'rejected' && $claim->status !== 'claimed') {
            return redirect()->route('claim-history.index')
                ->with('error', 'This claim has not been processed yet.');
        }
        
        return view('claim-history-view', [
            'claim' => $claim
        ]);
    }
    
    /**
     * Mark a claim as 'claimed' when a student has collected their item
     *
     * @param int $id The ID of the claim
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsClaimed($id)
    {
        // Find the claim
        $claim = Claim::findOrFail($id);
        
        // Check if the claim is in 'approved' status
        if ($claim->status !== 'approved') {
            return redirect()->route('claim-history.index')
                ->with('error', 'Only approved claims can be marked as claimed.');
        }
        
        // Update the claim status to 'claimed'
        $claim->status = 'claimed';
        $claim->save();
        
        // Get student and item details for the success message
        $studentMatric = $claim->student ? $claim->student->matric_no : 'Unknown';
        $itemName = $claim->foundItem ? $claim->foundItem->name : 'Unknown item';
        
        return redirect()->route('claim-history.index')
            ->with('success', "Item \"$itemName\" has been successfully collected by student $studentMatric.");
    }
}
