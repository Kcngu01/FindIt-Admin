<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Claim;
use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get all faculties for the filter dropdown
        $faculties = Faculty::all();
        
        // Get count of registered students
        $registeredStudents = Student::count();
        
        // Check for faculty filter
        $facultyId = $request->input('faculty_id');
        
        // Get count of pending claims
        $pendingClaimsQuery = Claim::where('status', 'pending');
        
        // Apply faculty filter if selected
        if ($facultyId) {
            $pendingClaimsQuery->whereHas('foundItem', function($query) use ($facultyId) {
                $query->where('claim_location_id', $facultyId);
            });
        }
        $pendingClaims = $pendingClaimsQuery->count();
        
        // Get count of claimed items
        $claimedItemsQuery = Claim::where('status', 'approved')->orWhere('status', 'claimed');
        
        // Apply faculty filter if selected
        if ($facultyId) {
            $claimedItemsQuery->whereHas('foundItem', function($query) use ($facultyId) {
                $query->where('claim_location_id', $facultyId);
            });
        }
        $claimedItems = $claimedItemsQuery->count();
        
        // Calculate recovery rate (approved claims / total claims)
        $totalClaimsQuery = Claim::query();
        
        // Apply faculty filter if selected
        if ($facultyId) {
            $totalClaimsQuery->whereHas('foundItem', function($query) use ($facultyId) {
                $query->where('claim_location_id', $facultyId);
            });
        }
        $totalClaims = $totalClaimsQuery->count();
        
        $recoveryRate = ($totalClaims > 0) 
            ? round(($claimedItems / $totalClaims) * 100, 1) 
            : 0;
        
        // Get monthly data for the past 3 months
        $monthlyData = $this->getMonthlyClaimedData($facultyId);
        
        // Prepare data for the dashboard
        $dashboardData = [
            'registeredStudents' => $registeredStudents,
            'pendingClaims' => $pendingClaims,
            'claimedItems' => $claimedItems,
            'recoveryRate' => $recoveryRate,
            'monthlyClaimedData' => $monthlyData['data'],
            'monthLabels' => $monthlyData['labels'],
            'selectedFaculty' => $facultyId
        ];
        
        return view('dashboard', compact('dashboardData', 'faculties'));
    }
    
    /**
     * Get monthly claimed data for the last 3 months
     * 
     * @param int|null $facultyId Faculty ID for filtering
     * @return array
     */
    private function getMonthlyClaimedData($facultyId = null)
    {
        $monthlyData = [];
        $monthLabels = [];
        
        // Get data for the last 3 months
        for ($i = 2; $i >= 0; $i--) {
            // subtracts $i months from the current date.
            $date = Carbon::now()->subMonths($i);
            // Formats the date as a 3-letter month abbreviation.
            $month = $date->format('M');
            // Formats the date as a 4-digit year.
            $year = $date->format('Y');
            
            $monthLabels[] = $month;
            
            // Count approved claims for this month
            $claimsQuery = Claim::where('status', 'approved')->orWhere('status', 'claimed')
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $date->month);
            
            // Apply faculty filter if selected
            if ($facultyId) {
                $claimsQuery->whereHas('foundItem', function($query) use ($facultyId) {
                    $query->where('claim_location_id', $facultyId);
                });
            }
            
            $claimsCount = $claimsQuery->count();
                
            $monthlyData[] = $claimsCount;
        }
        
        return [
            'labels' => $monthLabels,
            'data' => $monthlyData
        ];
    }
} 