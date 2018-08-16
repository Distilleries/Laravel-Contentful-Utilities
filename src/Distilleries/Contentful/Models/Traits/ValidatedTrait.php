<?php

namespace Distilleries\Contentful\Models\Traits;

use Distilleries\Contentful\Models\Scopes\ValidatedScope;

trait ValidatedTrait
{

    abstract public function withoutGlobalScope($scope);

    /**
     * Boot the validated scope for a model.
     *
     * @return void
     */
    public static function bootValidatedTrait()
    {
        static::addGlobalScope(new ValidatedScope);
    }

    /**
     * Get a new query builder that also non-validated entries.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutValidatedTrait()
    {
        return (new static)->withoutGlobalScope(ValidatedScope::class);
    }

    /**
     * Return "validated_at" field name.
     *
     * @return string
     */
    public function getValidatedAtFieldSource(): string
    {
        return 'validityDate';
    }
}
