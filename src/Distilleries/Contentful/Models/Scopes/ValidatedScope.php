<?php

namespace Distilleries\Contentful\Models\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class ValidatedScope implements Scope
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
            ->where(function ($query) use ($model) {
                $query
                    ->whereNull($model->getTable() . '.validated_at')
                    ->orWhere($model->getTable() . '.validated_at', '>=', Carbon::now()->format('Y-m-d H:i:s'));
            });

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
        $builder->macro('withoutValidated', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
