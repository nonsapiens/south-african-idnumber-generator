<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker\RsaIdNumber;

class SouthAfricanIdNumberFaker extends Base
{

    /**
     * @param string|Carbon|mixed $dateOfBirth
     * @param string|null $gender M or F
     * @return string
     */
    public function southAfricanIdNumber(
        mixed $dateOfBirth = null,
        ?string $gender = null)
    : string
    {
        if (is_null($dateOfBirth)) {
            $dateOfBirth = Carbon::now()->subYears(rand(2, 99))->addDays(rand(0, 364));
        } else {
            $dateOfBirth = Carbon::parse($dateOfBirth);
        }
        $dateOfBirth = $dateOfBirth ?? Carbon::now()->subYears(rand(2, 99))->addDays(rand(0, 364));
        $gender = $gender ?? $this->randomElement(['m', 'f']);

        return RsaIdNumber::generateRsaIdNumber($dateOfBirth, $gender);
    }

    /**
     * @param Carbon|null $dateOfBirth
     * @return string
     */
    public function southAfricanIdNumberFemale(?Carbon $dateOfBirth = null): string
    {
        return $this->southAfricanIdNumber($dateOfBirth, 'f');
    }

    /**
     * @param Carbon|null $dateOfBirth
     * @return string
     */
    public function southAfricanIdNumberMale(?Carbon $dateOfBirth = null): string
    {
        return $this->southAfricanIdNumber($dateOfBirth, 'm');
    }
}