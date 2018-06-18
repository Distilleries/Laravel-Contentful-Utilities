<?php

use Distilleries\Contentful\Helpers\Image;

class ImageTest extends ContentfulTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Contentful\ServiceProvider',
            'AgentProviderTest',
        ];
    }

    public function testGetUrl()
    {
        $url = "http://test.com/test.jpg";

        $this->assertEquals(Image::url($url), $url . '?q=80&fit=fill');
    }

    public function testGetWebpEnabled()
    {
        // $app['config']->set('contentful.image.use_webp', true);

        $this->assertEquals(true, false);
    }

     public function testGetUrlWebpEnabledAndChrome()
    {
        $this->assertEquals(true, false);
    }
}
