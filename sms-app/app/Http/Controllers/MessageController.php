<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->middleware('auth');
        $this->smsService = $smsService;
    }

    /**
     * Show the message composition form
     */
    public function compose()
    {
        return view('messages.compose');
    }

    /**
     * Send a quick message
     */
    public function quickSend(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:160'
        ]);

        try {
            $result = $this->smsService->sendBulk(
                [$validated['phone']],
                $validated['message'],
                'Quick Message',
                null,
                true
            );

            if ($result['success']) {
                return redirect()->route('dashboard')
                    ->with('status', 'Message sent successfully!');
            }

            return back()->with('error', $result['error'] ?? 'Failed to send message');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display message history
     */
    public function history(Request $request)
    {
        $query = Message::withCount('recipients')
            ->where('user_id', Auth::id())
            ->latest();

        // Apply filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('service')) {
            $query->where('service', $request->input('service'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('scheduled_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        $messages = $query->paginate(15);

        return view('messages.history', compact('messages'));
    }

    /**
     * Display the specified message
     */
    public function show(Message $message)
    {
        $this->authorize('view', $message);
        
        $message->load(['recipients' => function($query) {
            $query->withPivot('status', 'error_message');
        }]);

        return view('messages.show', compact('message'));
    }

    /**
     * Cancel a scheduled message
     */
    public function cancel(Message $message)
    {
        $this->authorize('update', $message);

        if ($message->status !== 'scheduled') {
            return back()->with('error', 'Only scheduled messages can be cancelled');
        }

        $message->update(['status' => 'cancelled']);
        
        return back()->with('status', 'Message has been cancelled');
    }

    /**
     * Delete a message
     */
    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        
        $message->delete();
        
        return redirect()->route('messages.history')
            ->with('status', 'Message has been deleted');
    }

    /**
     * Export message history
     */
    public function export(Request $request)
    {
        $query = Message::withCount('recipients')
            ->where('user_id', Auth::id())
            ->latest();

        // Apply filters same as history method
        $this->applyFilters($query, $request);

        $messages = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="messages_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID', 'Subject', 'Message', 'Status', 'Service', 
                'Recipients', 'Scheduled At', 'Sent At', 'Created At'
            ]);
            
            // Add data rows
            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->subject,
                    Str::limit($message->message, 50),
                    ucfirst($message->status),
                    $message->service,
                    $message->recipients_count,
                    $message->scheduled_at?->format('Y-m-d H:i:s'),
                    $message->sent_at?->format('Y-m-d H:i:s'),
                    $message->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, $request)
    {
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('service')) {
            $query->where('service', $request->input('service'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('scheduled_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }
    }
}
