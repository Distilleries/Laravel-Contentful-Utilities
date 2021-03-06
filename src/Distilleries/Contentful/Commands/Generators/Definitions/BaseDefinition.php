<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Illuminate\Support\Str;

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
     * @return void
     */
    public function __construct($table, $field)
    {
        $this->table = $table;

        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function modelGetter();

    /**
     * Return normalized ID of current field.
     *
     * @return string
     */
    protected function id(): string
    {
        return $this->field['id'];
    }

    /**
     * Return studly case ID of current field.
     *
     * @return string
     */
    protected function studlyId(): string
    {
        return Str::studly($this->id());
    }

    /**
     * Return snake case ID of current field.
     *
     * @return string
     */
    protected function snakeId(): string
    {
        return Str::snake($this->id());
    }

    /**
     * Write stub to destination path with given string replacements.
     *
     * Return relative base path of destination path.
     *
     * @param  string  $stubPath
     * @param  array  $replacements
     * @return string
     */
    public static function getStub(string $stubPath, array $replacements = []): string
    {
        $content = file_get_contents($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace('{{' . Str::upper($key) . '}}', $value, $content);
        }

        return $content;
    }
}
