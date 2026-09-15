<?php

namespace Database\Seeders\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MockData
{
    /**
     * Governorates mapped to some of their cities.
     */
    public const LOCATIONS = [
        'القاهرة' => ['مدينة نصر', 'المعادي', 'مصر الجديدة', 'التجمع الخامس', 'شبرا'],
        'الجيزة' => ['الدقي', 'المهندسين', '6 أكتوبر', 'الشيخ زايد', 'الهرم'],
        'الإسكندرية' => ['سموحة', 'سيدي جابر', 'المنتزه', 'العجمي'],
        'الدقهلية' => ['المنصورة', 'طلخا', 'ميت غمر'],
        'الشرقية' => ['الزقازيق', 'العاشر من رمضان'],
        'القليوبية' => ['بنها', 'شبرا الخيمة', 'العبور'],
        'الغربية' => ['طنطا', 'المحلة الكبرى'],
    ];

    private const STREETS = [
        'شارع التحرير',
        'شارع جامعة الدول العربية',
        'شارع عباس العقاد',
        'شارع مصطفى النحاس',
        'شارع فيصل',
        'شارع بورسعيد',
        'شارع الجمهورية',
        'شارع 9',
    ];

    /**
     * @return array{0: string, 1: string} [state, city]
     */
    public static function location(): array
    {
        $state = array_rand(self::LOCATIONS);

        return [$state, Arr::random(self::LOCATIONS[$state])];
    }

    public static function street(): string
    {
        return Arr::random(self::STREETS).'، عمارة '.random_int(1, 120).'، الدور '.random_int(1, 12);
    }

    public static function phone(): string
    {
        return '01'.Arr::random(['0', '1', '2', '5']).fake()->numerify('########');
    }

    /**
     * Existing customer accounts; the mock seeders never create users.
     */
    public static function customers(): Collection
    {
        return User::where('role', 'user')->get();
    }
}
