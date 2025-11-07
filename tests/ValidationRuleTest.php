<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Tests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker\RsaIdNumber;

class ValidationRuleTest extends TestCase
{
    public function test_rsa_id_number_rule_passes_for_valid_number(): void
    {
        $valid = RsaIdNumber::generateRsaIdNumber(Carbon::create(1990, 1, 1), 'm', true);

        $validator = Validator::make([
            'rsaIdNumber' => $valid,
        ], [
            'rsaIdNumber' => 'required|string|rsaidnumber',
        ]);

        $this->assertTrue($validator->passes(), 'Expected rsaidnumber rule to pass for a valid ID');
    }

    public function test_rsa_id_number_rule_fails_for_invalid_number(): void
    {
        $valid = RsaIdNumber::generateRsaIdNumber(Carbon::create(1990, 1, 1), 'f', true);
        // Corrupt the checksum (last digit)
        $last = (int) substr($valid, -1);
        $badLast = ($last + 1) % 10;
        $invalid = substr($valid, 0, -1) . $badLast;

        $validator = Validator::make([
            'rsaIdNumber' => $invalid,
        ], [
            'rsaIdNumber' => 'required|string|rsaidnumber',
        ]);

        $this->assertFalse($validator->passes(), 'Expected rsaidnumber rule to fail for an invalid ID');
        $this->assertArrayHasKey('rsaIdNumber', $validator->errors()->toArray());
    }
}
