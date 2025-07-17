<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * The SMS service instance.
     *
     * @var SmsServiceInterface
     */
    protected $smsService;

    /**
     * The user instance.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new SmsService instance.
     *
     * @param User $user
     * @param string|null $service
     */
    public function __construct(User $user, ?string $service = null)
    {
        $this->user = $user;
        $service = $service ?: config('sms.default');
        $this->smsService = app('sms.' . $service);
    }

    /**
     * Send an SMS message.
     *
     * @param string $to
     * @param string $message
     * @param string|null $subject
     * @param string|null $service
     * @param bool $queue
     * @return array
     */
    public function send(
        string $to, 
        string $message, 
        ?string $subject = null, 
        ?string $service = null,
        bool $queue = true
    ): array {
        return $this->sendBulk([$to], $message, $subject, $service, $queue);
    }

    /**
     * Send bulk SMS messages.
     *
     * @param array $recipients
     * @param string $message
     * @param string|null $subject
     * @param string|null $service
     * @param bool $queue
     * @return array
     */
    public function sendBulk(
        array $recipients, 
        string $message, 
        ?string $subject = null, 
        ?string $service = null,
        bool $queue = true
    ): array {
        try {
            // Create a new message record
            $messageRecord = $this->createMessageRecord($message, $subject, $service, count($recipients));

            // Dispatch jobs for each recipient
            foreach ($recipients as $recipient) {
                $this->dispatchSmsJob($messageRecord, $recipient, $service, $queue);
            }

            return [
                'success' => true,
                'message' => 'SMS messages are being processed.',
                'message_id' => $messageRecord->id,
                'recipients_count' => count($recipients),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send bulk SMS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send SMS messages: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new message record.
     *
     * @param string $message
     * @param string|null $subject
     * @param string|null $service
     * @param int $recipientCount
     * @return Message
     */
    protected function createMessageRecord(
        string $message, 
        ?string $subject = null, 
        ?string $service = null, 
        int $recipientCount = 1
    ): Message {
        return $this->user->messages()->create([
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
            'sent_via' => $service ?: config('sms.default'),
            'recipients_count' => $recipientCount,
            'success_count' => 0,
            'failure_count' => 0,
        ]);
    }

    /**
     * Dispatch an SMS job for a recipient.
     *
     * @param Message $message
     * @param string|array $recipient
     * @param string|null $service
     * @param bool $queue
     * @return void
     */
    protected function dispatchSmsJob(
        Message $message, 
        $recipient, 
        ?string $service = null, 
        bool $queue = true
    ): void {
        $phoneNumber = is_array($recipient) ? ($recipient['phone_number'] ?? null) : $recipient;
        $contactId = is_array($recipient) ? ($recipient['id'] ?? null) : null;

        if (!$phoneNumber) {
            Log::warning('Skipping recipient without phone number', [
                'recipient' => $recipient,
                'message_id' => $message->id,
            ]);
            return;
        }

        $job = new SendSmsJob(
            $message,
            $phoneNumber,
            $contactId,
            $service ?: config('sms.default')
        );

        if ($queue) {
            $job->onQueue(config('sms.queue.queue'));
            dispatch($job);
        } else {
            dispatch_sync($job);
        }
    }

    /**
     * Get the account balance.
     *
     * @return float
     */
    public function getBalance(): float
    {
        try {
            return $this->smsService->checkBalance();
        } catch (\Exception $e) {
            Log::error('Failed to get SMS balance', [
                'error' => $e->getMessage(),
                'service' => get_class($this->smsService),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->smsService->getServiceName();
    }
}
