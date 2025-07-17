<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\Contact;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardStats extends Component
{
    public $period = 'month';
    public $stats = [];
    public $recentMessages = [];
    public $contactStats = [];
    public $serviceBalances = [];
    public $loading = true;

    protected $listeners = ['refreshDashboard' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->loading = true;
        $this->loadMessageStats();
        $this->loadRecentMessages();
        $this->loadContactStats();
        $this->loadServiceBalances();
        $this->loading = false;
    }

    protected function loadMessageStats()
    {
        $user = Auth::user();
        $now = now();
        
        // Determine date range based on selected period
        $startDate = match($this->period) {
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'year' => $now->copy()->startOfYear(),
            default => $now->copy()->startOfMonth(),
        };

        try {
            // Get message counts by status for current period
            $messageStats = Message::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get total messages sent in current period
            $totalMessages = array_sum($messageStats);

            // Calculate previous period date range
            $previousEndDate = $startDate->copy();
            $previousStartDate = match($this->period) {
                'week' => $previousEndDate->copy()->subWeek(),
                'month' => $previousEndDate->copy()->subMonth(),
                'year' => $previousEndDate->copy()->subYear(),
                default => $previousEndDate->copy()->subMonth(),
            };
            
            // Get message count for previous period
            $previousPeriodCount = Message::where('user_id', $user->id)
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->count();

            // Calculate trend percentage
            $trend = $previousPeriodCount > 0 
                ? (($totalMessages - $previousPeriodCount) / $previousPeriodCount) * 100 
                : ($totalMessages > 0 ? 100 : 0);
                
        } catch (\Exception $e) {
            // Log the error and set default values
            \Log::error('Error loading message stats: ' . $e->getMessage());
            
            return $this->stats = [
                'total_messages' => 0,
                'delivered' => 0,
                'failed' => 0,
                'pending' => 0,
                'trend' => 0,
                'period' => $this->period,
            ];
        }

        $this->stats = [
            'total_messages' => $totalMessages,
            'delivered' => $messageStats['delivered'] ?? 0,
            'failed' => $messageStats['failed'] ?? 0,
            'pending' => $messageStats['pending'] ?? 0,
            'trend' => round($trend, 1),
            'period' => $this->period,
        ];
    }

    protected function loadRecentMessages()
    {
        $this->recentMessages = Message::withCount('recipients')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($message) {
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                    'scheduled' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                ];

                return [
                    'id' => $message->id,
                    'subject' => $message->subject ?: 'No Subject',
                    'recipients_count' => $message->recipients_count,
                    'status' => $message->status,
                    'status_class' => $statusColors[$message->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                    'created_at' => $message->created_at->diffForHumans(),
                    'service' => $message->service === 'twilio' ? 'Twilio' : 'TextSMS Kenya',
                ];
            });
    }

    protected function loadContactStats()
    {
        $user = Auth::user();
        
        $totalContacts = Contact::where('user_id', $user->id)->count();
        $recentContacts = Contact::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $contactsByGroup = Contact::where('user_id', $user->id)
            ->whereNotNull('group')
            ->selectRaw('`group`, count(*) as count')
            ->groupBy('group')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->mapWithKeys(fn($item) => [$item->group => $item->count])
            ->toArray();

        $this->contactStats = [
            'total' => $totalContacts,
            'recent' => $recentContacts,
            'groups' => $contactsByGroup,
        ];
    }

    protected function loadServiceBalances()
    {
        $user = Auth::user();
        
        // Check if Twilio credentials are configured
        if (empty($user->twilio_sid) || empty($user->twilio_auth_token)) {
            $this->serviceBalances = [
                'current_service' => 'None',
                'balance' => 0,
                'currency' => 'KES',
                'warning' => 'SMS service not configured. Please update your profile with Twilio credentials.'
            ];
            return;
        }
        
        try {
            $smsService = app(SmsService::class);
            $balance = $smsService->getBalance();
            $this->serviceBalances = [
                'current_service' => $smsService->getServiceName(),
                'balance' => $balance,
                'currency' => 'KES', // Default currency, can be dynamic based on service
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to load service balances', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            
            $this->serviceBalances = [
                'current_service' => 'Error',
                'balance' => 0,
                'currency' => 'KES',
                'error' => 'Unable to fetch balance. Please check your SMS service configuration.'
            ];
        }
    }

    public function updatedPeriod()
    {
        $this->loadMessageStats();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
