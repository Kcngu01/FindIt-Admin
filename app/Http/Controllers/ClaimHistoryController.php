<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;

class ClaimHistoryController extends Controller
{
    //
    public function index(){
        $claims = Claim::where('status', 'approved')
            ->orWhere('status', 'rejected')
            ->with('student')
            ->get();
        return view('claim-history',['claims' => $claims]);
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
            'lostItem.category', 
            'lostItem.color', 
            'lostItem.location',
            'student',
            'admin',
            'match'
        ])->findOrFail($id);
        
        // Ensure the claim has been processed (approved or rejected)
        if ($claim->status !== 'approved' && $claim->status !== 'rejected') {
            return redirect()->route('claim-history.index')
                ->with('error', 'This claim has not been processed yet.');
        }
        
        return view('claim-history-view', [
            'claim' => $claim
        ]);
    }
}
