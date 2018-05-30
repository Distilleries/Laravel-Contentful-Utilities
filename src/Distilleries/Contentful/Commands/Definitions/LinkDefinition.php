<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

use Exception;
use Distilleries\Contentful\Eloquent;

class LinkDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "string('" . $this->id() . Eloquent::CF_ID_FIELD_POSTFIX . "')->index()",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        switch ($this->field['linkType']) {
            case 'Asset':
                $model = 'asset';
                break;
            case 'Entry':
                if (! isset($this->field['validations'][0])) {
                    throw new Exception('A content-type must be set to LinkDefinition "' . $this->field['id'] . '"');
                }
                $model = $this->field['validations'][0]['linkContentType'][0];
                break;
            default;
                throw new Exception('Unknown Link linkType "' . $this->field['linkType'] . '"');
        }

        return [
            "string \$" . $this->id() . Eloquent::CF_ID_FIELD_POSTFIX,
            "\\App\\Models\\" . studly_case(Eloquent::TABLE_PREFIX . $model) . " \$" . snake_case($this->id()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelFillable()
    {
        return [
            $this->id() . Eloquent::CF_ID_FIELD_POSTFIX,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelRelationship()
    {
        switch ($this->field['linkType']) {
            case 'Asset':
                $model = 'asset';
                break;
            case 'Entry':
                if (! isset($this->field['validations'][0])) {
                    throw new Exception('A content-type must be set to LinkDefinition "' . $this->field['id'] . '"');
                }
                $model = $this->field['validations'][0]['linkContentType'][0];
                break;
            default;
                throw new Exception('Unknown Link linkType "' . $this->field['linkType'] . '"');
        }

        $relatedFunction = camel_case($this->id());
        $relatedModel = studly_case(Eloquent::TABLE_PREFIX . $model);
        $ownerKey = $this->id() . Eloquent::CF_ID_FIELD_POSTFIX;

        $code = [
            "/**",
            " * " . class_basename($relatedModel) . " relationship.",
            " *",
            " * @return \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo",
            " */",
            "public function " . $relatedFunction . "()",
            "{",
            "\treturn \$this->belongsTo(" . $relatedModel . "::class, '" . $ownerKey . "', 'contentful_id');",
            "}",
        ];

        return implode("\n", array_map(function ($line) {
            return "\t" . $line;
        }, $code));
    }
}
