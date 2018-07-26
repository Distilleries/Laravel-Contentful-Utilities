<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 12/07/2018
 * Time: 15:30
 */

namespace Distilleries\Contentful\Models\Traits;


use Distilleries\Contentful\Models\Locale;
use Illuminate\Database\Eloquent\Builder;

trait Localable
{
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Scope a query to a given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $locale
     * @param  string  $country
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, string $locale = '',string $country='') : Builder
    {
        $locale = ! empty($locale) ? $locale : Locale::getAppOrDefaultLocale();
        $country = ! empty($country) ? $country : Locale::getAppOrDefaultCountry();

        return $query
            ->whereRaw('LOWER('.$this->getTable().'.country) LIKE LOWER("' . $country . '")')
            ->whereRaw('LOWER('.$this->getTable().'.locale) LIKE LOWER("' . $locale . '")');
    }

}