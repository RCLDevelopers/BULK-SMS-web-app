<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', 'month');
        
        // Set date range based on period
        $endDate = now();
        $startDate = $this->getStartDate($period, $endDate);
        
        // Get message statistics
        $messageStats = $this->getMessageStats($startDate, $endDate);
        
        // Get message trend data
        $messageTrend = $this->getMessageTrend($startDate, $endDate);
        
        // Get service usage
        $serviceUsage = $this->getServiceUsage($startDate, $endDate);
        
        // Get contact growth
        $contactGrowth = $this->getContactGrowth($startDate, $endDate);
        
        // Get top contacts
        $topContacts = $this->getTopContacts($startDate, $endDate);
        
        return view('reports.index', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'messageStats' => $messageStats,
            'messageTrend' => $messageTrend,
            'serviceUsage' => $serviceUsage,
            'contactGrowth' => $contactGrowth,
            'topContacts' => $topContacts,
        ]);
    }
    
    /**
     * Export reports data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', 'month');
        
        // Set date range based on period
        $endDate = now();
        $startDate = $this->getStartDate($period, $endDate);
        
        // Get message statistics
        $messageStats = $this->getMessageStats($startDate, $endDate);
        $messageTrend = $this->getMessageTrend($startDate, $endDate);
        $serviceUsage = $this->getServiceUsage($startDate, $endDate);
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sms_reports_' . now()->format('Y-m-d') . '.csv"',
        ];
        
        return response()->stream(
            function() use ($messageStats, $messageTrend, $serviceUsage, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                
                // Report Summary
                fputcsv($file, ['SMS Reports Summary']);
                fputcsv($file, ['Period', $startDate->format('M j, Y') . ' to ' . $endDate->format('M j, Y')]);
                fputcsv($file, ['']);
                
                // Message Statistics
                fputcsv($file, ['Message Statistics']);
                fputcsv($file, ['Total Messages', $messageStats['total']]);
                fputcsv($file, ['Delivered', $messageStats['delivered'] . ' (' . $messageStats['delivered_percent'] . '%)']);
                fputcsv($file, ['Failed', $messageStats['failed'] . ' (' . $messageStats['failed_percent'] . '%)']);
                fputcsv($file, ['Pending', $messageStats['pending'] . ' (' . $messageStats['pending_percent'] . '%)']);
                fputcsv($file, ['']);
                
                // Service Usage
                fputcsv($file, ['Service Usage']);
                foreach ($serviceUsage as $service) {
                    fputcsv($file, [
                        ucfirst($service->service),
                        $service->count . ' messages',
                        $service->percentage . '%'
                    ]);
                }
                fputcsv($file, ['']);
                
                // Message Trend
                fputcsv($file, ['Message Trend']);
                fputcsv($file, ['Date', 'Total', 'Delivered', 'Failed', 'Pending']);
                foreach ($messageTrend as $trend) {
                    fputcsv($file, [
                        $trend->date,
                        $trend->total,
                        $trend->delivered,
                        $trend->failed,
                        $trend->pending
                    ]);
                }
                
                fclose($file);
            },
            200,
            $headers
        );
    }
    
    /**
     * Get start date based on period
     */
    protected function getStartDate($period, $endDate)
    {
        return match($period) {
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->startOfMonth(),
            'quarter' => $endDate->copy()->subMonths(3),
            'year' => $endDate->copy()->startOfYear(),
            default => $endDate->copy()->subMonth(),
        };
    }
    
    /**
     * Get message statistics
     */
    protected function getMessageStats($startDate, $endDate)
    {
        $user = Auth::user();
        
        $stats = Message::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = "delivered" then 1 else 0 end) as delivered')
            ->selectRaw('sum(case when status = "failed" then 1 else 0 end) as failed')
            ->selectRaw('sum(case when status = "pending" then 1 else 0 end) as pending')
            ->first();
            
        $total = $stats->total ?? 0;
        
        return [
            'total' => $total,
            'delivered' => $stats->delivered ?? 0,
            'failed' => $stats->failed ?? 0,
            'pending' => $stats->pending ?? 0,
            'delivered_percent' => $total > 0 ? round(($stats->delivered / $total) * 100, 1) : 0,
            'failed_percent' => $total > 0 ? round(($stats->failed / $total) * 100, 1) : 0,
            'pending_percent' => $total > 0 ? round(($stats->pending / $total) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get message trend data
     */
    protected function getMessageTrend($startDate, $endDate)
    {
        $user = Auth::user();
        $dateFormat = $this->getDateFormat($startDate, $endDate);
        
        return Message::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when status = "delivered" then 1 else 0 end) as delivered'),
                DB::raw('sum(case when status = "failed" then 1 else 0 end) as failed'),
                DB::raw('sum(case when status = "pending" then 1 else 0 end) as pending')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    /**
     * Get service usage statistics
     */
    protected function getServiceUsage($startDate, $endDate)
    {
        $user = Auth::user();
        
        $total = Message::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        return Message::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'service',
                DB::raw('count(*) as count'),
                DB::raw('round((count(*) * 100.0) / ' . max($total, 1) . ', 1) as percentage')
            )
            ->groupBy('service')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    /**
     * Get contact growth data
     */
    protected function getContactGrowth($startDate, $endDate)
    {
        $user = Auth::user();
        $dateFormat = $this->getDateFormat($startDate, $endDate);
        
        return Contact::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    /**
     * Get top contacts
     */
    protected function getTopContacts($startDate, $endDate, $limit = 5)
    {
        $user = Auth::user();
        
        return DB::table('message_recipient')
            ->join('contacts', 'message_recipient.contact_id', '=', 'contacts.id')
            ->join('messages', 'message_recipient.message_id', '=', 'messages.id')
            ->where('messages.user_id', $user->id)
            ->whereBetween('messages.created_at', [$startDate, $endDate])
            ->select(
                'contacts.id',
                'contacts.name',
                'contacts.phone',
                DB::raw('count(*) as message_count')
            )
            ->groupBy('contacts.id', 'contacts.name', 'contacts.phone')
            ->orderBy('message_count', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get appropriate date format for grouping
     */
    protected function getDateFormat($startDate, $endDate)
    {
        $daysDifference = $startDate->diffInDays($endDate);
        
        if ($daysDifference <= 7) {
            return '%Y-%m-%d %H:00'; // Hourly for a week
        } elseif ($daysDifference <= 30) {
            return '%Y-%m-%d'; // Daily for a month
        } elseif ($daysDifference <= 90) {
            return '%Y-%U'; // Weekly for 3 months
        } else {
            return '%Y-%m'; // Monthly for longer periods
        }
    }
}
