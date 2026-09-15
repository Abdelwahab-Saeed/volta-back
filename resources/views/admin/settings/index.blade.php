@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-6">روابط وسائل التواصل الاجتماعي</h3>
    
    <form action="{{ route('admin.settings.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Facebook -->
            <div>
                <label for="facebook" class="block text-sm font-bold text-gray-700 mb-2">فيسبوك (Facebook)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/></svg>
                    </span>
                    <input type="url" name="facebook" id="facebook" value="{{ old('facebook', $settings['facebook'] ?? '') }}" 
                        class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors text-left dir-ltr"
                        placeholder="https://facebook.com/..." dir="ltr">
                </div>
                @error('facebook')
                    <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Instagram -->
            <div>
                <label for="instagram" class="block text-sm font-bold text-gray-700 mb-2">إنستغرام (Instagram)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="14" height="14" x="5" y="5" rx="4" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M16.5 7.5v.01"/></svg>
                    </span>
                    <input type="url" name="instagram" id="instagram" value="{{ old('instagram', $settings['instagram'] ?? '') }}" 
                        class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors text-left dir-ltr"
                        placeholder="https://instagram.com/..." dir="ltr">
                </div>
                @error('instagram')
                    <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Twitter -->
            <div>
                <label for="twitter" class="block text-sm font-bold text-gray-700 mb-2">تويتر (Twitter / X)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </span>
                    <input type="url" name="twitter" id="twitter" value="{{ old('twitter', $settings['twitter'] ?? '') }}" 
                        class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors text-left dir-ltr"
                        placeholder="https://twitter.com/..." dir="ltr">
                </div>
                @error('twitter')
                    <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Youtube -->
            <div>
                <label for="youtube" class="block text-sm font-bold text-gray-700 mb-2">يوتيوب (YouTube)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4-2v4l4-2z"/><rect width="20" height="14" x="2" y="5" rx="4" stroke-width="2"/></svg>
                    </span>
                    <input type="url" name="youtube" id="youtube" value="{{ old('youtube', $settings['youtube'] ?? '') }}" 
                        class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors text-left dir-ltr"
                        placeholder="https://youtube.com/..." dir="ltr">
                </div>
                @error('youtube')
                    <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- TikTok -->
            <div>
                <label for="tiktok" class="block text-sm font-bold text-gray-700 mb-2">تيك توك (TikTok)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 font-bold font-sans text-xs">
                        TT
                    </span>
                    <input type="url" name="tiktok" id="tiktok" value="{{ old('tiktok', $settings['tiktok'] ?? '') }}" 
                        class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors text-left dir-ltr"
                        placeholder="https://tiktok.com/..." dir="ltr">
                </div>
                @error('tiktok')
                    <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location (Arabic / English) -->
            @foreach (['ar' => ['rtl', 'مثال: القاهرة، مصر'], 'en' => ['ltr', 'e.g. Cairo, Egypt']] as $locale => [$dir, $placeholder])
                @php($input = "location_{$locale}")
                <div class="md:col-span-2">
                    <label for="{{ $input }}" class="block text-sm font-bold text-gray-700 mb-2">{{ __("admin.{$input}") }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 {{ $dir === 'rtl' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </span>
                        <input type="text" name="{{ $input }}" id="{{ $input }}" value="{{ old($input, $settings[$input] ?? '') }}" dir="{{ $dir }}"
                            class="w-full {{ $dir === 'rtl' ? 'pr-10 pl-4' : 'pl-10 pr-4 text-left' }} py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                            placeholder="{{ $placeholder }}">
                    </div>
                    @error($input)
                        <p class="mt-1 text-sm text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-sm shadow-blue-200 flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                حفظ الإعدادات
            </button>
        </div>
    </form>
</div>
@endsection
