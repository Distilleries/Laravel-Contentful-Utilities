<?php

namespace Distilleries\Contentful\Models\Traits;

use Distilleries\Contentful\Models\Scopes\ValidatedScope;

trait ValidatedTrait
{
    /**
     * Boot the not-null-slug scope for a model.
     *
     * @return void
     */
    public static function bootValidatedTrait()
    {
        static::addGlobalScope(new ValidatedScope());
    }

    /**
     * Get a new query builder that also includes null-slug.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutValidatedTrait()
    {
        return (new static)->withoutGlobalScope(ValidatedScope::class);
    }

    public function getValidatedAtFieldSource()
    {
        return 'validityDate';
    }
}
