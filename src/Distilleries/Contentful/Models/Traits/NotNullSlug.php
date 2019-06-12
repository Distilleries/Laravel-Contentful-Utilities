<?php

namespace Distilleries\Contentful\Models\Traits;

use Distilleries\Contentful\Models\Scopes\NotNullSlugScope;

trait NotNullSlug
{
    /**
     * Boot the not-null-slug scope for a model.
     *
     * @return void
     */
    public static function bootNotNullSlug()
    {
        static::addGlobalScope(new NotNullSlugScope);
    }

    /**
     * Get a new query builder that also includes null-slug.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function allowNullSlug()
    {
        return static::withoutGlobalScope(NotNullSlugScope::class);
    }
}
