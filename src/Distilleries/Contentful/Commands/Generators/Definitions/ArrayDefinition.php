<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Exception;
use Illuminate\Support\Carbon;
use Distilleries\Contentful\Eloquent;
use Distilleries\Contentful\Commands\Generators\Models as EloquentCommand;

class ArrayDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        $migrations = [];

        switch ($this->field['items']['type']) {
            case 'Link':
                switch ($this->field['items']['linkType']) {
                    case 'Asset':
                        $pivot = Eloquent::TABLE_PREFIX . 'asset';
                        break;
                    case 'Entry':
                        $pivot = Eloquent::TABLE_PREFIX . str_singular($this->id());
                        break;
                    default:
                        throw new Exception('Unknown Array items linkType "' . $this->field['items']['linkType'] . '"');
                }
                $table = str_singular($this->table) . '_' . $pivot;
                $this->createPivotMigration($table, [
                    "string('" . $pivot . Eloquent::CF_ID_FIELD_POSTFIX . "')->index()",
                    "integer('order')->unsigned()",
                ]);
                break;
            case 'Symbol':
                $migrations = [
                    "text('" . str_plural($this->id()) . "')",
                ];
                break;
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }

        return $migrations;
    }

    /**
     * Create pivot table migration with given fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return void
     */
    private function createPivotMigration($table, $fields)
    {
        $stubPath = __DIR__ . '/../stubs/migration.stub';
        $destPath = database_path('migrations/' . Carbon::now()->format('Y_m_d_His') . '_create_' . $table . '_table.php');

        $fileBasePath = EloquentCommand::writeStub($stubPath, $destPath, [
            'class' => studly_case($table),
            'table' => $table,
            'fields' => $this->migrationFields($fields),
        ]);

        echo 'Migration "' . $fileBasePath . '" created' . PHP_EOL;
    }

    /**
     * Generate migration fields definitions.
     *
     * @param  array  $fields
     * @return string
     */
    private function migrationFields($fields)
    {
        $migrations = [];

        array_unshift($fields, "string('" . str_singular($this->table) . Eloquent::CF_ID_FIELD_POSTFIX . "')->index()");
        foreach ($fields as $field) {
            $migrations[] = "\t\t\t\$table->" . $field . ";";
        }

        return implode("\n", $migrations);
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        switch ($this->field['items']['type']) {
            case 'Link':
                return [
                    "\\Illuminate\\Support\\Collection \$" . camel_case(Eloquent::TABLE_PREFIX . str_plural($this->id())),
                ];
            case 'Symbol':
                return [
                    "array \$" . str_plural($this->id()),
                ];
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modelFillable()
    {
        switch ($this->field['items']['type']) {
            case 'Link':
                return [];
            case 'Symbol':
                return [
                    str_plural($this->id()),
                ];
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        switch ($this->field['items']['type']) {
            case 'Link':
                return [];
            case 'Symbol':
                return [
                    ['key' => str_plural($this->id()), 'type' => 'array'],
                ];
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modelRelationship()
    {
        switch ($this->field['items']['type']) {
            case 'Link':
                switch ($this->field['items']['linkType']) {
                    case 'Asset':
                        $pivot = Eloquent::TABLE_PREFIX . 'asset';
                        break;
                    case 'Entry':
                        $pivot = Eloquent::TABLE_PREFIX . str_singular($this->id());
                        break;
                    default:
                        throw new Exception('Unknown Array items linkType "' . $this->field['items']['linkType'] . '"');
                }
                $relationType = 'belongsToMany';
                $relatedFunction = camel_case(Eloquent::TABLE_PREFIX . str_plural($this->id()));
                $relatedModel = studly_case($pivot);
                $table = str_singular($this->table) . '_' . $pivot;
                break;
            case 'Symbol':
                return [];
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }

        $code = [
            "/**",
            " * " . $relatedModel . " relationships.",
            " *",
            " * @return \\Illuminate\\Database\\Eloquent\\Relations\\" . studly_case($relationType),
            " */",
            "public function " . $relatedFunction . "()",
            "{",
            "\treturn \$this->" . $relationType . "(" . $relatedModel . "::class, '" . $table . "', '" . str_singular($this->table) . Eloquent::CF_ID_FIELD_POSTFIX . "', '" . $pivot . Eloquent::CF_ID_FIELD_POSTFIX . "', 'contentful_id', 'contentful_id');",
            "}",
        ];

        return implode("\n", array_map(function ($line) {
            return "\t" . $line;
        }, $code));
    }
}
