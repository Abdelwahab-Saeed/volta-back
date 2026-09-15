<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Splits the `location` setting into `location_ar` and `location_en`.
 *
 * The existing address is treated as the English one; the Arabic address is
 * filled in from the dashboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        $location = DB::table('settings')->where('key', 'location')->first();

        if ($location) {
            // `key` is unique, so only rename when no location_en row exists yet;
            // otherwise keep a filled location_en and drop the old row.
            $english = DB::table('settings')->where('key', 'location_en')->first();

            if (! $english) {
                DB::table('settings')->where('id', $location->id)->update(['key' => 'location_en']);
            } else {
                if (blank($english->value)) {
                    DB::table('settings')->where('id', $english->id)->update(['value' => $location->value]);
                }

                DB::table('settings')->where('id', $location->id)->delete();
            }
        }

        foreach (['location_ar', 'location_en'] as $key) {
            if (DB::table('settings')->where('key', $key)->doesntExist()) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $english = DB::table('settings')->where('key', 'location_en')->value('value');
        $arabic = DB::table('settings')->where('key', 'location_ar')->value('value');

        DB::table('settings')->whereIn('key', ['location_ar', 'location_en'])->delete();

        DB::table('settings')->insert([
            'key' => 'location',
            'value' => $english ?? $arabic,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
