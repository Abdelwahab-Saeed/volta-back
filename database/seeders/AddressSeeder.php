<?php

namespace Database\Seeders;

use App\Models\Address;
use Database\Seeders\Support\MockData;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        foreach (MockData::customers() as $user) {
            foreach (array_slice(['المنزل', 'العمل'], 0, random_int(1, 2)) as $index => $label) {
                [$state, $city] = MockData::location();

                Address::create([
                    'user_id' => $user->id,
                    'name' => $label,
                    'recipient_name' => $user->name,
                    'address_line_1' => MockData::street(),
                    'address_line_2' => fake()->boolean(40) ? 'بجوار '.fake()->randomElement(['المسجد', 'الصيدلية', 'المول', 'البنك']) : null,
                    'city' => $city,
                    'state' => $state,
                    'zip_code' => fake()->numerify('1####'),
                    'country' => 'مصر',
                    'phone_number' => $user->phone_number ?: MockData::phone(),
                    'backup_phone_number' => fake()->boolean(30) ? MockData::phone() : null,
                    'is_default' => $index === 0,
                ]);
            }
        }
    }
}
