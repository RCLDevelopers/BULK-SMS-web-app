<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

class TwilioService implements SmsServiceInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $from;

    /**
     * TwilioService constructor.
     *
     * @param string $sid
     * @param string $token
     * @param string|null $from
     */
    public function __construct(string $sid, string $token, ?string $from = null)
    {
        $this->client = new Client($sid, $token);
        $this->from = $from;
    }

    /**
     * @inheritDoc
     */
    public function sendSms(string $to, string $message, ?string $from = null): array
    {
        try {
            $from = $from ?? $this->from;
            
            if (empty($from)) {
                throw new Exception('From number is required');
            }

            $message = $this->client->messages->create(
                $this->formatNumber($to),
                [
                    'from' => $from,
                    'body' => $message
                ]
            );

            return [
                'success' => true,
                'message_sid' => $message->sid,
                'status' => $message->status,
                'to' => $to,
                'from' => $from,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'to' => $to,
                'from' => $from ?? $this->from,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function sendBulkSms(array $recipients, string $message, ?string $from = null): array
    {
        $results = [];
        
        foreach ($recipients as $to) {
            $results[$to] = $this->sendSms($to, $message, $from);
        }
        
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function checkBalance(): float
    {
        try {
            $balance = $this->client->balance->fetch();
            return (float) $balance->balance;
        } catch (Exception $e) {
            throw new Exception('Unable to fetch Twilio balance: ' . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getServiceName(): string
    {
        return 'Twilio';
    }

    /**
     * Format phone number to E.164 format
     *
     * @param string $number
     * @return string
     */
    protected function formatNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // If number doesn't start with '+', assume it's a local number and add the default country code
        if (strpos($number, '+') !== 0) {
            // This is a simple implementation - you might want to use a proper phone number library
            // like giggsey/libphonenumber-for-php for more robust phone number handling
            $number = '+' . ltrim($number, '0');
        }
        
        return $number;
    }
}
