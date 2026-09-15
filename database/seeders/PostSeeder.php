<?php

namespace Database\Seeders;

use App\Models\Post;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    private const POSTS = [
        [
            'title_en' => 'How to Choose the Right Power Bank',
            'title_ar' => 'كيف تختار الباور بانك المناسب',
            'description_en' => 'Capacity is measured in mAh, but the usable capacity is usually about 65% of the rated number. A 10,000mAh power bank charges most phones about twice, while 20,000mAh is better for trips and tablets. Also check the output wattage and whether it supports fast charging for your phone.',
            'description_ar' => 'تُقاس سعة الباور بانك بوحدة مللي أمبير، لكن السعة الفعلية القابلة للاستخدام تكون عادةً حوالي 65% من الرقم المكتوب. باور بانك بسعة 10000 مللي أمبير يشحن معظم الهواتف مرتين تقريبًا، بينما سعة 20000 أنسب للسفر والأجهزة اللوحية. تأكد أيضًا من قدرة الخرج ودعم الشحن السريع لهاتفك.',
        ],
        [
            'title_en' => 'GaN Chargers Explained',
            'title_ar' => 'ما هي شواحن GaN؟',
            'description_en' => 'GaN (gallium nitride) chargers run cooler and are much smaller than traditional chargers of the same power. A single 65W GaN charger can power a laptop, a tablet and a phone at the same time, which makes it a great travel companion.',
            'description_ar' => 'شواحن GaN (نيتريد الغاليوم) أقل حرارة وأصغر حجمًا بكثير من الشواحن التقليدية بنفس القدرة. شاحن GaN واحد بقدرة 65 وات يمكنه شحن اللابتوب والتابلت والهاتف في نفس الوقت، مما يجعله رفيقًا مثاليًا في السفر.',
        ],
        [
            'title_en' => '5 Tips to Extend Your Phone Battery Life',
            'title_ar' => '5 نصائح لإطالة عمر بطارية هاتفك',
            'description_en' => 'Avoid leaving your phone on the charger at 100% overnight, keep it away from heat, and use certified cables and chargers. Lowering screen brightness and turning off background app refresh also make a noticeable difference.',
            'description_ar' => 'تجنب ترك هاتفك على الشاحن بعد وصوله إلى 100% طوال الليل، وأبعده عن الحرارة، واستخدم كابلات وشواحن معتمدة. كما أن تقليل سطوع الشاشة وإيقاف تحديث التطبيقات في الخلفية يُحدثان فرقًا ملحوظًا.',
        ],
        [
            'title_en' => 'Wireless Earbuds: What Does ANC Mean?',
            'title_ar' => 'السماعات اللاسلكية: ماذا يعني العزل النشط للضوضاء؟',
            'description_en' => 'Active noise cancellation uses built-in microphones to detect outside noise and plays an opposite sound wave to cancel it. It works best on constant sounds such as traffic, air conditioners and airplane engines.',
            'description_ar' => 'تستخدم تقنية العزل النشط للضوضاء ميكروفونات مدمجة لرصد الأصوات الخارجية وإصدار موجة صوتية معاكسة لإلغائها. وتعمل بأفضل شكل مع الأصوات المستمرة مثل زحام الطريق والمكيفات ومحركات الطائرات.',
        ],
        [
            'title_en' => 'USB-C vs Lightning: Which Cable Do You Need?',
            'title_ar' => 'USB-C أم Lightning: أي كابل تحتاج؟',
            'description_en' => 'Newer iPhones, most Android phones and laptops use USB-C, while older iPhones use Lightning. For fast charging, choose a cable rated for at least 60W and a length that suits where you charge.',
            'description_ar' => 'تستخدم أجهزة آيفون الحديثة ومعظم هواتف أندرويد وأجهزة اللابتوب منفذ USB-C، بينما تستخدم أجهزة آيفون الأقدم منفذ Lightning. للشحن السريع اختر كابلًا يدعم 60 وات على الأقل وبطول يناسب مكان الشحن.',
        ],
        [
            'title_en' => 'Getting Started with Your Smart Watch',
            'title_ar' => 'ابدأ استخدام ساعتك الذكية',
            'description_en' => 'Pair the watch with its companion app over Bluetooth, allow notifications, and set your daily step and sleep goals. Charge it fully before the first use to calibrate the battery.',
            'description_ar' => 'قم بإقران الساعة مع التطبيق المخصص لها عبر البلوتوث، وفعّل الإشعارات، وحدد أهدافك اليومية للخطوات والنوم. اشحنها بالكامل قبل أول استخدام لمعايرة البطارية.',
        ],
    ];

    public function run(): void
    {
        foreach (self::POSTS as $index => $data) {
            $post = new Post([
                ...$data,
                'image' => PlaceholderImage::make('posts', $data['title_en'], 1200, 675),
            ]);
            $post->created_at = now()->subDays((count(self::POSTS) - $index) * 9);
            $post->save();
        }
    }
}
