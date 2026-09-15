<?php

namespace Database\Seeders;

use App\Models\Banner;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    private const BANNERS = [
        [
            'title_en' => 'Charge Faster, Go Further',
            'title_ar' => 'اشحن أسرع وانطلق أبعد',
            'description_en' => 'Up to 25% off power banks and fast chargers.',
            'description_ar' => 'خصم يصل إلى 25% على الباور بانك والشواحن السريعة.',
            'status' => true,
        ],
        [
            'title_en' => 'New AirBeats Pro Earbuds',
            'title_ar' => 'سماعات إير بيتس برو الجديدة',
            'description_en' => 'Active noise cancellation with up to 30 hours of playtime.',
            'description_ar' => 'عزل نشط للضوضاء مع تشغيل حتى 30 ساعة.',
            'status' => true,
        ],
        [
            'title_en' => 'Bundle & Save',
            'title_ar' => 'اشترِ أكثر ووفّر أكثر',
            'description_en' => 'Get special prices when you buy 2 or 3 pieces.',
            'description_ar' => 'احصل على أسعار خاصة عند شراء قطعتين أو ثلاث.',
            'status' => true,
        ],
        [
            'title_en' => 'Smart Watches Collection',
            'title_ar' => 'تشكيلة الساعات الذكية',
            'description_en' => 'Track your health, workouts and notifications from your wrist.',
            'description_ar' => 'تابع صحتك وتمارينك وإشعاراتك من معصمك.',
            'status' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::BANNERS as $banner) {
            Banner::create([
                ...$banner,
                'image' => PlaceholderImage::make('uploads/banners', $banner['title_en'], 1600, 600),
            ]);
        }
    }
}
