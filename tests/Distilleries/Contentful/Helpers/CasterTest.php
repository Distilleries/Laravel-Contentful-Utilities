<?php

class CasterTest extends ContentfulTestCase
{

    public function testIsString()
    {

        $str = "http://test.com/test.jpg";

        $this->assertEquals(is_string(\Distilleries\Contentful\Helpers\Caster::string($str)), true);
    }

    public function testToJson()
    {

    	$data = array("name" => "John", "city" => "Paris", "age" => "23");

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::toJson($data), json_encode($data));
    }

    public function testFromJson()
    {
    	
    	$data = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::fromJson($data), json_decode($data, true));
    }
    
    public function testFromJsonEmpty()
    {
    	
    	$data = "";

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::fromJson($data), null);
    }

    public function testFromJsonErrorNone()
    {
    	
    	$data = '{"a":1;"b":2}';

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::fromJson($data), null);
    }

    public function testMarkdown()
    {
    	
    	$md = "#test" ;

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::markdown($md), "<h1>test</h1>");
    } 

    public function testMarkdownEmpty()
    {
    	
    	$md = "" ;

    	$this->assertEquals(\Distilleries\Contentful\Helpers\Caster::markdown($md), null);
    } 

    public function testInteger()
    {

    	$int = 15;

    	$this->assertEquals(is_integer(\Distilleries\Contentful\Helpers\Caster::integer($int)), true);

    }


    public function testEntryId()
    {

    	$entry = ['sys'=>['id'=>1]];

    	$this->assertEquals(is_string(\Distilleries\Contentful\Helpers\Caster::entryId($entry)), '1');

    }

}
 