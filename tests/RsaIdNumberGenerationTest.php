<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Tests;

use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker\RsaIdNumber;

class RsaIdNumberGenerationTest extends TestCase
{
    public function test_generates_13_digit_numeric_string(): void
    {
        $dob = Carbon::create(1990, 1, 1);
        $id = RsaIdNumber::generateRsaIdNumber($dob, 'm', true);

        $this->assertIsString($id);
        $this->assertSame(13, strlen($id));
        $this->assertMatchesRegularExpression('/^\d{13}$/', $id);
    }

    public function test_embeds_birthdate_in_first_six_digits(): void
    {
        // Check multiple boundary dates incl. century edge
        $dates = [
            Carbon::create(1985, 7, 9),
            Carbon::create(1999, 12, 31),
            Carbon::create(2000, 1, 1),
            Carbon::create(2001, 11, 30),
            Carbon::create(2000, 2, 29), // leap day in a leap year (2000)
        ];

        foreach ($dates as $dob) {
            $id = RsaIdNumber::generateRsaIdNumber($dob, 'f', true);
            $expectedYYMMDD = substr((string)$dob->year, -2) . str_pad((string)$dob->month, 2, '0', STR_PAD_LEFT) . str_pad((string)$dob->day, 2, '0', STR_PAD_LEFT);
            $this->assertSame($expectedYYMMDD, substr($id, 0, 6), 'YYMMDD not encoded correctly');

            // Round-trip through parser
            $parsed = new RsaIdNumber($id);
            $this->assertTrue($parsed->isValid(), 'Generated ID should be valid');
            $this->assertSame($dob->format('Y-m-d'), $parsed->dateOfBirth('Y-m-d'));
        }
    }

    public function test_gender_sequence_distribution_and_accessors(): void
    {
        // For males, first digit of sequence (position 6) should be 5-9; for females, 0-4
        $dob = Carbon::create(1990, 5, 20);

        // Run multiple times to cover randomness
        for ($i = 0; $i < 50; $i++) {
            $maleId = RsaIdNumber::generateRsaIdNumber($dob, 'm', true);
            $femaleId = RsaIdNumber::generateRsaIdNumber($dob, 'f', true);

            $this->assertGreaterThanOrEqual(5, (int)$maleId[6]);
            $this->assertLessThan(5, (int)$femaleId[6]);

            // Accessor gender should match
            $this->assertSame('m', (new RsaIdNumber($maleId))->gender());
            $this->assertSame('f', (new RsaIdNumber($femaleId))->gender());
        }
    }

    public function test_citizenship_digit_and_accessors(): void
    {
        $dob = Carbon::create(1990, 5, 20);

        $sa = RsaIdNumber::generateRsaIdNumber($dob, 'm', true);
        $pr = RsaIdNumber::generateRsaIdNumber($dob, 'f', false);

        $this->assertSame('0', $sa[10], 'SA citizen digit should be 0');
        $this->assertSame('1', $pr[10], 'Permanent resident digit should be 1');

        $this->assertTrue((new RsaIdNumber($sa))->isCitizen());
        $this->assertFalse((new RsaIdNumber($sa))->isPermanentResident());

        $this->assertFalse((new RsaIdNumber($pr))->isCitizen());
        $this->assertTrue((new RsaIdNumber($pr))->isPermanentResident());
    }

    public function test_random_misc_digit_is_8_or_9(): void
    {
        $dob = Carbon::create(1990, 5, 20);
        for ($i = 0; $i < 50; $i++) {
            $id = RsaIdNumber::generateRsaIdNumber($dob, 'm', true);
            $this->assertContains($id[11], ['8', '9']);
        }
    }

    public function test_luhn_checksum_is_valid_for_generated_numbers(): void
    {
        $dob = Carbon::create(1990, 5, 20);

        // Generate a bunch and verify via validator object
        for ($i = 0; $i < 200; $i++) {
            $gender = ($i % 2 === 0) ? 'm' : 'f';
            $citizen = ($i % 3 !== 0);

            $id = RsaIdNumber::generateRsaIdNumber($dob, $gender, $citizen);
            $this->assertTrue((new RsaIdNumber($id))->isValid(), "Generated ID failed Luhn/format validation: $id");
        }
    }

    public function test_to_natural_formatting_round_trip(): void
    {
        $dob = Carbon::create(1993, 9, 17);
        $id = RsaIdNumber::generateRsaIdNumber($dob, 'f', false);

        $natural = (new RsaIdNumber($id))->toNatural();
        $this->assertNotNull($natural);
        $this->assertMatchesRegularExpression('/^\d{6}\s\d{4}\s\d{3}$/', $natural);

        // Ensure that spaces are stripped by constructor and it still validates
        $idWithSpaces = $natural;
        $this->assertTrue((new RsaIdNumber($idWithSpaces))->isValid(), 'Constructor should accept spaces and still validate');
    }

    public function test_age_and_is_adult_accessors_from_generated_numbers(): void
    {
        $today = Carbon::now();

        $nineteenYearsAgo = $today->copy()->subYears(19);
        $seventeenYearsAgo = $today->copy()->subYears(17);

        $adultId = RsaIdNumber::generateRsaIdNumber($nineteenYearsAgo, 'm', true);
        $minorId = RsaIdNumber::generateRsaIdNumber($seventeenYearsAgo, 'f', true);

        $adult = new RsaIdNumber($adultId);
        $minor = new RsaIdNumber($minorId);

        $this->assertNotNull($adult->age());
        $this->assertNotNull($minor->age());

        $this->assertTrue($adult->isAdult());
        $this->assertFalse($minor->isAdult());
    }

    public function test_many_random_samples_property_style(): void
    {
        // Sample a variety of dates across decades and combinations
        $years = [1980, 1990, 1999, 2000, 2001, 2010, (int)Carbon::now()->year - 1];
        $months = range(1, 12);
        $days = [1, 15, 28];

        foreach ($years as $y) {
            foreach ($months as $m) {
                foreach ($days as $d) {
                    // Skip invalid dates like Feb 30; Carbon will fix overflow if not careful
                    if (!checkdate($m, $d, $y)) {
                        continue;
                    }
                    $dob = Carbon::create($y, $m, $d);

                    foreach (["m", "f"] as $gender) {
                        foreach ([true, false] as $citizen) {
                            for ($i = 0; $i < 3; $i++) {
                                $id = RsaIdNumber::generateRsaIdNumber($dob, $gender, $citizen);
                                $r = new RsaIdNumber($id);
                                $this->assertTrue($r->isValid(), "Generated ID should be valid for $y-$m-$d, $gender, citizen=" . ($citizen ? '1' : '0'));

                                // Check invariants again
                                $this->assertSame(substr((string)$y, -2) . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . str_pad((string)$d, 2, '0', STR_PAD_LEFT), substr($id, 0, 6));
                                $this->assertSame($citizen ? '0' : '1', $id[10]);
                                $this->assertSame($gender, $r->gender());
                            }
                        }
                    }
                }
            }
        }
    }
}
