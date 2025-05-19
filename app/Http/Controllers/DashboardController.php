<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Claim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get count of registered students
        $registeredStudents = Student::count();
        
        // Get count of pending claims
        $pendingClaims = Claim::where('status', 'pending')->count();
        
        // Get count of claimed items
        $claimedItems = Claim::where('status', 'approved')->count();
        
        // Calculate recovery rate (approved claims / total claims)
        $totalClaims = Claim::count();
        $recoveryRate = ($totalClaims > 0) 
            ? round(($claimedItems / $totalClaims) * 100, 1) 
            : 0;
        
        // Get monthly data for the past 3 months
        $monthlyData = $this->getMonthlyClaimedData();
        
        // Prepare data for the dashboard
        $dashboardData = [
            'registeredStudents' => $registeredStudents,
            'pendingClaims' => $pendingClaims,
            'claimedItems' => $claimedItems,
            'recoveryRate' => $recoveryRate,
            'monthlyClaimedData' => $monthlyData['data'],
            'monthLabels' => $monthlyData['labels']
        ];
        
        return view('dashboard', compact('dashboardData'));
    }
    
    /**
     * Get monthly claimed data for the last 3 months
     * 
     * @return array
     */
    private function getMonthlyClaimedData()
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
            //whereYear and whereMonth are Laravel query builder methods that filter records by year and month of a timestamp column.
            $claimsCount = Claim::where('status', 'approved')
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $date->month)
                ->count();
                
            $monthlyData[] = $claimsCount;
        }
        
        return [
            'labels' => $monthLabels,
            'data' => $monthlyData
        ];
    }
} 