<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    /**
     * Products per category name: [name_en, name_ar, price].
     */
    private const PRODUCTS = [
        'Power Banks' => [
            ['Volta PowerCore 10000mAh', 'باور بانك فولتا باور كور 10000 مللي أمبير', 650],
            ['Volta PowerCore 20000mAh Fast Charge', 'باور بانك فولتا باور كور 20000 مللي أمبير شحن سريع', 1100],
            ['Volta MagSafe Wireless Power Bank 5000mAh', 'باور بانك لاسلكي فولتا ماج سيف 5000 مللي أمبير', 950],
            ['Volta Slim Power Bank 5000mAh', 'باور بانك فولتا سليم 5000 مللي أمبير', 420],
        ],
        'Chargers' => [
            ['Volta 20W USB-C Fast Charger', 'شاحن فولتا سريع 20 وات USB-C', 350],
            ['Volta 65W GaN Charger 3 Ports', 'شاحن فولتا GaN بقدرة 65 وات 3 منافذ', 900],
            ['Volta 15W Wireless Charging Pad', 'قاعدة شحن لاسلكي فولتا 15 وات', 480],
            ['Volta 36W Dual USB Car Charger', 'شاحن سيارة فولتا 36 وات بمنفذين', 300],
        ],
        'Cables' => [
            ['Volta USB-C to USB-C Cable 1m 60W', 'كابل فولتا USB-C إلى USB-C طول 1 متر 60 وات', 150],
            ['Volta USB-C to Lightning Cable 1m', 'كابل فولتا USB-C إلى Lightning طول 1 متر', 220],
            ['Volta Braided 3-in-1 Cable 1.2m', 'كابل فولتا مجدول 3 في 1 طول 1.2 متر', 250],
            ['Volta Micro USB Cable 2m', 'كابل فولتا Micro USB طول 2 متر', 90],
        ],
        'Headphones & Earbuds' => [
            ['Volta AirBeats Pro ANC Earbuds', 'سماعات فولتا إير بيتس برو بعزل الضوضاء', 1450],
            ['Volta AirBeats Lite Earbuds', 'سماعات فولتا إير بيتس لايت', 650],
            ['Volta Studio Over-Ear Headphones', 'سماعة رأس فولتا ستوديو', 1800],
            ['Volta Sport Neckband Earphones', 'سماعات فولتا سبورت حول الرقبة', 400],
        ],
        'Smart Watches' => [
            ['Volta Fit Smart Watch 1.8"', 'ساعة فولتا فيت الذكية 1.8 بوصة', 1300],
            ['Volta Active Fitness Band', 'سوار فولتا أكتيف الرياضي', 600],
            ['Volta Classic AMOLED Smart Watch', 'ساعة فولتا كلاسيك الذكية بشاشة AMOLED', 2200],
            ['Volta Kids GPS Smart Watch', 'ساعة فولتا الذكية للأطفال مع GPS', 1100],
        ],
        'Speakers' => [
            ['Volta Boom Mini Bluetooth Speaker', 'سماعة بلوتوث فولتا بوم ميني', 550],
            ['Volta Boom Max Waterproof Speaker 30W', 'سماعة فولتا بوم ماكس ضد الماء 30 وات', 1700],
            ['Volta Party Speaker with RGB Lights', 'سماعة فولتا بارتي بإضاءة RGB', 2600],
            ['Volta Smart Home Speaker', 'سماعة فولتا المنزلية الذكية', 1250],
        ],
    ];

    /**
     * Feature pool per category name: [name_en, name_ar].
     */
    private const FEATURES = [
        'Power Banks' => [
            ['Fast charging with Power Delivery', 'شحن سريع بتقنية Power Delivery'],
            ['USB-C input and output', 'منفذ USB-C للإدخال والإخراج'],
            ['LED battery level indicator', 'مؤشر LED لمستوى البطارية'],
            ['Charges two devices at once', 'يشحن جهازين في نفس الوقت'],
            ['Overcharge and short-circuit protection', 'حماية من الشحن الزائد والماس الكهربائي'],
            ['Lightweight and pocket-sized', 'خفيف الوزن وبحجم الجيب'],
        ],
        'Chargers' => [
            ['Smart chip adjusts output to each device', 'شريحة ذكية تضبط الخرج حسب كل جهاز'],
            ['Supports PD and Quick Charge 3.0', 'يدعم PD و Quick Charge 3.0'],
            ['Over-heat protection', 'حماية من ارتفاع الحرارة'],
            ['Compact travel-friendly design', 'تصميم صغير مناسب للسفر'],
            ['Compatible with iPhone, Samsung and laptops', 'متوافق مع آيفون وسامسونج واللابتوب'],
            ['18-month warranty', 'ضمان 18 شهرًا'],
        ],
        'Cables' => [
            ['Nylon braided and tangle-free', 'مجدول بالنايلون ومقاوم للتشابك'],
            ['Tested for 20,000+ bends', 'مختبر لأكثر من 20000 ثنية'],
            ['Fast data transfer', 'نقل بيانات سريع'],
            ['Reinforced aluminum connectors', 'رؤوس ألومنيوم مقواة'],
            ['Supports fast charging', 'يدعم الشحن السريع'],
        ],
        'Headphones & Earbuds' => [
            ['Stable Bluetooth 5.3 connection', 'اتصال ثابت عبر بلوتوث 5.3'],
            ['Up to 30 hours total playtime', 'تشغيل حتى 30 ساعة إجمالًا'],
            ['Touch controls', 'تحكم باللمس'],
            ['IPX5 sweat and water resistant', 'مقاومة للعرق والماء بمعيار IPX5'],
            ['Built-in microphone for calls', 'ميكروفون مدمج للمكالمات'],
            ['Deep bass drivers', 'صوت جهير عميق'],
        ],
        'Smart Watches' => [
            ['Heart rate and SpO2 monitoring', 'قياس نبض القلب ونسبة الأكسجين'],
            ['Up to 7 days battery life', 'عمر بطارية حتى 7 أيام'],
            ['100+ sport modes', 'أكثر من 100 وضع رياضي'],
            ['Call and message notifications', 'إشعارات المكالمات والرسائل'],
            ['IP68 water resistant', 'مقاومة للماء بمعيار IP68'],
            ['Sleep tracking', 'تتبع النوم'],
        ],
        'Speakers' => [
            ['360° surround sound', 'صوت محيطي 360 درجة'],
            ['IPX7 waterproof', 'مقاومة للماء بمعيار IPX7'],
            ['Up to 12 hours playtime', 'تشغيل حتى 12 ساعة'],
            ['TWS pairing for stereo sound', 'اقتران TWS لصوت ستيريو'],
            ['Built-in microphone for hands-free calls', 'ميكروفون مدمج للمكالمات بدون استخدام اليدين'],
            ['USB and microSD playback', 'تشغيل من USB وبطاقة microSD'],
        ],
    ];

    public function run(): void
    {
        foreach (Category::whereIn('name_en', array_keys(self::PRODUCTS))->get() as $category) {
            foreach (self::PRODUCTS[$category->name_en] as [$nameEn, $nameAr, $price]) {
                $this->createProduct($category, $nameEn, $nameAr, $price);
            }
        }
    }

    private function createProduct(Category $category, string $nameEn, string $nameAr, int $pounds): void
    {
        $price = $pounds * 100; // piasters
        $discount = fake()->boolean(40) ? fake()->randomElement([10, 15, 20, 25]) : null;

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'description_en' => "{$nameEn}. {$category->description_en} Backed by the official Volta warranty.",
            'description_ar' => "{$nameAr}. {$category->description_ar} مع ضمان فولتا الرسمي.",
            'price' => $price,
            'discount' => $discount,
            'discount_price' => $discount ? (int) round($price * (100 - $discount) / 100, -2) : null, // whole pounds
            'cost_price' => (int) round($price * fake()->randomFloat(2, 0.55, 0.7)),
            'shipping_cost' => fake()->randomElement([0, 0, 2500, 4000, 5000]),
            'stock' => fake()->boolean(10) ? 0 : fake()->numberBetween(20, 150),
            'image' => PlaceholderImage::make('uploads/products', $nameEn),
            'status' => ! fake()->boolean(8),
        ]);

        foreach (Arr::random(self::FEATURES[$category->name_en], 4) as [$featureEn, $featureAr]) {
            $product->features()->create(['name_en' => $featureEn, 'name_ar' => $featureAr]);
        }

        foreach ([2, 3] as $view) {
            $product->extraImages()->create([
                'image' => PlaceholderImage::make('uploads/products', "{$nameEn} - {$view}"),
            ]);
        }

        // Bundle price is the total for exactly that quantity (see PriceCalculator).
        if ($pounds <= 700) {
            foreach ([2 => 0.9, 3 => 0.85] as $quantity => $factor) {
                $product->bundleOffers()->create([
                    'quantity' => $quantity,
                    'bundle_price' => (int) round($product->final_price * $quantity * $factor, -2), // whole pounds
                    'is_active' => true,
                ]);
            }
        }
    }
}
