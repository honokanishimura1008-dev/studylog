@extends('layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <h1 class="h3 mb-4">履歴</h1>

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    {{-- 筋トレ / 食事 切替タブ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a href="{{ route('mussle-log.index') }}" class="nav-link {{ $tab === 'train' ? 'active' : '' }}">筋トレ</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mussle-log.index', ['tab' => 'meal']) }}" class="nav-link {{ $tab === 'meal' ? 'active' : '' }}">食事</a>
        </li>
    </ul>

    {{-- フィルター --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @if ($tab === 'train')
            <a href="{{ route('mussle-log.index', ['filter' => 'all']) }}" class="filter-pill {{ $filter === 'all' ? 'active' : '' }}">すべて</a>
            <a href="{{ route('mussle-log.index', ['filter' => 'stuck']) }}" class="filter-pill {{ $filter === 'stuck' ? 'active' : '' }}">課題あり</a>
            <a href="{{ route('mussle-log.index', ['filter' => 'week']) }}" class="filter-pill {{ $filter === 'week' ? 'active' : '' }}">今週</a>
        @else
            <a href="{{ route('mussle-log.index', ['tab' => 'meal', 'filter' => 'all']) }}" class="filter-pill {{ $filter === 'all' ? 'active' : '' }}">すべて</a>
            <a href="{{ route('mussle-log.index', ['tab' => 'meal', 'filter' => 'week']) }}" class="filter-pill {{ $filter === 'week' ? 'active' : '' }}">今週</a>
        @endif
    </div>

    @if ($tab === 'train')
        {{-- ===== 筋トレ履歴 ===== --}}
        @if ($studyLogs->isEmpty())
            <div class="panel p-4 text-secondary">
                トレーニング記録がまだありません。<a href="{{ route('dashboard') }}" class="link-danger">ダッシュボードのカレンダーから記録する</a>
            </div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach ($studyLogs as $log)
                    <div class="panel p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                @if ($log->material->cover_path)
                                    <img src="{{ asset($log->material->cover_path) }}" alt="" class="log-thumb">
                                @endif
                                <div>
                                    <span class="badge text-bg-secondary">{{ $log->material->typeLabel() }}</span>
                                    <h2 class="h5 mt-2 mb-0">{{ $log->material->title }}</h2>
                                </div>
                            </div>
                            <div class="text-secondary small text-end ms-3">
                                {{ $log->studied_on->format('n/j') }}<br>{{ $log->setSummary() }}
                            </div>
                        </div>

                        @if ($log->memo)
                            <p class="small mb-2">
                                <span class="fw-semibold">メモ:</span> {{ $log->memo }}
                            </p>
                        @endif

                        {{-- 旧フォーマットの記録（良かった点/課題）も表示できるよう残す --}}
                        @if ($log->learned)
                            <p class="small mb-2">
                                <span class="fw-semibold">良かった点:</span> {{ $log->learned }}
                            </p>
                        @endif

                        @if ($log->stuck)
                            <p class="small text-warning mb-3">
                                <span class="fw-semibold">課題・改善点:</span> {{ $log->stuck }}
                            </p>
                        @endif

                        <div class="d-flex gap-3">
                            <a
                                href="{{ route('dashboard', ['date' => $log->studied_on->format('Y-m-d'), 'edit' => $log->id, 'modal' => 1]) }}"
                                class="btn btn-link btn-sm p-0"
                            >修正</a>
                            <form method="POST" action="{{ route('mussle-log.destroy', $log) }}" onsubmit="return confirm('このトレーニング記録を削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">削除</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $studyLogs->links() }}
            </div>
        @endif
    @else
        {{-- ===== 食事履歴（日付ごとにまとめて表示） ===== --}}
        @if ($mealLogs->isEmpty())
            <div class="panel p-4 text-secondary">
                食事記録がまだありません。<a href="{{ route('dashboard') }}" class="link-danger">ダッシュボードのカレンダーから記録する</a>
            </div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach ($mealsByDate as $dateKey => $meals)
                    @php
                        $date = \Carbon\Carbon::parse($dateKey);
                        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
                        $counted = $meals->filter(fn ($meal) => $meal->hasNutrition());
                        $uncounted = $meals->count() - $counted->count();
                    @endphp
                    <div class="panel p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h2 class="h6 mb-0">{{ $date->format('n月j日') }}（{{ $weekday }}）</h2>
                            <div class="text-secondary small">
                                合計 <span class="fw-bold">{{ number_format($counted->sum('kcal')) }}</span> kcal ・
                                P {{ round($counted->sum('protein'), 1) }} /
                                F {{ round($counted->sum('fat'), 1) }} /
                                C {{ round($counted->sum('carbs'), 1) }}
                            </div>
                        </div>

                        @if ($uncounted > 0)
                            <div class="summary-note mb-2">⚠ {{ $uncounted }}件は栄養値未入力のため合計に含まれていません</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-dark-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>区分</th>
                                        <th>食品</th>
                                        <th class="text-end">量(g)</th>
                                        <th class="text-end">kcal</th>
                                        <th class="text-end">P</th>
                                        <th class="text-end">F</th>
                                        <th class="text-end">C</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($meals as $meal)
                                        <tr>
                                            <td><span class="chip {{ $meal->meal_type }}">{{ $meal->mealTypeLabel() }}</span></td>
                                            <td>
                                                {{ $meal->displayName() }}
                                                @if ($meal->isQuick())
                                                    <span class="chip free ms-1">クイック</span>
                                                @elseif ($meal->isFree())
                                                    <span class="chip free ms-1">自由記述</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $meal->amount_g ?? '—' }}</td>
                                            <td class="text-end">{{ $meal->kcal ?? '未入力' }}</td>
                                            <td class="text-end">{{ $meal->protein ?? '—' }}</td>
                                            <td class="text-end">{{ $meal->fat ?? '—' }}</td>
                                            <td class="text-end">{{ $meal->carbs ?? '—' }}</td>
                                            <td class="text-end text-nowrap">
                                                <a
                                                    href="{{ route('dashboard', ['date' => $dateKey, 'editMeal' => $meal->id, 'modal' => 1, 'tab' => 'meal']) }}"
                                                    class="btn btn-sm btn-ghost"
                                                >修正</a>
                                                <form method="POST" action="{{ route('meal-log.destroy', $meal) }}" class="d-inline" onsubmit="return confirm('この食事記録を削除しますか？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-danger">削除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $mealLogs->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
