<?php

namespace Distilleries\Contentful\Contentful\Commands;

use Illuminate\Console\Command;
use Distilleries\Contentful\Eloquent;
use App\Services\Contentful\Api\ManagementApi;

abstract class BaseCommand extends Command
{
    /**
     * Contentful Management API implementation.
     *
     * @var \App\Services\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\Contentful\Api\ManagementApi  $api
     */
    public function __construct(ManagementApi $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * Return content-type corresponding table string ID.
     *
     * @param  string  $id
     * @return string
     */
    protected function tableName($id)
    {
        return Eloquent::TABLE_PREFIX . str_plural($id);
    }
}
