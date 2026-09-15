<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\App;

class SettingController extends Controller
{
    /**
     * Settings stored per language as {key}_ar / {key}_en.
     */
    private const TRANSLATABLE = ['location'];

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        // Expose each translatable setting in the request language (falling
        // back to the other one) next to its _ar / _en values.
        $locales = config('app.supported_locales');

        foreach (self::TRANSLATABLE as $key) {
            $settings[$key] = collect(array_unique([App::getLocale(), ...$locales]))
                ->map(fn (string $locale) => $settings["{$key}_{$locale}"] ?? null)
                ->first(fn (?string $value) => filled($value));
        }

        return response()->json($settings);
    }
}
