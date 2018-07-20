<?php

namespace Distilleries\Contentful\Repositories\Traits;

use Illuminate\Support\Facades\DB;

trait EntryType
{
    /**
     * Insert / update entry type for given Contentful entry.
     *
     * @param  array  $entry
     * @param  string  $contentfulType
     * @return void
     */
    protected function upsertEntryType(array $entry, string $contentfulType)
    {

        $pivot = DB::table('entry_types')
            ->where('contentful_id', '=', $entry['sys']['id'])
            ->first();
        if (empty($pivot)) {
            DB::table('entry_types')
                ->insert([
                    'contentful_id' => $entry['sys']['id'],
                    'contentful_type' => $contentfulType,
                ]);
        } else {
            DB::table('entry_types')
                ->where('contentful_id', '=', $entry['sys']['id'])
                ->update([
                    'contentful_type' => $contentfulType,
                ]);
        }
    }

    /**
     * Delete entry types for given Contentful entry.
     *
     * @param  string  $entryId
     * @return void
     */
    protected function deleteEntryType(string $entryId)
    {
        DB::table('entry_types')
            ->where('contentful_id', '=', $entryId)
            ->delete();
    }
}
