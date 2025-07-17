<?php

namespace App\Helpers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberHelper
{
    /**
     * Format a phone number to E.164 format.
     *
     * @param string $phoneNumber
     * @param string $defaultRegion
     * @return string|null
     */
    public static function formatE164(string $phoneNumber, string $defaultRegion = 'KE'): ?string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, $defaultRegion);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
            }
        } catch (NumberParseException $e) {
            // Log error if needed
            \Log::error('Phone number parsing failed: ' . $e->getMessage(), [
                'phone_number' => $phoneNumber,
                'default_region' => $defaultRegion,
            ]);
        }

        return null;
    }

    /**
     * Check if a phone number is valid.
     *
     * @param string $phoneNumber
     * @param string $defaultRegion
     * @return bool
     */
    public static function isValid(string $phoneNumber, string $defaultRegion = 'KE'): bool
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, $defaultRegion);
            return $phoneUtil->isValidNumber($numberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Format a phone number for display.
     *
     * @param string $phoneNumber
     * @param string $defaultRegion
     * @return string
     */
    public static function formatForDisplay(string $phoneNumber, string $defaultRegion = 'KE'): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, $defaultRegion);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->format($numberProto, PhoneNumberFormat::INTERNATIONAL);
            }
        } catch (NumberParseException $e) {
            // Return the original number if parsing fails
        }

        return $phoneNumber;
    }

    /**
     * Extract the country code from a phone number.
     *
     * @param string $phoneNumber
     * @param string $defaultRegion
     * @return int|null
     */
    public static function getCountryCode(string $phoneNumber, string $defaultRegion = 'KE'): ?int
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, $defaultRegion);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $numberProto->getCountryCode();
            }
        } catch (NumberParseException $e) {
            // Return null if parsing fails
        }

        return null;
    }
}
