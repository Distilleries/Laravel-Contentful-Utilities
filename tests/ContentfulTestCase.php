<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/08/2016
 * Time: 10:51
 */

use \Mockery as m;

abstract class ContentfulTestCase extends \Orchestra\Testbench\TestCase
{
    protected $facade;

    protected function initService()
    {
        $service       = $this->app->getProvider('Distilleries\Messenger\ContentfulServiceProvider');

        $service->boot();
        $service->register();

        return $service;
    }

    public function setUp()
    {
        parent::setUp();
        $this->app['Illuminate\Contracts\Console\Kernel']->call('vendor:publish');
        $this->artisan('migrate');
    }



    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }


    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Messenger\ContentfulServiceProvider',
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
        ];
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