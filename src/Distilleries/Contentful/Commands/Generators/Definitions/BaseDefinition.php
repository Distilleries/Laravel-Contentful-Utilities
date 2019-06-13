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
        return Str::lower(Str::snake($this->field['id']));
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
     * Return attribute of current field.
     *
     * @return string
     */
    protected function attribute(): string
    {
        $attribute = $this->studlyId();

        return Str::lower(Str::substr($attribute, 0, 1)) . Str::substr($attribute, 1);
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
