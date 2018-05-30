<?php

namespace Distilleries\Contentful\Contentful;

use Illuminate\Support\Facades\DB;

class Eloquent
{
    /**
     * Contentful table prefix (e.g. 'cf_').
     *
     * @var string
     */
    const TABLE_PREFIX = '';

    /**
     * Contentful related ID postfix.
     *
     * @var string
     */
    const CF_ID_FIELD_POSTFIX = '_contentful_id';

    /**
     * Return DB table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table($table)
    {
        return DB::table($table);
    }

    /**
     * Handle given table element ID relations with given relations data ($map['relations']).
     *
     * @param  string  $table
     * @param  string  $id
     * @param  array  $relations
     * @return void
     */
    public static function handleRelations($table, $id, $relations)
    {
        $pivotField = str_singular($table) . static::CF_ID_FIELD_POSTFIX;

        foreach ($relations as $pivotTable => $data) {
            static::table($pivotTable)->truncate();

            $foreignField = $data['field'];

            $pivotEntries = array_map(function ($foreignId) use ($pivotField, $id, $foreignField) {
                return [
                    $pivotField => $id,
                    $foreignField => $foreignId,
                ];
            }, $data['ids']);

            $order = 0;
            foreach ($pivotEntries as $pivotEntry) {
                static::table($pivotTable)->insert($pivotEntry + [
                    'order' => $order,
                ]);
                $order++;
            }
        }
    }
}
