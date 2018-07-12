<?php

namespace Distilleries\Contentful\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class NotNullSlugScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder
            ->whereNotNull($model->getTable() . '.slug')
            ->where($model->getTable() . '.slug', '!=', '');

        $this->extend($builder);
    }

    /**
     * Extend Builder with following macros.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('allowNullSlug', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
