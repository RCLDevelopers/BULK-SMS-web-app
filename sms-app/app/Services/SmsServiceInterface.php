<?php

namespace App\Services;

interface SmsServiceInterface
{
    /**
     * Send an SMS to a single recipient.
     *
     * @param string $to
     * @param string $message
     * @param string|null $from
     * @return array
     */
    public function sendSms(string $to, string $message, ?string $from = null): array;

    /**
     * Send bulk SMS to multiple recipients.
     *
     * @param array $recipients Array of phone numbers
     * @param string $message
     * @param string|null $from
     * @return array
     */
    public function sendBulkSms(array $recipients, string $message, ?string $from = null): array;

    /**
     * Check the account balance.
     *
     * @return float
     */
    public function checkBalance(): float;

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getServiceName(): string;
}
