<?php

use Mockery as m;
use Jenssegers\Agent\Agent;
use Jenssegers\Agent\AgentServiceProvider;

class AgentProviderTest extends AgentServiceProvider
{
    public function register()
    {
        $this->app->singleton('agent', function ($app) {
            $agent = m::mock();

            $agent->shouldReceive('browser')
                ->andReturn('chrome');

            $agent->shouldReceive('isMobile')
                ->andReturn(false);

            return $agent;
        });

        $this->app->alias('agent', Agent::class);
    }
}
