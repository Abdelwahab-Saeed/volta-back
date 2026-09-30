{{-- Shared by create and edit. $post is null on create. --}}
@php($post = $post ?? null)
<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'title', 'model' => $post])

    @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $post, 'textarea' => true, 'rows' => 12])

    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة المقال',
        'current' => $post?->image,
        'accept' => 'image/*',
        'hint' => 'PNG, JPG, GIF حتى 2MB',
        'previewClass' => 'w-40 h-28 object-cover',
    ])
</div>
