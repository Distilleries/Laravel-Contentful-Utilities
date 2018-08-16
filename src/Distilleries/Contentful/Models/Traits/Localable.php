<?php

namespace Distilleries\Contentful\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Distilleries\Contentful\Models\Locale;

trait Localable
{
    abstract public function getTable();

    /**
     * Scope a query to a given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null $locale
     * @param  string|null $country
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, ?string $locale = '', ?string $country = ''): Builder
    {
        $locale = !empty($locale) ? $locale : Locale::getAppOrDefaultLocale();
        $country = !empty($country) ? $country : Locale::getAppOrDefaultCountry();

        return $query
            ->whereRaw('LOWER(' . $this->getTable() . '.country) LIKE LOWER("' . $country . '")')
            ->whereRaw('LOWER(' . $this->getTable() . '.locale) LIKE LOWER("' . $locale . '")');
    }
}