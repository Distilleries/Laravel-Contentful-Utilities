<?php

namespace Distilleries\Contentful\Webhook;

use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Repositories\EntriesRepository;

class EntryHandler
{
    /**
     * Entries repository implementation.
     *
     * @var \Distilleries\Contentful\Repositories\EntriesRepository
     */
    protected $entries;

    /**
     * EntryHandler constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->entries = new EntriesRepository;
    }

    /**
     * Handle an incoming ContentManagementEntry request.
     * (create, save, auto_save, archive, unarchive, publish, unpublish, delete)
     *
     * @param  string  $action
     * @param  array  $payload
     * @param  boolean  $isPreview
     * @return void
     */
    public function handle(string $action, array $payload, bool $isPreview)
    {
        $actionMethods = ['create', 'archive', 'unarchive', 'publish', 'unpublish', 'delete'];
        $actionMethods = ! empty($isPreview) ? array_merge($actionMethods, ['save', 'auto_save']): $actionMethods;

        if (method_exists($this, $action) and in_array($action, $actionMethods)) {
            $this->$action($payload);
        }
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Auto-save entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function auto_save($payload)
    {
        $this->upsertEntry($payload);
    }

    /**
     * Save entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function save($payload)
    {
        $this->upsertEntry($payload);
    }

    /**
     * Create entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function create($payload)
    {
        $this->upsertEntry($payload);
    }

    /**
     * Archive entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function archive($payload)
    {
        $this->deleteEntry($payload);
    }

    /**
     * Un-archive entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function unarchive($payload)
    {
        $this->upsertEntry($payload);
    }

    /**
     * Publish entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function publish($payload)
    {
        $this->upsertEntry($payload);
    }

    /**
     * Un-publish entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function unpublish($payload)
    {
        $this->deleteEntry($payload);
    }

    /**
     * Delete entry.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function delete($payload)
    {
        $this->deleteEntry($payload);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Upsert entry in DB.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */

    private function upsertEntry($payload)
    {
        $this->entries->toContentfulModel($payload,Locale::all());
    }

    /**
     * Delete entry from DB.
     *
     * @param  array  $payload
     * @return void
     * @throws \Exception
     */
    private function deleteEntry($payload)
    {
        $this->entries->delete($payload);
    }
}
