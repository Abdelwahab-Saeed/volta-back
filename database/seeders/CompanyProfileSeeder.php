<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Partner;
use App\Models\TeamMember;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

/**
 * Sample partners, clients, certificates and team members so the home page sections have
 * something to show in development. Real content is entered from the admin dashboard.
 */
class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 6) as $i) {
            Partner::create([
                'name_ar' => "شريك تجريبي {$i}",
                'name_en' => "Sample Partner {$i}",
                'type' => Partner::TYPE_PARTNER,
                'logo' => PlaceholderImage::make('uploads/partners', "Partner {$i}", 400, 200),
                'sort_order' => $i,
            ]);
        }

        foreach (range(1, 8) as $i) {
            Partner::create([
                'name_ar' => "عميل تجريبي {$i}",
                'name_en' => "Sample Client {$i}",
                'type' => Partner::TYPE_CLIENT,
                'logo' => PlaceholderImage::make('uploads/partners', "Client {$i}", 400, 200),
                'sort_order' => $i,
            ]);
        }

        $certificates = [
            ['ISO 9001', 'شهادة الأيزو 9001', 'ISO 9001 Certificate', 'نظام إدارة الجودة', 'Quality management system'],
            ['EOS', 'اعتماد المواصفات المصرية', 'Egyptian Standards Approval', 'مطابقة المنتجات للمواصفات القياسية', 'Products comply with national standards'],
            ['Dealer', 'وكيل معتمد', 'Authorised Dealer', 'وكيل معتمد للعلامات التي نبيعها', 'Authorised dealer of the brands we sell'],
        ];

        foreach ($certificates as $i => [$label, $titleAr, $titleEn, $descriptionAr, $descriptionEn]) {
            Certificate::create([
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'issuer_ar' => 'جهة تجريبية',
                'issuer_en' => 'Sample issuer',
                'description_ar' => $descriptionAr,
                'description_en' => $descriptionEn,
                'image' => PlaceholderImage::make('uploads/certificates', $label, 600, 800),
                'issued_year' => 2020 + $i,
                'sort_order' => $i,
            ]);
        }

        $team = [
            ['أحمد محمد', 'Ahmed Mohamed', 'المدير العام', 'General Manager'],
            ['سارة علي', 'Sara Ali', 'مديرة المبيعات', 'Sales Manager'],
            ['محمود حسن', 'Mahmoud Hassan', 'مهندس تركيبات', 'Installation Engineer'],
            ['منى إبراهيم', 'Mona Ibrahim', 'خدمة العملاء', 'Customer Support'],
        ];

        foreach ($team as $i => [$nameAr, $nameEn, $roleAr, $roleEn]) {
            TeamMember::create([
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'role_ar' => $roleAr,
                'role_en' => $roleEn,
                // One member without a photo shows the initial-letter avatar.
                'photo' => $i === 3 ? null : PlaceholderImage::make('uploads/team', $nameEn, 400, 400),
                'sort_order' => $i,
            ]);
        }
    }
}
