<?php

namespace App\Models;

use App\Support\Governorates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

/** A trader asking to become a Volta distributor ("كن موزع فولتا"). */
class DistributorRequest extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'new' => 'جديد',
        'contacted' => 'تم التواصل',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
    ];

    /** Badge class per status (admin design system). */
    public const STATUS_BADGES = [
        'new' => 'badge-info',
        'contacted' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
    ];

    public const SOURCES = [
        'website' => 'الموقع',
        'admin' => 'لوحة التحكم',
    ];

    protected $fillable = [
        'name',
        'phone',
        'governorate',
        'center',
        'area',
        'has_commercial_register',
        'has_shop',
        'notes',
        'status',
        'admin_notes',
        'source',
    ];

    protected $casts = [
        'has_commercial_register' => 'boolean',
        'has_shop' => 'boolean',
    ];

    /** Validation rules for the applicant's fields, shared by the storefront API and the admin form. */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
            'governorate' => ['required', 'string', Rule::in(Governorates::keys())],
            'center' => 'required|string|max:100',
            'area' => 'required|string|max:150',
            'has_commercial_register' => 'required|boolean',
            'has_shop' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /** Arabic labels for validation messages in the admin. */
    public static function attributeNames(): array
    {
        return [
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'governorate' => 'المحافظة',
            'center' => 'المركز',
            'area' => 'القرية أو الحي',
            'has_commercial_register' => 'السجل التجاري',
            'has_shop' => 'المحل التجاري',
            'notes' => 'الملاحظات',
            'status' => 'الحالة',
            'admin_notes' => 'الملاحظات الداخلية',
        ];
    }

    /** "٠١٢ 3456-7890" => "01234567890": Arabic-Indic digits to Western, separators dropped. */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $phone = strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        $phone = preg_replace('/[\s\-().]/', '', $phone);

        // +20 / 0020 country code => local 0 prefix
        return preg_replace('/^(\+20|0020)/', '0', $phone);
    }

    public function getGovernorateArAttribute(): string
    {
        return Governorates::arabic($this->governorate);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Number in international form for wa.me links. */
    public function getWhatsappUrlAttribute(): string
    {
        return 'https://wa.me/2' . $this->phone;
    }
}
