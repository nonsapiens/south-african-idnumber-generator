<?php

namespace Nonsapiens\SouthAfricanIdNumberFaker\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Nonsapiens\SouthAfricanIdNumberFaker\Providers\SouthAfricanIdNumberFakerServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SouthAfricanIdNumberFakerServiceProvider::class,
        ];
    }
}
