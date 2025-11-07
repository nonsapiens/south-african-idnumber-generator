<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker;

use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker\Exceptions\RsaIdNumberException;

class RsaIdNumber implements \Stringable
{
    protected string $rsaIdNumber;

    /**
     * @param string $rsaIdNumber The 13-digit RSA ID number to validate
     */
    public function __construct(string $rsaIdNumber) {
        $this->rsaIdNumber = preg_replace("/\s/", '', $rsaIdNumber);
    }

    /**
     * @param bool $throwExceptions Whether to throw exceptions on validation failure
     * @return bool True if the ID number is valid, false otherwise
     * @throws RsaIdNumberException If the ID number is invalid and $throwExceptions is true
     */
    public function isValid(bool $throwExceptions = false): bool
    {
        # Validate $this->rsaIdNumber
        // Ensure the ID number is exactly 13 digits long
        if (!preg_match('/^\d{13}$/', $this->rsaIdNumber)) {
            if ($throwExceptions) {
                throw new RsaIdNumberException('ID number must be exactly 13 digits.');
            }
            return false;
        }

        // Extract components of the ID number
        $birthDate = substr($this->rsaIdNumber, 0, 6);
        $genderCode = substr($this->rsaIdNumber, 6, 1);
        $citizenCode = substr($this->rsaIdNumber, 10, 1);

        // Validate the birthdate (format YYMMDD)
        if (!$this->isValidDate($birthDate)) {
            if ($throwExceptions) {
                throw new RsaIdNumberException('Invalid birth date in ID number');
            } else {
                return false;
            }
        }

        // Validate the gender code (should be between 0 and 9)
        if (!ctype_digit($genderCode)) {
            if ($throwExceptions) {
                throw new RsaIdNumberException('Invalid gender code in ID number');
            } else {
                return false;
            }
        }

        // Validate the citizenship code (0 for SA citizen, 1 for permanent resident)
        if (!in_array($citizenCode, ['0', '1'])) {
            if ($throwExceptions) {
                throw new RsaIdNumberException('Invalid citizenship code in ID number');
            } else {
                return false;
            }
        }

        // Perform the Luhn algorithm checksum validation
        if (!$this->isValidLuhn($this->rsaIdNumber)) {
            if ($throwExceptions) {
                throw new RsaIdNumberException('Invalid checksum in ID number');
            } else {
                return false;
            }
        }

        return true;
    }

    public function gender(): ?string
    {
        if ($this->isValid()) {
            # Return 'm' for male, 'f' for female
            return ($this->rsaIdNumber[6] < 5) ? 'f' : 'm';
        } else {
            return null;
        }
    }

    public function dateOfBirth(string $format = 'Y-m-d'): ?string
    {
        if ($this->isValid()) {
            $year = substr($this->rsaIdNumber, 0, 2);
            $month = substr($this->rsaIdNumber, 2, 2);
            $day = substr($this->rsaIdNumber, 4, 2);

            $fullYear = ($year > date('y')) ? '19' . $year : '20' . $year;

            return Carbon::createFromFormat('Y-m-d', $fullYear . '-' . $month . '-' . $day)->format($format);
        } else {
            return null;
        }
    }

    public function age(): ?int
    {
        if ($this->isValid()) {
            return Carbon::parse($this->dateOfBirth())->age;
        } else {
            return null;
        }
    }

    public function isAdult(int $adultAge = 18): ?bool
    {
        if ($this->isValid()) {
            return $this->age() >= $adultAge;
        } else {
            return null;
        }
    }

    public function isCitizen(): ?bool
    {
        if ($this->isValid()) {
            return $this->rsaIdNumber[10] === '0';
        } else {
            return null;
        }
    }

    public function isPermanentResident(): ?bool
    {
        if ($this->isValid()) {
            return $this->rsaIdNumber[10] === '1';
        } else {
            return null;
        }
    }

    public function toNatural(): ?string
    {
        if ($this->isValid()) {
            return substr($this->rsaIdNumber, 0, 6) . ' ' . substr($this->rsaIdNumber, 6, 4) . ' ' . substr($this->rsaIdNumber, 10, 3);
        } else {
            return null;
        }
    }

    /**
     * Check if the given birthdate in YYMMDD format is valid.
     *
     * @param string $birthDate
     * @return bool
     */
    private function isValidDate(string $birthDate): bool
    {
        $year = substr($birthDate, 0, 2);
        $month = substr($birthDate, 2, 2);
        $day = substr($birthDate, 4, 2);

        $fullYear = ($year > date('y')) ? '19' . $year : '20' . $year;

        return checkdate((int)$month, (int)$day, (int)$fullYear);
    }

    /**
     * Validate the ID number using the Luhn algorithm.
     *
     * @param string $idNumber
     * @return bool
     */
    private function isValidLuhn(string $idNumber): bool
    {
        $sum = 0;
        $alternate = false;

        // Start from the last digit and move backwards
        for ($i = strlen($idNumber) - 1; $i >= 0; $i--) {
            $digit = (int)$idNumber[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return $sum % 10 === 0;
    }

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

    public function __toString(): string
    {
        return $this->rsaIdNumber;
    }
}
