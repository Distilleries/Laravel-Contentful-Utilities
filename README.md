[![Build Status](https://travis-ci.org/Distilleries/Laravel-Contentful-Utilities.svg?branch=master)](https://travis-ci.org/Distilleries/Laravel-Contentful-Utilities) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Distilleries/Laravel-Contentful-Utilities/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Laravel-Contentful-Utilities/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Distilleries/Laravel-Contentful-Utilities/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Laravel-Contentful-Utilities/?branch=master)
[![Total Downloads](https://poser.pugx.org/distilleries/contentful/downloads)](https://packagist.org/packages/distilleries/contentful)
[![Latest Stable Version](https://poser.pugx.org/distilleries/contentful/version)](https://packagist.org/packages/distilleries/contentful)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE) 

# Distilleries / Laravel-Contentful-Utilities

Laravel-Contentful-Utilities is a Laravel 5.6 / Lumen package 5.6 package to use [contentful](https://www.contentful.com/) in offline mode with and without preview.
Contentful is a headless CMS in cloud you can have more information on their website https://www.contentful.com


### Features

* Model generator from contentful
* Migration generator from contentful
* Synchronization from contentful to database


### Installation
#### Composer
Install the [composer package] by running the following command:

    composer require distilleries/contentful

### Models and Mapper

When we synchronize all the data on database the mapper link to the model are call. This mapper car provide the extract of field you would like one the database.
For example you want to externilize the title and the slug on the database you have to change the migation generated and the mapper


    class TerritoryMapper extends ContentfulMapper
    {
        /**
         * {@inheritdoc}
         */
        protected function map(array $entry, string $locale) : array
        {
            $payload = $this->mapPayload($entry, $locale);
    
            return [
                'slug' => isset($payload['slug']) ? Caster::string($payload['slug']) : '',
                'title' => isset($payload['title']) ? Caster::string($payload['title']) : '',
            ];
        }
    }
    
    
    <?php
    
    namespace App\Models;
    
    use Illuminate\Support\Collection;
    use Distilleries\Contentful\Models\Asset;
    use Distilleries\Contentful\Helpers\Caster;
    use Distilleries\Contentful\Models\Base\ContentfulModel;
    
    /**
     * @property string $contentful_id
     * @property string $locale
     * @property string $country
     * @property string $slug
     * @property string $url
     * @property array $payload
     * @property string $title
     * @property \Distilleries\Contentful\Models\Asset $picture
     * @property \Illuminate\Support\Carbon $created_at
     * @property \Illuminate\Support\Carbon $updated_at
     */
    class Territory extends ContentfulModel
    {
        /**
         * {@inheritdoc}
         */
        protected $table = 'territories';
    
        /**
         * {@inheritdoc}
         */
        protected $fillable = [
            'slug',
            'title',
        ];
    
    
        /**
         * Picture attribute accessor.
         *
         * @return \Distilleries\Contentful\Models\Asset|null
         */
        public function getPictureAttribute() : ?Asset
        {
            return isset($this->payload['picture']) ? $this->contentfulAsset($this->payload['picture']) : null;
        }
    
    }


 
All the model generated have a getters for all the payload fields. If you want to externilize the field on database


### Command-line tools

To make model and mapper from contentful

* php artisan contentful:generate:models

> Models à generated on app_path('Models'); and the mappers à generated on app_path('Models/Mappers');

To make migration from contentful model

* php artisan contentful:generate:migrations


To launch the synchronisation you can use this command line 

 * php artisan contentful:sync-data  {--preview}
 * php artisan contentful:sync-flatten  {--preview}
 
 > --preview is optional and use if you want to flatten the preview database.
 
 | Command | Explain |
 | -------: | -------: |
 | sync-data | Get all the entries from contentful and put in the flatten database | 
 | sync-flatten | Get all the entries from data table to explode on all the other types  |
 
 
 ### Webhook
 To flatten the preview or the regular database you need to set the webhook on contentful
 
 Create a controller and use the trait :
 
    use \Distilleries\Contentful\Http\Controllers\WebhookTrait;
    
    
Make the route callable in post 

    $router->post('/webhook/live', 'WebhookController@live');
    $router->post('/webhook/preview', 'WebhookController@preview');

* Live method is called to save on live mode
* Preview metho is called to save the preview datas


To display the site with preview database you have to use UsePreview middleware 

        $router->group(['prefix' => 'preview', 'middleware' => 'use_preview'], function () use ($router) {
        });


Add your middleware :

    'use_preview' => Distilleries\Contentful\Http\Middleware\UsePreview::class,
