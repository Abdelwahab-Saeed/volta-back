@php
    use App\Models\DistributorRequest;
    use App\Support\Governorates;
    $model = $distributorRequest;
@endphp

<div class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="label">الاسم <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $model->name) }}" maxlength="100" required class="input">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="phone" class="label">رقم الهاتف <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" id="phone" value="{{ old('phone', $model->phone) }}" dir="ltr" inputmode="numeric" placeholder="01xxxxxxxxx" required class="input text-left">
            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="governorate" class="label">المحافظة <span class="text-red-500">*</span></label>
            <select name="governorate" id="governorate" required class="input">
                <option value="">اختر المحافظة</option>
                @foreach(Governorates::ALL as $value => $label)
                    <option value="{{ $value }}" @selected(old('governorate', $model->governorate) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('governorate')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="center" class="label">المركز <span class="text-red-500">*</span></label>
            <input type="text" name="center" id="center" value="{{ old('center', $model->center) }}" maxlength="100" required class="input">
            @error('center')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="area" class="label">القرية أو الحي <span class="text-red-500">*</span></label>
            <input type="text" name="area" id="area" value="{{ old('area', $model->area) }}" maxlength="150" required class="input">
            @error('area')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        @include('admin.distributor-requests._yes-no', ['name' => 'has_commercial_register', 'label' => 'هل يوجد سجل تجاري؟', 'value' => $model->has_commercial_register])
        @include('admin.distributor-requests._yes-no', ['name' => 'has_shop', 'label' => 'هل يوجد محل تجاري؟', 'value' => $model->has_shop])
    </div>

    <div>
        <label for="notes" class="label">ملاحظات المتقدم (اختياري)</label>
        <textarea name="notes" id="notes" rows="3" maxlength="1000" class="input">{{ old('notes', $model->notes) }}</textarea>
        @error('notes')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="border-t border-slate-100 pt-6 grid gap-5 sm:grid-cols-3">
        <div>
            <label for="status" class="label">الحالة</label>
            <select name="status" id="status" class="input">
                @foreach(DistributorRequest::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $model->status ?? 'new') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="admin_notes" class="label">ملاحظات داخلية (لا تظهر للمتقدم)</label>
            <textarea name="admin_notes" id="admin_notes" rows="3" maxlength="2000" class="input">{{ old('admin_notes', $model->admin_notes) }}</textarea>
            @error('admin_notes')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
