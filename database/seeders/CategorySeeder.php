<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        [
            'name_en' => 'Power Banks',
            'name_ar' => 'باور بانك',
            'description_en' => 'Portable batteries that keep your phone, tablet and earbuds charged on the go.',
            'description_ar' => 'بطاريات محمولة تحافظ على شحن هاتفك وجهازك اللوحي وسماعاتك أثناء التنقل.',
        ],
        [
            'name_en' => 'Chargers',
            'name_ar' => 'الشواحن',
            'description_en' => 'Fast wall, car and wireless chargers for all your devices.',
            'description_ar' => 'شواحن سريعة للحائط والسيارة وشواحن لاسلكية لجميع أجهزتك.',
        ],
        [
            'name_en' => 'Cables',
            'name_ar' => 'الكابلات',
            'description_en' => 'Durable charging and data cables for every connector and length.',
            'description_ar' => 'كابلات شحن ونقل بيانات متينة بجميع أنواع المنافذ والأطوال.',
        ],
        [
            'name_en' => 'Headphones & Earbuds',
            'name_ar' => 'السماعات',
            'description_en' => 'Wireless earbuds and headphones with clear sound and long battery life.',
            'description_ar' => 'سماعات لاسلكية بصوت نقي وعمر بطارية طويل.',
        ],
        [
            'name_en' => 'Smart Watches',
            'name_ar' => 'الساعات الذكية',
            'description_en' => 'Smart watches and fitness bands to track your health and notifications.',
            'description_ar' => 'ساعات ذكية وأساور رياضية لمتابعة صحتك وإشعاراتك.',
        ],
        [
            'name_en' => 'Speakers',
            'name_ar' => 'مكبرات الصوت',
            'description_en' => 'Portable Bluetooth speakers with powerful sound for home and outdoors.',
            'description_ar' => 'مكبرات صوت بلوتوث محمولة بصوت قوي للمنزل والرحلات.',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $category) {
            Category::create([
                ...$category,
                'image' => PlaceholderImage::make('uploads/categories', $category['name_en']),
                'status' => true,
                'category_order' => $index + 1,
            ]);
        }
    }
}
