<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker;

use Illuminate\Support\Carbon;

class RsaIdNumber
{
    public static function generateRsaIdNumber(
        Carbon $dateOfBirth,
        string $gender,
        bool $isCitizen = true
    ): string
    {
        // Normalize gender input
        $gender = trim(strtolower($gender));

        // Format date of birth (YYMMDD)
        $year = $dateOfBirth->year;
        $month = str_pad($dateOfBirth->month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($dateOfBirth->day, 2, '0', STR_PAD_LEFT);
        $dob = substr($year, -2) . $month . $day;

        // Generate sequence number based on gender
        $sequenceStart = $gender === 'm' ? 5000 : 0;
        $sequence = str_pad(rand($sequenceStart, $sequenceStart + 4999), 4, '0', STR_PAD_LEFT);

        // Citizenship digit: 0 = South African, 1 = Permanent resident
        $citizenDigit = $isCitizen ? 0 : 1;

        // Random miscellaneous digit (can vary, 8 or 9 are common)
        $randomA = rand(8, 9);

        // Form the partial ID (first 12 digits)
        $partialID = $dob . $sequence . $citizenDigit . $randomA;

        // Calculate the checksum using the Luhn algorithm
        $checksum = self::calculateLuhn($partialID);

        // Return the complete 13-digit ID
        return $partialID . $checksum;
    }

    private static function calculateLuhn(string $number): string
    {
        $sum = 0;

        // Process only every second digit, starting from the right (0-based index)
        for ($i = 0; $i < strlen($number); $i++) {
            $digit = intval($number[$i]);

            // Double every second digit
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        // Compute the checksum digit
        return (10 - ($sum % 10)) % 10;
    }
}
