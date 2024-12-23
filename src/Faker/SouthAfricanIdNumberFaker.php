<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker\RsaIdNumber;

class SouthAfricanIdNumberFaker extends Base
{
    public function southAfricanIdNumber(
        ?Carbon $dateOfBirth = null,
        ?string $gender = null)
    : string
    {
        $dateOfBirth = $dateOfBirth ?? Carbon::now()->subYears(rand(2, 99))->addDays(rand(0, 364));
        $gender = $gender ?? $this->randomElement(['M', 'F']);

        return RsaIdNumber::generateRsaIdNumber($dateOfBirth, $gender);
    }

    public function southAfricanIdNumberFemale(?Carbon $dateOfBirth = null): string
    {
        return $this->southAfricanIdNumber($dateOfBirth, 'F');
    }

    public function southAfricanIdNumberMale(?Carbon $dateOfBirth = null): string
    {
        return $this->southAfricanIdNumber($dateOfBirth, 'M');
    }
}