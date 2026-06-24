@extends('layouts.app')

@section('content')
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h1 class="page-title fs-3 mb-0">食事ギャラリー</h1>
        <input class="search d-none d-md-block" placeholder="食品名で検索…">
    </div>
    <p class="note mb-4">記録時に参照する食事マスタです。値はすべて100gあたり。この画面は閲覧専用です。</p>

    <div class="toolbar mb-4">
        <div class="filters" id="filters">
            <button class="filter active" data-cat="all">すべて</button>
            @foreach (\App\Models\Food::CATEGORIES as $key => $label)
                <button class="filter" data-cat="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>
        <input class="search d-md-none ms-auto" placeholder="検索…">
    </div>

    @if ($foods->isEmpty())
        <div class="panel p-4 text-secondary">
            食事マスタが登録されていません。<code>php artisan db:seed --class=FoodSeeder</code> を実行してください。
        </div>
    @else
        <div class="row g-3" id="grid">
            @foreach ($foods as $food)
                <div class="col-6 col-md-4 col-lg-3 food-item" data-cat="{{ $food->category }}" data-name="{{ $food->name }}">
                    <div class="card-food">
                        <div class="thumb thumb--{{ $food->category }}">{{ $food->emoji }}</div>
                        <div class="card-body">
                            <div class="fname">{{ $food->name }}</div>
                            <div class="fcat">{{ $food->categoryLabel() }}</div>
                            <div class="kcal">{{ $food->kcal }}<small> kcal</small></div>
                            <div class="pfc">
                                <div><div class="k">P</div><div class="v">{{ rtrim(rtrim(number_format($food->protein, 1), '0'), '.') }}</div></div>
                                <div><div class="k">F</div><div class="v">{{ rtrim(rtrim(number_format($food->fat, 1), '0'), '.') }}</div></div>
                                <div><div class="k">C</div><div class="v">{{ rtrim(rtrim(number_format($food->carbs, 1), '0'), '.') }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>
@endsection

@push('scripts')
<script>
    const filters = document.getElementById('filters');
    const items = [...document.querySelectorAll('.food-item')];
    let cat = 'all', q = '';

    function apply() {
        items.forEach((it) => {
            const okCat = (cat === 'all' || it.dataset.cat === cat);
            const okQ = (q === '' || it.dataset.name.includes(q));
            it.style.display = (okCat && okQ) ? '' : 'none';
        });
    }

    filters.addEventListener('click', (e) => {
        const b = e.target.closest('.filter');
        if (!b) return;
        filters.querySelectorAll('.filter').forEach((x) => x.classList.remove('active'));
        b.classList.add('active');
        cat = b.dataset.cat;
        apply();
    });

    document.querySelectorAll('.search').forEach((s) => s.addEventListener('input', (e) => {
        q = e.target.value.trim();
        apply();
    }));
</script>
@endpush
