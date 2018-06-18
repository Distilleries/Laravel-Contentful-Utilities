<?php

class ImageTest extends ContentfulTestCase {
    

    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Contentful\ContentfulServiceProvider',
            'AgentProviderTest'
        ];
    }

    public function testGetUrl()
    {
        $url = "http://test.com/test.jpg";

       
           $this->assertEquals( \Distilleries\Contentful\Helpers\Image::getUrl($url), $url.'?q=80&fit=fill');
    }

    public function testGetWebpEnabled()
    {
        // $app['config']->set('contentful.image.webp_enabled', true);

           $this->assertEquals(true, false);
    }

     public function testGetUrlWebpEnabledAndChrome()
    {
           $this->assertEquals(true, false);
    }
    
}


