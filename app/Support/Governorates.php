<?php

namespace App\Support;

/**
 * Egypt's governorates, English name => Arabic name.
 * The English name is the value the storefront sends (volta-app src/lib/governorates.js holds the same list).
 */
class Governorates
{
    public const ALL = [
        'Cairo' => 'القاهرة',
        'Alexandria' => 'الإسكندرية',
        'Port Said' => 'بورسعيد',
        'Suez' => 'السويس',
        'Damietta' => 'دمياط',
        'Dakahlia' => 'الدقهلية',
        'Sharqia' => 'الشرقية',
        'Qalyubia' => 'القليوبية',
        'Kafr El Sheikh' => 'كفر الشيخ',
        'Gharbia' => 'الغربية',
        'Monufia' => 'المنوفية',
        'Beheira' => 'البحيرة',
        'Ismailia' => 'الإسماعيلية',
        'Giza' => 'الجيزة',
        'Beni Suef' => 'بني سويف',
        'Fayoum' => 'الفيوم',
        'Minya' => 'المنيا',
        'Assiut' => 'أسيوط',
        'Sohag' => 'سوهاج',
        'Qena' => 'قنا',
        'Luxor' => 'الأقصر',
        'Aswan' => 'أسوان',
        'Red Sea' => 'البحر الأحمر',
        'New Valley' => 'الوادي الجديد',
        'Matrouh' => 'مطروح',
        'North Sinai' => 'شمال سيناء',
        'South Sinai' => 'جنوب سيناء',
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::ALL);
    }

    /** Arabic name for the admin; unknown values are shown as they are. */
    public static function arabic(?string $governorate): string
    {
        return self::ALL[$governorate] ?? (string) $governorate;
    }
}
