<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

abstract class BaseDefinition implements DefinitionInterface
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table;

    /**
     * Field data.
     *
     * @var array
     */
    protected $field;

    /**
     * BaseDefinition constructor.
     *
     * @param  string  $table
     * @param  array  $field
     */
    public function __construct($table, $field)
    {
        $this->table = $table;

        $this->field = $field;
    }

    /**
     * Return database type structure for current field (e.g. "integer('count')->unsigned()").
     *
     * @return array
     */
    abstract protected function migrationType();

    /**
     * {@inheritdoc}
     */
    abstract public function modelProperties();

    /**
     * {@inheritdoc}
     */
    public function modelFillable()
    {
        return [
            $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function modelRelationship()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function migration()
    {
        $migrations = $this->migrationType();

        foreach ($migrations as & $migration) {
            if (strpos($migration, '->index()') === false) {
                $migration .= ! $this->field['required'] ? '->nullable()' : '';
            }
        }

        return $migrations;
    }

    /**
     * Return normalized ID of current field.
     *
     * @return string
     */
    protected function id()
    {
        return mb_strtolower(snake_case($this->field['id']));
    }
}
