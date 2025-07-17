<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Services\SmsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 300, 600];

    /**
     * The message model instance.
     *
     * @var \App\Models\Message
     */
    protected $message;

    /**
     * The recipient's phone number.
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * The contact ID if available.
     *
     * @var int|null
     */
    protected $contactId;

    /**
     * The SMS service to use.
     *
     * @var string
     */
    protected $service;

    /**
     * Create a new job instance.
     *
     * @param Message $message
     * @param string $phoneNumber
     * @param int|null $contactId
     * @param string $service
     * @return void
     */
    public function __construct(Message $message, string $phoneNumber, ?int $contactId = null, string $service = 'twilio')
    {
        $this->message = $message->withoutRelations();
        $this->phoneNumber = $phoneNumber;
        $this->contactId = $contactId;
        $this->service = $service;
    }

    /**
     * Execute the job.
     *
     * @param SmsServiceInterface $smsService
     * @return void
     */
    public function handle(SmsServiceInterface $smsService)
    {
        try {
            // Create a message recipient record
            $recipient = new MessageRecipient([
                'message_id' => $this->message->id,
                'contact_id' => $this->contactId,
                'phone_number' => $this->phoneNumber,
                'status' => 'pending',
            ]);

            $recipient->save();

            // Send the SMS
            $response = $smsService->sendSms(
                $this->phoneNumber,
                $this->message->message,
                $this->getSenderId()
            );

            // Update the recipient status
            if ($response['success']) {
                $recipient->update([
                    'status' => 'sent',
                    'message_sid' => $response['message_sid'] ?? null,
                    'sent_at' => now(),
                ]);

                // Update the message success count
                $this->message->increment('success_count');
            } else {
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $response['error'] ?? 'Unknown error',
                ]);

                // Update the message failure count
                $this->message->increment('failure_count');

                // Log the error
                Log::error('Failed to send SMS', [
                    'message_id' => $this->message->id,
                    'phone_number' => $this->phoneNumber,
                    'error' => $response['error'] ?? 'Unknown error',
                ]);

                // Re-throw the exception to trigger a retry
                throw new \Exception($response['error'] ?? 'Failed to send SMS');
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('SMS sending job failed', [
                'message_id' => $this->message->id,
                'phone_number' => $this->phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update the recipient status if it was created
            if (isset($recipient)) {
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                // Update the message failure count
                $this->message->increment('failure_count');
            }

            // Re-throw the exception to trigger a retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Log the final failure after all retries are exhausted
        Log::error('SMS sending job failed after all retries', [
            'message_id' => $this->message->id,
            'phone_number' => $this->phoneNumber,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Get the sender ID based on the service.
     *
     * @return string|null
     */
    protected function getSenderId(): ?string
    {
        if ($this->service === 'twilio') {
            return config('services.twilio.from');
        }

        if ($this->service === 'textsms') {
            return config('services.textsms.sender_id');
        }

        return null;
    }
}
