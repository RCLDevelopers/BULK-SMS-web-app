<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class TextSmsKenyaService implements SmsServiceInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $senderId;

    /**
     * TextSmsKenyaService constructor.
     *
     * @param string $apiKey
     * @param string|null $senderId
     */
    public function __construct(string $apiKey, ?string $senderId = null)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.textsms.co.ke/api/v2/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ]
        ]);
        
        $this->apiKey = $apiKey;
        $this->senderId = $senderId;
    }

    /**
     * @inheritDoc
     */
    public function sendSms(string $to, string $message, ?string $senderId = null): array
    {
        try {
            $senderId = $senderId ?? $this->senderId;
            
            if (empty($senderId)) {
                throw new Exception('Sender ID is required');
            }

            $response = $this->client->post('sms/send', [
                'json' => [
                    'phone' => $this->formatNumber($to),
                    'message' => $message,
                    'sender_id' => $senderId,
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || !isset($responseData['status']) || $responseData['status'] !== 'success') {
                throw new Exception($responseData['message'] ?? 'Failed to send SMS');
            }

            return [
                'success' => true,
                'message_id' => $responseData['data']['message_id'] ?? null,
                'status' => 'sent',
                'to' => $to,
                'from' => $senderId,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'to' => $to,
                'from' => $senderId ?? $this->senderId,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function sendBulkSms(array $recipients, string $message, ?string $senderId = null): array
    {
        try {
            $senderId = $senderId ?? $this->senderId;
            
            if (empty($senderId)) {
                throw new Exception('Sender ID is required');
            }

            $response = $this->client->post('sms/bulk/send', [
                'json' => [
                    'phones' => array_map([$this, 'formatNumber'], $recipients),
                    'message' => $message,
                    'sender_id' => $senderId,
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || !isset($responseData['status']) || $responseData['status'] !== 'success') {
                throw new Exception($responseData['message'] ?? 'Failed to send bulk SMS');
            }

            return [
                'success' => true,
                'batch_id' => $responseData['data']['batch_id'] ?? null,
                'status' => 'sent',
                'recipients_count' => count($recipients),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'recipients_count' => count($recipients),
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function checkBalance(): float
    {
        try {
            $response = $this->client->get('account/balance');
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || !isset($responseData['status']) || $responseData['status'] !== 'success') {
                throw new Exception($responseData['message'] ?? 'Failed to fetch balance');
            }

            return (float) ($responseData['data']['balance'] ?? 0);
        } catch (Exception $e) {
            throw new Exception('Unable to fetch TextSMS Kenya balance: ' . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getServiceName(): string
    {
        return 'TextSMS Kenya';
    }

    /**
     * Format phone number
     *
     * @param string $number
     * @return string
     */
    protected function formatNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // If number starts with '0', replace with country code '254' for Kenya
        if (strpos($number, '0') === 0) {
            $number = '254' . substr($number, 1);
        }
        // If number starts with '+', remove the '+'
        elseif (strpos($number, '+') === 0) {
            $number = substr($number, 1);
        }
        
        return $number;
    }
}
