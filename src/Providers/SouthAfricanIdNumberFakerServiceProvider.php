<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Providers;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;
use Nonsapiens\SouthAfricanIdNumberFaker\Faker\SouthAfricanIdNumberFaker;

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
}