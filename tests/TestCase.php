<?php

namespace Codrasil\Closurable\Tests\Unit;

use Codrasil\Closurable\ClosurableServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return \Codrasil\Closurable\ClosurableServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [ClosurableServiceProvider::class];
    }
}
