@extends('layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">メニュー</h1>
        <a href="{{ route('materials.create') }}" class="btn btn-accent btn-sm">+ 追加</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <p class="text-secondary small mb-4">登録メニュー {{ $materials->count() }}件</p>

    @if ($materials->isEmpty())
        <div class="panel p-4 text-secondary">
            メニューがまだありません。<a href="{{ route('materials.create') }}" class="link-danger">追加する</a>
        </div>
    @else
        <div class="row g-4">
            @foreach ($materials as $material)
                @php
                    $totalMinutes = (int) ($material->minutes_sum ?? 0);
                    $progress = $material->estimated_minutes
                        ? min(100, (int) round($totalMinutes / $material->estimated_minutes * 100))
                        : 0;
                    $status = $totalMinutes === 0
                        ? '未実施'
                        : ($material->estimated_minutes && $totalMinutes >= $material->estimated_minutes ? '達成' : '継続中');
                @endphp
                <div class="col-sm-6 col-lg-4">
                    <div class="panel overflow-hidden h-100">
                        <div class="material-cover">
                            @if ($material->cover_path)
                                <img src="{{ asset($material->cover_path) }}" alt="{{ $material->title }}">
                            @else
                                {{ $material->typeLabel() }}
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge text-bg-secondary">{{ $material->typeLabel() }}</span>
                                <span class="text-secondary small">{{ $status }}</span>
                            </div>
                            <h2 class="h5 mb-3">{{ $material->title }}</h2>
                            <div class="progress mb-2" style="height: 0.5rem;">
                                <div class="progress-bar" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-secondary small mb-3">
                                累計 {{ format_training_minutes($totalMinutes) }} · {{ $progress }}%
                            </p>
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('materials.edit', $material) }}" class="btn btn-link btn-sm p-0">修正</a>
                                <form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('このメニューを削除しますか？関連するトレーニング記録も削除されます。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0">削除</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
