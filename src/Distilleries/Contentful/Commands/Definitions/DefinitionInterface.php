<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

interface DefinitionInterface
{
    /**
     * Return migration type string (e.g. "string('name')->nullable").
     *
     * @return array
     */
    public function migration();

    /**
     * Return model DocBlock properties (e.g. ["string $contentful_id"]).
     *
     * @return array
     */
    public function modelProperties();

    /**
     * Return model fillable key (field ID by default).
     *
     * @return array
     */
    public function modelFillable();

    /**
     * Return model cast key - type array (array of associative ['key', 'type']).
     *
     * @return array
     */
    public function modelCast();

    /**
     * Return relationship function code.
     *
     * @return string
     */
    public function modelRelationship();
}