<?php

namespace Distilleries\Contentful\Contentful\Webhook;

use Exception;
use Distilleries\Contentful\Eloquent;

class EntryHandler
{
    /**
     * Handle an incoming ContentManagementEntry request.
     * create, save, auto_save, archive, unarchive, publish, unpublish, delete
     *
     * @param  string  $action
     * @param  array  $payload
     * @return void
     */
    public function handle($action, $payload)
    {
        if (method_exists($this, $action)) {
            $this->$action($payload);
        }
    }

    /**
     * Create entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function create($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Save entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function auto_save($payload)
    {
        //
    }

    /**
     * Save entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function save($payload)
    {
        //
    }

    /**
     * Archive entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function archive($payload)
    {
        $this->delete($payload);
    }

    /**
     * Un-archive entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function unarchive($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Publish entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function publish($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Un-publish entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function unpublish($payload)
    {
        $this->delete($payload);
    }

    /**
     * Delete entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    protected function delete($payload)
    {
        $this->entryModel($payload)->where('contentful_id', '=', $payload['sys']['id'])->delete();
    }

    /**
     * Return entry for given payload.
     *
     * @param  array  $payload
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function upsert($payload)
    {
        $map = $this->entryMapper($payload)->map($payload);
        
        // @TODO DEBUG QA... webhook
        $entry = $this->entryModel($payload)->where('contentful_id', '=', $payload['sys']['id'])->first();
        if (empty($entry)) {
            $entry = $this->entryModel($payload)->forceFill($map);
        } else {
            foreach ($map['fields'] as $field => $value) {
                $entry->$field = $value;
            }
        }
        $entry->save();

        if (isset($map['relations']) and is_array($map['relations'])) {
            Eloquent::handleRelations($this->tableName($payload), $map['fields']['contentful_id'], $map['relations']);
        }

        return $entry;
    }

    /**
     * Return model name for given payload.
     *
     * @param  array  $payload
     * @return string
     */
    private function modelName($payload)
    {
        return studly_case(Eloquent::TABLE_PREFIX . str_singular($payload['sys']['contentType']['sys']['id']));
    }

    /**
     * Return model name for given payload.
     *
     * @param  array  $payload
     * @return string
     */
    private function tableName($payload)
    {
        return Eloquent::TABLE_PREFIX . str_plural($payload['sys']['contentType']['sys']['id']);
    }

    /**
     * Return model corresponding for given payload.
     *
     * @param  array  $payload
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function entryModel($payload)
    {
        $className = '\App\Models\\' . $this->modelName($payload);

        if (! class_exists($className)) {
            throw new Exception('Unknown model "' . $className . '"');
        }

        return new $className;
    }

    /**
     * Return model mapper corresponding for given payload.
     *
     * @param  array  $payload
     * @return \App\Models\Contentful\Mappers\MapperInterface
     * @throws \Exception
     */
    private function entryMapper($payload)
    {
        $className = '\App\Models\Contentful\Mappers\\' . $this->modelName($payload) . 'Mapper';

        if (! class_exists($className)) {
            throw new Exception('Unknown model mapper "' . $className . '"');
        }

        return new $className;
    }
}
