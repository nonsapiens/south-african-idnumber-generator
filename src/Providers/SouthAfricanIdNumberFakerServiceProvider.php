<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Providers;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Nonsapiens\SouthAfricanIdNumberFaker\Faker\SouthAfricanIdNumberFaker;
use Nonsapiens\SouthAfricanIdNumberFaker\RsaIdNumber;

class SouthAfricanIdNumberFakerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Generator::class, function ($app) {
            $faker = Factory::create(config('app.faker_locale', 'en_ZA'));
            $faker->addProvider(new SouthAfricanIdNumberFaker($faker));

            return $faker;
        });
    }

    public function boot(): void
    {
        // Add a validator rule: rsaidnumber
        Validator::extend('rsaidnumber', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            try {
                return (new RsaIdNumber($value))->isValid();
            } catch (\Throwable $e) {
                return false;
            }
        }, 'The :attribute must be a valid South African ID number.');
    }
}