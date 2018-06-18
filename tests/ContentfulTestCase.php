<?php

use Mockery as m;
use Orchestra\Testbench\TestCase;

abstract class ContentfulTestCase extends TestCase
{
    protected $facade;

    protected function initService()
    {
        $service = $this->app->getProvider('Distilleries\Contentful\ServiceProvider');

        $service->boot();
        $service->register();

        return $service;
    }

    public function setUp()
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'prefix' => '',
            'database' => ':memory:',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Contentful\ServiceProvider',
        ];
    }

    protected function getPackageAliases($app)
    {
        return [];
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    public function assertHTTPExceptionStatus($expectedStatusCode, Closure $statusCodeReturned)
    {
        $code = $statusCodeReturned($this);
        $this->assertEquals(
            $expectedStatusCode,
            $statusCodeReturned($this),
            sprintf("Expected an HTTP status of %d but got %d.", $expectedStatusCode, $code)
        );
    }
}