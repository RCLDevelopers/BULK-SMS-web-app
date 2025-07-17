<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSmsRequest;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    /**
     * The SmsService instance.
     *
     * @var SmsService
     */
    protected $smsService;

    /**
     * Create a new controller instance.
     *
     * @param SmsService $smsService
     * @return void
     */
    public function __construct(SmsService $smsService)
    {
        $this->middleware('auth');
        $this->smsService = $smsService;
    }

    /**
     * Send an SMS message.
     *
     * @param SendSmsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(SendSmsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $result = $this->smsService->sendBulk(
            $validated['recipients'],
            $validated['message'],
            $validated['subject'] ?? null,
            $validated['service'] ?? null,
            true // Queue the job
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS messages are being processed.',
                'data' => [
                    'message_id' => $result['message_id'],
                    'recipients_count' => count($validated['recipients']),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to send SMS messages.',
        ], 500);
    }

    /**
     * Get the account balance.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(Request $request): JsonResponse
    {
        try {
            $balance = $this->smsService->getBalance();
            $serviceName = $this->smsService->getServiceName();

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $balance,
                    'currency' => 'KES', // Assuming Kenyan Shillings for TextSMS Kenya
                    'service' => $serviceName,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the message history.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Auth::user()->messages()
            ->withCount('recipients')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $messages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Get the message details.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $message = Auth::user()->messages()
            ->with(['recipients' => function ($query) {
                $query->latest();
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }
}
