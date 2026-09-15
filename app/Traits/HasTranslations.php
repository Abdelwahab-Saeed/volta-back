<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

/**
 * Bilingual attributes stored as `{field}_ar` / `{field}_en` columns.
 *
 * Models list the base field names in `protected array $translatable`.
 * Reading `$model->name` returns the value for the current locale, falling
 * back to the other language when that one is empty, and serializing the
 * model adds the localized `name` key next to the raw `name_ar` / `name_en`.
 * The locale comes from the SetLocale middleware (Accept-Language on the API).
 */
trait HasTranslations
{
    public static function translationLocales(): array
    {
        return config('app.supported_locales', ['ar', 'en']);
    }

    public function translate(string $field, ?string $locale = null): ?string
    {
        $locales = static::translationLocales();
        $locale ??= App::getLocale();

        if (! in_array($locale, $locales, true)) {
            $locale = $locales[0];
        }

        // Requested locale first, then the others in configured order.
        foreach (array_unique([$locale, ...$locales]) as $candidate) {
            $value = parent::getAttribute("{$field}_{$candidate}");

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->translatable, true)) {
            return $this->translate($key);
        }

        return parent::getAttribute($key);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->translatable as $field) {
            $attributes[$field] = $this->translate($field);
        }

        return $attributes;
    }

    /**
     * Match a term against every language column of the given fields.
     */
    public function scopeWhereTranslationLike(Builder $query, array $fields, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($fields, $term) {
            foreach ($fields as $field) {
                foreach (static::translationLocales() as $locale) {
                    $query->orWhere("{$field}_{$locale}", 'LIKE', "%{$term}%");
                }
            }
        });
    }
}
