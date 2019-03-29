<?php

namespace Distilleries\Contentful\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property integer $id
 * @property string $label
 * @property string $code
 * @property string $locale
 * @property string $country
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
        'locale',
        'country',
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
            $default = static::select('locale')
                ->where('is_default', '=', true)
                ->first();

            $default = !empty($default) ? $default->locale : config('contentful.default_locale');

            // Cache is cleaned in Console\Commands\SyncLocales (run at least daily)
            Cache::forever('locale_default', $default);
        }

        return $default;
    }

    /**
     * Return application OR Contentful space default locale.
     *
     * @return string
     */
    public static function getAppOrDefaultLocale(): string
    {
        return app()->getLocale() ?? static::default();
    }

    /**
     * Return application OR Contentful space default country.
     *
     * @param  string  $key
     * @return string
     */
    public static function getAppOrDefaultCountry($key = 'app.country'): string
    {
        return config($key, static::defaultCountry());
    }

    /**
     * Return default country code.
     *
     * @return string
     */
    public static function defaultCountry(): string
    {
        $default = Cache::get('country_default');

        if ($default === null) {
            $default = static::select('country')
                ->where('is_default', '=', true)
                ->first();

            $default = ! empty($default) ? $default->country : config('contentful.default_country');

            // Cache is cleaned in Console\Commands\SyncLocales (run at least daily)
            Cache::forever('country_default', $default);
        }

        return $default;
    }

    /**
     * Return fallback code for given locale code.
     *
     * @param  string  $code
     * @return string
     */
    public static function fallback(string $code): string
    {
        $fallback = Cache::get('locale_fallback_' . $code);

        if ($fallback === null) {
            $locale = static::select('fallback_code')
                ->where('code', '=', $code)
                ->first();

            $fallback = (! empty($locale) && ! empty($locale->fallback_code)) ? $locale->fallback_code : '';

            Cache::put('locale_fallback_' . $code, $fallback, 5);
        }

        return $fallback;
    }

    /**
     * Return if locale can be saved.
     *
     * @param  string  $country
     * @param  string  $locale
     * @return bool
     */
    public static function canBeSave(string $country, string $locale): bool
    {
        return ! in_array($country . '_' . $locale, static::_getLocalesDisabled());
    }

    /**
     * Return disabled locales (for flatten).
     *
     * @return array
     */
    protected static function _getLocalesDisabled(): array
    {

        $locales =config('contentful.use_preview')?config('contentful.locales_not_flatten_preview', ''):config('contentful.locales_not_flatten', '');

        return explode(',', $locales);
    }

    /**
     * Check if current instance is enabled (for flatten).
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return ! in_array($this->country . '_' . $this->locale, static::_getLocalesDisabled());
    }

    /**
     * Return locale from ISO code.
     *
     * @param  string  $code
     * @return string
     */
    public static function getLocale(string $code): string
    {
        if (Str::contains($code, '_')) {
            $tab = explode('_', $code);

            return Str::lower($tab[0]);
        } else if (Str::contains($code, '-')) {
            $tab = explode('-', $code);

            return Str::lower($tab[0]);
        }

        return Str::lower($code);
    }

    /**
     * Return country from ISO code.
     *
     * @param  string  $code
     * @return string
     */
    public static function getCountry(string $code): string
    {
        if (Str::contains($code, '_')) {
            $tab = explode('_', $code);

            return Str::lower($tab[1]);
        } else if (Str::contains($code, '-')) {
            $tab = explode('-', $code);

            return Str::lower($tab[1]);
        }

        return config('contentful.default_country');
    }

    /**
     * Return request accepted languages.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return string|array
     */
    public static function getAcceptedLanguages(Request $request = null)
    {
        $request = ! empty($request) ? $request : request();

        $languages = $request->server('HTTP_ACCEPT_LANGUAGE');
        if (! empty($languages)) {
            preg_match_all('/(\W|^)([a-z]{2})([^a-z]|$)/six', $languages, $locales, PREG_PATTERN_ORDER);

            if (! empty($locales) && ! empty($locales[2])) {
                return $locales[2];
            }
        }

        return [];
    }

    /**
     * Return user default language.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return string
     */
    public static function getDefaultLanguageUser(Request $request = null): string
    {
        $country = static::defaultCountry();
        $locales = static::getAcceptedLanguages($request);
        $locale = ! empty($locales) ? $locales[0] : config('app.fallback_locale');

        $localeModel = (new static)
            ->where('country', $country)
            ->where('locale', $locale)
            ->take(1)
            ->get()
            ->first();

        return empty($localeModel) ? static::default() : $localeModel->locale;
    }
}
