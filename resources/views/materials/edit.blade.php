@extends('layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <h1 class="h3 mb-4">メニューを修正</h1>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="panel p-4 p-md-5">
                <form method="POST" action="{{ route('materials.update', $material) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="type" class="form-label">種別</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="">選択してください</option>
                            @foreach (\App\Models\Material::TYPES as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $material->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">メニュー名</label>
                        <input id="title" name="title" type="text" class="form-control" value="{{ old('title', $material->title) }}" required>
                        @error('title')
                            <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
                        @enderror
                    </div>

                    <div class="mb-3 d-none" id="cover-picker">
                        <label class="form-label">画像</label>
                        <div class="form-text mb-2">種目のイメージ画像を選択してください（任意）。</div>

                        @foreach (\App\Models\Material::COVER_IMAGES as $typeKey => $names)
                            <div class="cover-group d-none" data-type="{{ $typeKey }}">
                                <div class="row g-2">
                                    @foreach ($names as $name)
                                        @php $path = 'images/materials/'.$name.'.png'; @endphp
                                        <div class="col-4 col-sm-3">
                                            <label class="cover-option">
                                                <input
                                                    type="radio"
                                                    name="cover_path"
                                                    value="{{ $path }}"
                                                    @checked(old('cover_path', $material->cover_path) === $path)
                                                    @if (old('type', $material->type) !== $typeKey) disabled @endif
                                                >
                                                <img src="{{ asset($path) }}" alt="{{ $name }}">
                                                <span class="cover-name">{{ $name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @error('cover_path')
                            <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="estimated_minutes" class="form-label">目標トレーニング時間（分）</label>
                        <input id="estimated_minutes" name="estimated_minutes" type="number" min="1" class="form-control" value="{{ old('estimated_minutes', $material->estimated_minutes) }}">
                        <div class="form-text">進捗%の分母になります。未入力の場合は0%表示です。</div>
                        @error('estimated_minutes')
                            <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-accent">更新する</button>
                        <a href="{{ route('materials.index') }}" class="text-secondary small">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const typeSelect = document.getElementById('type');
    const coverPicker = document.getElementById('cover-picker');
    const coverGroups = document.querySelectorAll('.cover-group');

    function updateCoverPicker() {
        const type = typeSelect.value;

        coverPicker.classList.toggle('d-none', type === '');

        coverGroups.forEach((group) => {
            const isActive = group.dataset.type === type;
            group.classList.toggle('d-none', !isActive);

            group.querySelectorAll('input[type="radio"]').forEach((radio) => {
                radio.disabled = !isActive;
                if (!isActive) {
                    radio.checked = false;
                }
            });
        });
    }

    typeSelect.addEventListener('change', updateCoverPicker);
    updateCoverPicker();
</script>
@endpush
