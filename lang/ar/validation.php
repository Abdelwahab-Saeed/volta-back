<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول الحقل :attribute.',
    'accepted_if' => 'يجب قبول الحقل :attribute عندما يكون :other هو :value.',
    'active_url' => 'الحقل :attribute ليس رابطاً صحيحاً.',
    'after' => 'يجب أن يكون الحقل :attribute تاريخاً بعد :date.',
    'after_or_equal' => 'يجب أن يكون الحقل :attribute تاريخاً بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي الحقل :attribute على حروف فقط.',
    'alpha_dash' => 'يجب أن يحتوي الحقل :attribute على حروف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي الحقل :attribute على حروف وأرقام فقط.',
    'array' => 'يجب أن يكون الحقل :attribute مصفوفة.',
    'ascii' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام ورموز أحادية البايت فقط.',
    'before' => 'يجب أن يكون الحقل :attribute تاريخاً قبل :date.',
    'before_or_equal' => 'يجب أن يكون الحقل :attribute تاريخاً قبل أو يساوي :date.',
    'between' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على ما بين :min و :max عنصراً.',
        'file' => 'يجب أن يكون حجم الملف :attribute ما بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute ما بين :min و :max.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute ما بين :min و :max حرفاً.',
    ],
    'boolean' => 'يجب أن تكون قيمة الحقل :attribute إما صحيحاً أو خاطئاً.',
    'can' => 'الحقل :attribute يحتوي على قيمة غير مصرح بها.',
    'confirmed' => 'تأكيد الحقل :attribute غير متطابق.',
    'contains' => 'الحقل :attribute يفتقد إلى قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'الحقل :attribute ليس تاريخاً صحيحاً.',
    'date_equals' => 'يجب أن يكون الحقل :attribute تاريخاً مساوياً لـ :date.',
    'date_format' => 'الحقل :attribute لا يتوافق مع الشكل :format.',
    'decimal' => 'يجب أن يحتوي الحقل :attribute على :decimal أرقام عشرية.',
    'declined' => 'يجب رفض الحقل :attribute.',
    'declined_if' => 'يجب رفض الحقل :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مختلفين.',
    'digits' => 'يجب أن يتكون الحقل :attribute من :digits أرقام.',
    'digits_between' => 'يجب أن يتكون الحقل :attribute من ما بين :min و :max أرقام.',
    'dimensions' => 'الحقل :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'الحقل :attribute يحتوي على قيمة مكررة.',
    'doesnt_end_with' => 'الحقل :attribute يجب ألا ينتهي بأحد القيم التالية: :values.',
    'doesnt_start_with' => 'الحقل :attribute يجب ألا يبدأ بأحد القيم التالية: :values.',
    'email' => 'يجب أن يكون الحقل :attribute بريداً إلكترونياً صحيحاً.',
    'ends_with' => 'يجب أن ينتهي الحقل :attribute بأحد القيم التالية: :values.',
    'enum' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'exists' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'extensions' => 'الحقل :attribute يجب أن يكون من أحد الامتدادات التالية: :values.',
    'file' => 'يجب أن يكون الحقل :attribute ملفاً.',
    'filled' => 'الحقل :attribute مطلوب.',
    'gt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أكثر من :value عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أكبر من :value.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute أكبر من :value حرفاً.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :value عناصر أو أكثر.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أكبر من أو يساوي :value.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute أكبر من أو يساوي :value حرفاً.',
    ],
    'hex_color' => 'يجب أن يكون الحقل :attribute لوناً سداسي عشرياً صحيحاً.',
    'image' => 'يجب أن يكون الحقل :attribute صورة.',
    'in' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'in_array' => 'الحقل :attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون الحقل :attribute عدداً صحيحاً.',
    'ip' => 'يجب أن يكون الحقل :attribute عنوان IP صحيحاً.',
    'ipv4' => 'يجب أن يكون الحقل :attribute عنوان IPv4 صحيحاً.',
    'ipv6' => 'يجب أن يكون الحقل :attribute عنوان IPv6 صحيحاً.',
    'json' => 'يجب أن يكون الحقل :attribute نصاً بصيغة JSON صحيحاً.',
    'list' => 'يجب أن يكون الحقل :attribute قائمة.',
    'lowercase' => 'يجب أن يكون الحقل :attribute حروفاً صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أقل من :value عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من :value.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute أقل من :value حرفاً.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي الحقل :attribute على أكثر من :value عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute أقل من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من أو يساوي :value.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute أقل من أو يساوي :value حرفاً.',
    ],
    'mac_address' => 'يجب أن يكون الحقل :attribute عنوان MAC صحيحاً.',
    'max' => [
        'array' => 'يجب ألا يحتوي الحقل :attribute على أكثر من :max عناصر.',
        'file' => 'يجب ألا يتجاوز حجم الملف :attribute :max كيلوبايت.',
        'numeric' => 'يجب ألا تكون قيمة الحقل :attribute أكبر من :max.',
        'string' => 'يجب ألا يتجاوز طول نص الحقل :attribute :max حرفاً.',
    ],
    'max_digits' => 'الحقل :attribute يجب ألا يحتوي على أكثر من :max أرقام.',
    'mimes' => 'يجب أن يكون الحقل :attribute ملفاً من النوع: :values.',
    'mimetypes' => 'يجب أن يكون الحقل :attribute ملفاً من النوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على الأقل على :min عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute على الأقل :min.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute على الأقل :min حرفاً.',
    ],
    'min_digits' => 'الحقل :attribute يجب أن يحتوي على الأقل على :min أرقام.',
    'missing' => 'يجب أن يكون الحقل :attribute ناقصاً.',
    'missing_if' => 'يجب أن يكون الحقل :attribute ناقصاً عندما يكون :other هو :value.',
    'missing_unless' => 'يجب أن يكون الحقل :attribute ناقصاً إلا إذا كان :other هو :value.',
    'missing_with' => 'يجب أن يكون الحقل :attribute ناقصاً عندما يكون :values موجوداً.',
    'missing_with_all' => 'يجب أن يكون الحقل :attribute ناقصاً عندما تكون :values موجودة.',
    'multiple_of' => 'يجب أن يكون الحقل :attribute من مضاعفات :value.',
    'not_in' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'not_regex' => 'شكل الحقل :attribute غير صحيح.',
    'numeric' => 'يجب أن يكون الحقل :attribute رقماً.',
    'password' => [
        'letters' => 'يجب أن يحتوي الحقل :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي الحقل :attribute على حرف كبير واحد على الأقل وحرف صغير واحد على الأقل.',
        'numbers' => 'يجب أن يحتوي الحقل :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي الحقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'الحقل :attribute المختار ظهر في تسريب بيانات. الرجاء اختيار :attribute مختلف.',
    ],
    'present' => 'يجب تقديم الحقل :attribute.',
    'present_if' => 'يجب تقديم الحقل :attribute عندما يكون :other هو :value.',
    'present_unless' => 'يجب تقديم الحقل :attribute إلا إذا كان :other هو :value.',
    'present_with' => 'يجب تقديم الحقل :attribute عندما يكون :values موجوداً.',
    'present_with_all' => 'يجب تقديم الحقل :attribute عندما تكون :values موجودة.',
    'prohibited' => 'إدخال الحقل :attribute ممنوع.',
    'prohibited_if' => 'إدخال الحقل :attribute ممنوع عندما يكون :other هو :value.',
    'prohibited_unless' => 'إدخال الحقل :attribute ممنوع إلا إذا كان :other في :values.',
    'prohibits' => 'الحقل :attribute يمنع :other من التواجد.',
    'regex' => 'شكل الحقل :attribute غير صحيح.',
    'required' => 'الحقل :attribute مطلوب.',
    'required_array_keys' => 'الحقل :attribute يجب أن يحتوي على مدخلات لـ :values.',
    'required_if' => 'الحقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => 'الحقل :attribute مطلوب عندما يتم قبول :other.',
    'required_if_declined' => 'الحقل :attribute مطلوب عندما يتم رفض :other.',
    'required_unless' => 'الحقل :attribute مطلوب إلا إذا كان :other في :values.',
    'required_with' => 'الحقل :attribute مطلوب عندما يكون :values موجوداً.',
    'required_with_all' => 'الحقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => 'الحقل :attribute مطلوب عندما لا يكون :values موجوداً.',
    'required_without_all' => 'الحقل :attribute مطلوب عندما لا يكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق الحقل :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :size عنصر/عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute :size.',
        'string' => 'يجب أن يكون طول نص الحقل :attribute :size حرفاً/حروفاً.',
    ],
    'starts_with' => 'يجب أن يبدأ الحقل :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون الحقل :attribute نصاً.',
    'timezone' => 'يجب أن يكون الحقل :attribute نطاقاً زمنياً صحيحاً.',
    'unique' => 'القيمة المحددة لـ :attribute مستخدمة بالفعل.',
    'uploaded' => 'فشل في تحميل الحقل :attribute.',
    'uppercase' => 'يجب أن يكون الحقل :attribute حروفاً كبيرة.',
    'url' => 'يجب أن يكون الحقل :attribute رابطاً صحيحاً.',
    'ulid' => 'يجب أن يكون الحقل :attribute ULID صحيحاً.',
    'uuid' => 'يجب أن يكون الحقل :attribute UUID صحيحاً.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone_number' => 'رقم الهاتف',
        'role' => 'الصلاحية',
        'description' => 'الوصف',
        'price' => 'السعر',
        'discount' => 'الخصم',
        'stock' => 'المخزون',
        'image' => 'الصورة',
        'status' => 'الحالة',
        'code' => 'الكود',
        'type' => 'النوع',
        'value' => 'القيمة',
        'min_order_amount' => 'الحد الأدنى للطلب',
        'starts_at' => 'تاريخ البدء',
        'expires_at' => 'تاريخ الانتهاء',
        'max_uses' => 'أقصى عدد للاستخدام',
        'category_id' => 'القسم',
        'title' => 'العنوان',
    ],

];
