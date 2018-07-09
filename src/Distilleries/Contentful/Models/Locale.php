<?php

namespace Distilleries\Contentful\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property integer $id
 * @property string $label
 * @property string $code
 * @property string $fallback_code
 * @property boolean $is_editable
 * @property boolean $is_publishable
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Locale extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'locales';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'label',
        'code',
        'fallback_code',
        'is_default',
        'is_editable',
        'is_publishable',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_editable' => 'boolean',
        'is_publishable' => 'boolean',
    ];

    /**
     * Return default locale code.
     *
     * @return string
     */
    public static function default(): string
    {
        $default = Cache::get('locale_default');

        if ($default === null) {
            $default = static::query()->select('code')->where('is_default', '=', true)->first();
            $default = !empty($default) ? $default->code : config('contentful.default_locale');

            // Cache is cleaned in Console\Commands\SyncLocales (run at least daily)
            Cache::forever('locale_default', $default);
        }

        return $default;
    }

    /**
     * Return fallback code for given locale code.
     *
     * @param  string $code
     * @return string
     */
    public static function fallback(string $code): string
    {
        $fallback = Cache::get('locale_fallback_' . $code);

        if ($fallback === null) {
            $locale = static::query()->select('fallback_code')->where('code', '=', $code)->first();
            $fallback = (!empty($locale) and !empty($locale->fallback_code)) ? $locale->fallback_code : '';

            Cache::put('locale_fallback_' . $code, $fallback, 5);
        }

        return $fallback;
    }

    public static function getLocale(string $locale) :string
    {
        if (Str::contains($locale, '_')) {
            $tab = explode('_', $locale);
            return $tab[1];
        }

        return $locale;
    }

    public static function getCountry(string $locale) :string
    {
        if (Str::contains($locale, '_')) {
            $tab = explode('_', $locale);
            return $tab[0];
        }

        return config('contentful.default_country');
    }

    public function getLocaleAttribute(): string
    {
        return self::getLocale($this->code);
    }

    public function getCountryAttribute(): string
    {
        return self::getCountry($this->code);
    }
}
