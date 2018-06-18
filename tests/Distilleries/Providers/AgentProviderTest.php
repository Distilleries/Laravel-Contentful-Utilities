<?php

use Mockery as m;

 use \Jenssegers\Agent\AgentServiceProvider;
 use \Jenssegers\Agent\Agent;


class AgentProviderTest extends AgentServiceProvider{
	

    /**
     * Register the service provider.
     */
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