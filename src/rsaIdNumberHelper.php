<?php

use Illuminate\Support\Carbon;

function generateRsaIdNumber(Carbon $dateOfBirth, string $gender): string
{
    $dateOfBirth = $dateOfBirth->format('ymd');
    $gender = ($gender === 'M') ? '5' : '4';
    $sequence = rand(0, 9999);
    $checksum = calculateIdSequenceChecksum($dateOfBirth, $gender, $sequence);

    return $dateOfBirth . $gender . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT) . $checksum;
}

function calculateIdSequenceChecksum(string $dateOfBirth, string $gender, string $sequence): string
{
    $gender = trim(strtoupper($gender));
    $idNumber = $dateOfBirth . $gender . $sequence;
    $checksum = 0;

    foreach (str_split($idNumber) as $index => $digit) {
        $multiplier = ($index % 2 === 0) ? 1 : 2;
        $product = $digit * $multiplier;
        $checksum += ($product >= 10) ? $product - 9 : $product;
    }

    $checksum = (10 - ($checksum % 10)) % 10;

    return (string) $checksum;
}