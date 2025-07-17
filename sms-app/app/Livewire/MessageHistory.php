<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\MessageRecipient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MessageHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $service = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedMessage = null;
    public $showDetailsModal = false;
    public $recipients = [];
    public $stats = [
        'total' => 0,
        'delivered' => 0,
        'failed' => 0,
        'pending' => 0,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'service' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'status',
            'service',
            'dateFrom',
            'dateTo',
        ]);
        $this->resetPage();
    }

    public function showMessageDetails($messageId)
    {
        $this->selectedMessage = Message::with(['user', 'recipients.contact'])
            ->where('id', $messageId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->recipients = $this->selectedMessage->recipients()
            ->with('contact')
            ->latest()
            ->get();

        $this->showDetailsModal = true;
    }

    public function exportToCsv()
    {
        $messages = $this->getMessagesQuery()->get();
        $fileName = 'message-history-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel compatibility
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Subject',
                'Message',
                'Status',
                'Service',
                'Recipients',
                'Delivered',
                'Failed',
                'Pending',
                'Created At',
            ]);

            // Data
            foreach ($messages as $message) {
                $stats = $message->getStatusCounts();
                
                fputcsv($file, [
                    $message->id,
                    $message->subject,
                    $message->message,
                    $message->status,
                    $message->service,
                    $message->recipients_count,
                    $stats['delivered'] ?? 0,
                    $stats['failed'] ?? 0,
                    $stats['pending'] ?? 0,
                    $message->created_at->toDateTimeString(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getMessagesQuery()
    {
        $query = Message::withCount('recipients')
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                      ->orWhere('message', 'like', '%' . $this->search . '%')
                      ->orWhere('status', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->service, function ($query) {
                $query->where('service', $this->service);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            });

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    protected function updateStats()
    {
        $messages = $this->getMessagesQuery()->get();
        
        $this->stats = [
            'total' => $messages->count(),
            'delivered' => $messages->sum(function ($message) {
                return $message->recipients()->where('status', 'delivered')->count();
            }),
            'failed' => $messages->sum(function ($message) {
                return $message->recipients()->where('status', 'failed')->count();
            }),
            'pending' => $messages->sum(function ($message) {
                return $message->recipients()->where('status', 'pending')->count();
            }),
        ];
    }

    public function render()
    {
        $messages = $this->getMessagesQuery()
            ->paginate($this->perPage);

        $this->updateStats();

        return view('livewire.message-history', [
            'messages' => $messages,
            'statuses' => [
                'pending' => 'Pending',
                'sent' => 'Sent',
                'delivered' => 'Delivered',
                'failed' => 'Failed',
                'scheduled' => 'Scheduled',
            ],
            'services' => [
                'twilio' => 'Twilio',
                'textsms' => 'TextSMS Kenya',
            ],
        ]);
    }
}
