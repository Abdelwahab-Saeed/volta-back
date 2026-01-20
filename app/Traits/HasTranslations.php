<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get the translation for a given attribute.
     *
     * @param string $attribute
     * @param string|null $locale
     * @return string
     */
    public function getTranslation(string $attribute, ?string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        $translations = json_decode($this->attributes[$attribute] ?? '{}', true);

        // Return requested locale, or fallback to 'en', or fallback to 'ar', or empty string
        return $translations[$locale] ?? $translations['en'] ?? $translations['ar'] ?? '';
    }

    /**
     * Accessor to automatically translate attributes if accessed directly?
     * Ideally, we keep the raw access for Admin and use a resource for API.
     * But for convenience (e.g. $product->name), we can override getAttribute.
     * 
     * However, the feedback said "Locale logic in Middleware + Resources only".
     * So we might NOT want to override magic getters here to avoid confusion in Admin.
     * 
     * We will just use this trait to easily set translations.
     */
    public function setTranslation(string $attribute, string $locale, string $value): void
    {
        $translations = json_decode($this->attributes[$attribute] ?? '{}', true);
        $translations[$locale] = $value;
        $this->attributes[$attribute] = json_encode($translations, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Helper to set all translations at once (e.g. from request input)
     */
    public function setTranslations(string $attribute, array $translations): void
    {
        $this->attributes[$attribute] = json_encode($translations, JSON_UNESCAPED_UNICODE);
    }
}
