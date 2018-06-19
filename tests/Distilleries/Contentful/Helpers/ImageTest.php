<?php

class ImageTest extends ContentfulTestCase {
    

    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Contentful\ServiceProvider',
            'AgentProviderTest'
        ];
    }


    public function testNoUrl()
    {
        $url = "";

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url);
    }

    public function testGetUrl()
    {
        $url = "http://test.com/test.jpg";

        $this->app->make('config')->set('contentful.image.use_progressive',null);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url.'?q=80&fit=fill');
    }

     public function testGetUrlWithParameters()
    {
        $url = "http://test.com/test.jpg?q=80&fit=fill";
        $this->app->make('config')->set('contentful.image.use_progressive',null);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url);
    }

    public function testGetWebpEnabled()
    {

        $url = "http://test.com/test.jpg";

        $this->app->make('config')->set('contentful.image.use_webp',true);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url.'?q=80&fm=webp&fit=fill');

    }


    
    public function testGetWebpDisabled()
    {

        $url = "http://test.com/test.jpg";

        $this->app->make('config')->set('contentful.image.use_progressive',null);
        $this->app->make('config')->set('contentful.image.use_webp',false);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url.'?q=80&fit=fill');

    }

   public function testProgressiveMode()
    {

        $url = "http://test.com/test.jpg";

        $this->app->make('config')->set('contentful.image.use_webp',false);
        $this->app->make('config')->set('contentful.image.use_progressive','progressive');

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url.'?q=80&fl=progressive&fit=fill');

    }


    public function testGetUrlWithReplaceHost()
    {
        $url = "http://test.com/test.jpg";

        $this->app->make('config')->set('contentful.image.search_hosts','test.com');
        $this->app->make('config')->set('contentful.image.replace_host','test-destination.com');

        $this->app->make('config')->set('contentful.image.use_progressive',null);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url),'http://test-destination.com/test.jpg?q=80&fit=fill');

    }

    public function testGetUrlWithWebp()
    {
        $url = "http://test.com/test.jpg?";

        $this->app->make('config')->set('contentful.image.use_webp',true);

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url), $url.'q=80&fm=webp&fit=fill');
    }

    public function testGetUrlWithoutWebp()
    {
        $url = "http://test.com/test.jpg?";

        $this->app->make('config')->set('contentful.image.use_webp',false);
        $this->app->make('config')->set('contentful.image.use_progressive','progressive');

        $this->assertEquals( \Distilleries\Contentful\Helpers\Image::url($url, 100, 100, '', 80, 'progressive', 'fill'), $url.'w=100&h=100&q=80&fl=progressive&fit=fill');

    }

}


