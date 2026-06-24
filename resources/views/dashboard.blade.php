@extends('layouts.app')

@section('content')
<main class="container py-4">

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <h1 class="page-title text-center mb-4 fs-3">{{ $displayYear }}年 トレーニング記録</h1>

    <div class="hero mb-3">
        <img src="{{ asset('images/dashboard-hero.png') }}" alt="ジム">
    </div>

    <div class="hint p-3 mb-4">
        <span class="me-1">●</span> 日付をタップすると、その日の記録を追加・編集できます。
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <a href="{{ $prevUrl }}" class="btn btn-sm btn-ghost" aria-label="前の月">‹</a>
                    <div class="cal-head fs-5">{{ $displayMonthLabel }}</div>
                    <a href="{{ $nextUrl }}" class="btn btn-sm btn-ghost" aria-label="次の月">›</a>
                </div>
                <div class="cal-sub text-center mb-3">記録した日にドットが付きます</div>

                <table class="cal">
                    <thead>
                        <tr>
                            @foreach (['日', '月', '火', '水', '木', '金', '土'] as $weekday)
                                <th>{{ $weekday }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weeks as $week)
                            <tr>
                                @foreach ($week as $day)
                                    <td>
                                        @if (! $day['inMonth'])
                                            <div class="day out">{{ $day['day'] }}</div>
                                        @else
                                            <a
                                                href="{{ $day['selectUrl'] }}&modal=1"
                                                class="day {{ $day['isToday'] ? 'today' : '' }}"
                                            >
                                                {{ $day['day'] }}
                                                @if ($day['hasTraining'] || $day['hasMeal'])
                                                    <div class="dots">
                                                        @if ($day['hasTraining'])
                                                            <span class="dot t"></span>
                                                        @endif
                                                        @if ($day['hasMeal'])
                                                            <span class="dot m"></span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="legend mt-3 justify-content-center">
                    <span><span class="dot t" style="width:8px;height:8px;border-radius:50%;display:inline-block"></span> 筋トレ</span>
                    <span><span class="dot m" style="width:8px;height:8px;border-radius:50%;display:inline-block"></span> 食事</span>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel p-4 h-100 menu-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="label">今日のメニュー</div>
                        <div class="fs-5 fw-bold">{{ $selectedDateLabel }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-accent" data-bs-toggle="modal" data-bs-target="#dayModal">＋ 記録</button>
                </div>

                <div class="mb-2"><span class="chip t">筋トレ</span></div>
                @forelse ($todayLogs as $log)
                    <div class="menu-row">
                        @if ($log->material->cover_path)
                            <img src="{{ asset($log->material->cover_path) }}" alt="" class="menu-thumb">
                        @endif
                        <span class="t1">{{ $log->material->title }}</span>
                        <span class="t2">{{ $log->setSummary() }}</span>
                    </div>
                @empty
                    <div class="menu-row">
                        <span class="t1 text-secondary">記録なし</span>
                    </div>
                @endforelse

                <div class="mt-3 mb-2"><span class="chip m">食事</span></div>
                @forelse ($todayMeals as $meal)
                    <div class="menu-row">
                        <span class="t1">{{ $meal->displayName() }}</span>
                        <span class="t2">
                            {{ $meal->mealTypeLabel() }} ・
                            @if ($meal->hasNutrition())
                                {{ $meal->kcal }}kcal
                            @else
                                栄養値未入力
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="menu-row">
                        <span class="t1 text-secondary">記録なし</span>
                    </div>
                @endforelse

                <div class="row g-2 mt-3">
                    <div class="col-3"><div class="stat"><div class="k">kcal</div><div class="v">{{ $todayMeals->isEmpty() ? '—' : number_format($mealSummary['kcal']) }}</div></div></div>
                    <div class="col-3"><div class="stat"><div class="k">P</div><div class="v">{{ $todayMeals->isEmpty() ? '—' : $mealSummary['protein'] }}</div></div></div>
                    <div class="col-3"><div class="stat"><div class="k">F</div><div class="v">{{ $todayMeals->isEmpty() ? '—' : $mealSummary['fat'] }}</div></div></div>
                    <div class="col-3"><div class="stat"><div class="k">C</div><div class="v">{{ $todayMeals->isEmpty() ? '—' : $mealSummary['carbs'] }}</div></div></div>
                </div>
                @if ($mealSummary['uncounted'] > 0)
                    <div class="summary-note mt-2">⚠ {{ $mealSummary['uncounted'] }}件は栄養値未入力のため合計に含まれていません</div>
                @endif
            </div>
        </div>
    </div>
</main>

{{-- 日次記録モーダル（S-05）: 一覧・追加・編集・削除をモーダル内で完結させる --}}
<div class="modal fade" id="dayModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $selectedDateLabel }}の記録</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if (session('status'))
                    <div class="alert alert-success py-2 small mb-3">{{ session('status') }}</div>
                @endif

                @php
                    // 食事フォーム送信時のバリデーションエラー・編集中・?tab=meal のときは食事タブを開く
                    $mealFields = ['mode', 'meal_type', 'eaten_on', 'food_id', 'amount_g', 'food_name_free', 'kcal', 'protein', 'fat', 'carbs'];
                    $mealTabActive = request('tab') === 'meal' || $editMeal || $errors->hasAny($mealFields);

                    // 編集対象から初期入力モードを決める（マスタ参照 / 自由記述 / クイック）
                    $initialMealMode = 'master';
                    if ($editMeal) {
                        $initialMealMode = $editMeal->food_id ? 'master' : ($editMeal->isQuick() ? 'quick' : 'free');
                    }
                    $initialMealMode = old('mode', $initialMealMode);
                @endphp

                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button class="nav-link {{ $mealTabActive ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#tabTrain" type="button">筋トレ</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $mealTabActive ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMeal" type="button">食事</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade {{ $mealTabActive ? '' : 'show active' }}" id="tabTrain">

                        {{-- この日の登録済みレコード一覧 --}}
                        <div class="table-responsive">
                            <table class="table table-dark-soft align-middle">
                                <thead>
                                    <tr>
                                        <th>種目</th>
                                        <th class="text-end">kg</th>
                                        <th class="text-end">回数</th>
                                        <th class="text-end">セット</th>
                                        <th>メモ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($todayLogs as $log)
                                        <tr class="{{ $editLog && $editLog->id === $log->id ? 'table-active' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($log->material->cover_path)
                                                        <img src="{{ asset($log->material->cover_path) }}" alt="" class="log-thumb">
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $log->material->title }}</div>
                                                        <div class="text-secondary small">{{ $log->material->typeLabel() }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">{{ $log->weight_kg ?? '—' }}</td>
                                            <td class="text-end">{{ $log->reps ?? '—' }}</td>
                                            <td class="text-end">{{ $log->sets ?? '—' }}</td>
                                            <td class="small">{{ $log->memo ? Str::limit($log->memo, 20) : '—' }}</td>
                                            <td class="text-end text-nowrap">
                                                <a
                                                    href="{{ route('dashboard', ['date' => $selectedDateKey, 'year' => $displayYear, 'month' => $displayMonth, 'edit' => $log->id, 'modal' => 1]) }}"
                                                    class="btn btn-sm btn-ghost"
                                                >編集</a>
                                                <form method="POST" action="{{ route('mussle-log.destroy', $log) }}" class="d-inline" onsubmit="return confirm('削除しますか？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-danger">削除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-secondary small">この日の記録はまだありません。</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 追加 / 編集フォーム（編集時は既存値を注入） --}}
                        <div class="border-top pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ $editLog ? '記録を編集' : '行を追加' }}</h6>
                                @if ($editLog)
                                    <a
                                        href="{{ route('dashboard', ['date' => $selectedDateKey, 'year' => $displayYear, 'month' => $displayMonth, 'modal' => 1]) }}"
                                        class="btn btn-sm btn-ghost"
                                    >編集をやめる</a>
                                @endif
                            </div>

                            @if (! $mealTabActive && $errors->any())
                                <div class="alert alert-danger py-2 small">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($materials->isEmpty())
                                <p class="text-secondary small mb-0">
                                    先に<a href="{{ route('materials.create') }}" class="link-danger">種目</a>を登録してください。
                                </p>
                            @else
                                <form
                                    id="logForm"
                                    method="POST"
                                    action="{{ $editLog ? route('mussle-log.update', $editLog) : route('mussle-log.store') }}"
                                >
                                    @csrf
                                    @if ($editLog)
                                        @method('PUT')
                                    @endif
                                    <input type="hidden" name="mussle_date" value="{{ old('mussle_date', $selectedDateKey) }}">

                                    <div class="row g-2">
                                        <div class="col-12 col-md-5">
                                            <label for="logMaterial" class="form-label small mb-1">種目</label>
                                            <select name="material_id" id="logMaterial" class="form-select" required>
                                                <option value="">選択してください</option>
                                                @foreach ($materials as $material)
                                                    <option
                                                        value="{{ $material->id }}"
                                                        data-type="{{ $material->typeLabel() }}"
                                                        data-image="{{ $material->cover_path ? asset($material->cover_path) : '' }}"
                                                        @selected((string) old('material_id', $editLog?->material_id) === (string) $material->id)
                                                    >{{ $material->title }}</option>
                                                @endforeach
                                            </select>
                                            {{-- 種目に紐づく画像・部位の自動表示 --}}
                                            <div id="materialPreview" class="d-none align-items-center gap-2 mt-2">
                                                <img id="materialPreviewImg" src="" alt="" class="log-preview d-none">
                                                <span id="materialPreviewType" class="badge text-bg-secondary"></span>
                                            </div>
                                        </div>
                                        <div class="col-4 col-md-2">
                                            <label class="form-label small mb-1">重量(kg)</label>
                                            <input
                                                type="number" name="weight_kg" class="form-control"
                                                min="0" max="999" step="0.5" placeholder="自重は空欄"
                                                value="{{ old('weight_kg', $editLog?->weight_kg) }}"
                                            >
                                        </div>
                                        <div class="col-4 col-md-2">
                                            <label class="form-label small mb-1">回数</label>
                                            <input
                                                type="number" name="reps" class="form-control"
                                                min="1" max="999" required
                                                value="{{ old('reps', $editLog?->reps) }}"
                                            >
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <label class="form-label small mb-1">セット数</label>
                                            <input
                                                type="number" name="sets" class="form-control"
                                                min="1" max="99" required
                                                value="{{ old('sets', $editLog?->sets) }}"
                                            >
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small mb-1">メモ</label>
                                            <input
                                                type="text" name="memo" class="form-control"
                                                maxlength="1000" placeholder="フォームが安定した、次回は重量UP など"
                                                value="{{ old('memo', $editLog?->memo) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-sm btn-accent px-4">{{ $editLog ? '更新する' : '＋ 追加' }}</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade {{ $mealTabActive ? 'show active' : '' }}" id="tabMeal">

                        {{-- この日の登録済み食事一覧 --}}
                        <div class="sec-label">この日の食事記録</div>
                        <div class="table-responsive">
                            <table class="table table-dark-soft align-middle">
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
                                    @forelse ($todayMeals as $meal)
                                        <tr class="{{ $editMeal && $editMeal->id === $meal->id ? 'table-active' : '' }}">
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
                                                    href="{{ route('dashboard', ['date' => $selectedDateKey, 'year' => $displayYear, 'month' => $displayMonth, 'editMeal' => $meal->id, 'modal' => 1, 'tab' => 'meal']) }}"
                                                    class="btn btn-sm btn-ghost"
                                                >編集</a>
                                                <form method="POST" action="{{ route('meal-log.destroy', $meal) }}" class="d-inline" onsubmit="return confirm('削除しますか？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-danger">削除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-secondary small">この日の食事記録はまだありません。</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 日次集計（F-07）: 未入力分は0扱いせず注記する --}}
                        @if ($todayMeals->isNotEmpty())
                            <div class="row g-2">
                                <div class="col-3"><div class="stat text-center"><div class="k">kcal</div><div class="v">{{ number_format($mealSummary['kcal']) }}</div></div></div>
                                <div class="col-3"><div class="stat text-center"><div class="k">P</div><div class="v">{{ $mealSummary['protein'] }}</div></div></div>
                                <div class="col-3"><div class="stat text-center"><div class="k">F</div><div class="v">{{ $mealSummary['fat'] }}</div></div></div>
                                <div class="col-3"><div class="stat text-center"><div class="k">C</div><div class="v">{{ $mealSummary['carbs'] }}</div></div></div>
                            </div>
                            @if ($mealSummary['uncounted'] > 0)
                                <div class="summary-note mt-2">⚠ {{ $mealSummary['uncounted'] }}件は栄養値未入力のため合計に含まれていません</div>
                            @endif
                        @endif

                        {{-- 追加 / 編集フォーム（ハイブリッド入力: マスタ選択 / 自由記述 / クイック） --}}
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ $editMeal ? '食事記録を編集' : '記録を追加' }}</h6>
                                @if ($editMeal)
                                    <a
                                        href="{{ route('dashboard', ['date' => $selectedDateKey, 'year' => $displayYear, 'month' => $displayMonth, 'modal' => 1, 'tab' => 'meal']) }}"
                                        class="btn btn-sm btn-ghost"
                                    >編集をやめる</a>
                                @endif
                            </div>

                            @if ($mealTabActive && $errors->any())
                                <div class="alert alert-danger py-2 small">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="add-zone">
                                <form
                                    id="mealForm"
                                    method="POST"
                                    action="{{ $editMeal ? route('meal-log.update', $editMeal) : route('meal-log.store') }}"
                                >
                                    @csrf
                                    @if ($editMeal)
                                        @method('PUT')
                                    @endif
                                    <input type="hidden" name="eaten_on" value="{{ $selectedDateKey }}">
                                    <input type="hidden" name="mode" id="mealMode" value="{{ $initialMealMode }}">

                                    <div class="mode-seg mb-3" id="mealModeSeg">
                                        <button type="button" data-mode="master">マスタから選択</button>
                                        <button type="button" data-mode="free">自由記述</button>
                                        <button type="button" data-mode="quick">クイック記録</button>
                                    </div>

                                    {{-- 共通: 区分 --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small mb-1">区分</label>
                                            <select name="meal_type" class="form-select form-select-sm">
                                                @foreach (\App\Models\MealLog::MEAL_TYPES as $typeKey => $typeLabel)
                                                    <option
                                                        value="{{ $typeKey }}"
                                                        @selected(old('meal_type', $editMeal?->meal_type ?? 'dinner') === $typeKey)
                                                    >{{ $typeLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- モードA: マスタ選択（PFC自動算出・複数行まとめて登録可） --}}
                                    <div id="mealModeMaster">
                                        @if ($editMeal)
                                            {{-- 先頭行 = 編集対象。追加した行は新しい記録として登録される --}}
                                            <div id="mealItems">
                                                <div class="row g-3 align-items-end meal-item-row mb-2">
                                                    <div class="col-12 col-md-5">
                                                        <label class="form-label small mb-1">食品（マスタ）</label>
                                                        <select name="food_id" class="form-select form-select-sm food-sel">
                                                            <option value="">選択してください</option>
                                                            @foreach ($foods as $food)
                                                                <option
                                                                    value="{{ $food->id }}"
                                                                    data-kcal="{{ $food->kcal }}"
                                                                    data-p="{{ $food->protein }}"
                                                                    data-f="{{ $food->fat }}"
                                                                    data-c="{{ $food->carbs }}"
                                                                    @selected((string) old('food_id', $editMeal->food_id) === (string) $food->id)
                                                                >{{ $food->emoji }} {{ $food->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-3 col-md-2">
                                                        <label class="form-label small mb-1">量 (g)</label>
                                                        <input
                                                            type="number" name="amount_g" class="form-control form-control-sm amount-input"
                                                            min="1" max="2000"
                                                            value="{{ old('amount_g', $editMeal->amount_g ?? 100) }}"
                                                        >
                                                    </div>
                                                    <div class="col-7 col-md-4">
                                                        <label class="form-label small mb-1">算出値 <span class="auto-badge">自動</span></label>
                                                        <div class="form-control form-control-sm calc-box calc-out">— kcal / P — / F — / C —</div>
                                                    </div>
                                                    <div class="col-2 col-md-1 text-end">
                                                        {{-- 編集対象の行は削除できない（削除はテーブル側の削除ボタンで） --}}
                                                        <button type="button" class="btn btn-sm btn-ghost meal-item-remove d-none" aria-label="この行を削除">✕</button>
                                                    </div>
                                                </div>
                                                @foreach (old('items', []) as $i => $item)
                                                    <div class="row g-3 align-items-end meal-item-row mb-2">
                                                        <div class="col-12 col-md-5">
                                                            <label class="form-label small mb-1">食品（マスタ）</label>
                                                            <select name="items[{{ $i }}][food_id]" class="form-select form-select-sm food-sel">
                                                                <option value="">選択してください</option>
                                                                @foreach ($foods as $food)
                                                                    <option
                                                                        value="{{ $food->id }}"
                                                                        data-kcal="{{ $food->kcal }}"
                                                                        data-p="{{ $food->protein }}"
                                                                        data-f="{{ $food->fat }}"
                                                                        data-c="{{ $food->carbs }}"
                                                                        @selected((string) ($item['food_id'] ?? '') === (string) $food->id)
                                                                    >{{ $food->emoji }} {{ $food->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-2">
                                                            <label class="form-label small mb-1">量 (g)</label>
                                                            <input
                                                                type="number" name="items[{{ $i }}][amount_g]" class="form-control form-control-sm amount-input"
                                                                min="1" max="2000"
                                                                value="{{ $item['amount_g'] ?? 100 }}"
                                                            >
                                                        </div>
                                                        <div class="col-7 col-md-4">
                                                            <label class="form-label small mb-1">算出値 <span class="auto-badge">自動</span></label>
                                                            <div class="form-control form-control-sm calc-box calc-out">— kcal / P — / F — / C —</div>
                                                        </div>
                                                        <div class="col-2 col-md-1 text-end">
                                                            <button type="button" class="btn btn-sm btn-ghost meal-item-remove" aria-label="この行を削除">✕</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="mealItemAdd" class="btn btn-sm btn-ghost mt-1">＋ 食品を追加</button>
                                            <div class="text-secondary small mt-1">追加した行は新しい記録として登録されます。</div>
                                        @else
                                            {{-- 新規登録は「＋ 食品を追加」で行を増やして一括登録 --}}
                                            @php
                                                $oldItems = old('items', [['food_id' => '', 'amount_g' => 100]]);
                                            @endphp
                                            <div id="mealItems">
                                                @foreach ($oldItems as $i => $item)
                                                    <div class="row g-3 align-items-end meal-item-row mb-2">
                                                        <div class="col-12 col-md-5">
                                                            <label class="form-label small mb-1">食品（マスタ）</label>
                                                            <select name="items[{{ $i }}][food_id]" class="form-select form-select-sm food-sel">
                                                                <option value="">選択してください</option>
                                                                @foreach ($foods as $food)
                                                                    <option
                                                                        value="{{ $food->id }}"
                                                                        data-kcal="{{ $food->kcal }}"
                                                                        data-p="{{ $food->protein }}"
                                                                        data-f="{{ $food->fat }}"
                                                                        data-c="{{ $food->carbs }}"
                                                                        @selected((string) ($item['food_id'] ?? '') === (string) $food->id)
                                                                    >{{ $food->emoji }} {{ $food->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-2">
                                                            <label class="form-label small mb-1">量 (g)</label>
                                                            <input
                                                                type="number" name="items[{{ $i }}][amount_g]" class="form-control form-control-sm amount-input"
                                                                min="1" max="2000"
                                                                value="{{ $item['amount_g'] ?? 100 }}"
                                                            >
                                                        </div>
                                                        <div class="col-7 col-md-4">
                                                            <label class="form-label small mb-1">算出値 <span class="auto-badge">自動</span></label>
                                                            <div class="form-control form-control-sm calc-box calc-out">— kcal / P — / F — / C —</div>
                                                        </div>
                                                        <div class="col-2 col-md-1 text-end">
                                                            <button type="button" class="btn btn-sm btn-ghost meal-item-remove" aria-label="この行を削除">✕</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="mealItemAdd" class="btn btn-sm btn-ghost mt-1">＋ 食品を追加</button>
                                        @endif
                                    </div>

                                    {{-- モードB: 自由記述 + 任意の栄養値（複数行まとめて登録可） --}}
                                    <div id="mealModeFree" class="d-none">
                                        @if ($editMeal)
                                            {{-- 先頭行 = 編集対象。追加した行は新しい記録として登録される --}}
                                            <div id="freeItems">
                                                <div class="row g-3 free-item-row mb-2">
                                                    <div class="col-12 col-md-5">
                                                        <label class="form-label small mb-1">食品名（自由入力）</label>
                                                        <input
                                                            type="text" name="food_name_free" class="form-control form-control-sm free-name"
                                                            maxlength="100" placeholder="例: コンビニのカルボナーラ"
                                                            value="{{ old('food_name_free', $editMeal->food_name_free) }}"
                                                        >
                                                    </div>
                                                    <div class="col-2 col-md-1">
                                                        <label class="form-label small mb-1">kcal</label>
                                                        <input type="number" name="kcal" class="form-control form-control-sm free-kcal" min="0" max="9999" placeholder="任意" value="{{ old('kcal', $editMeal->kcal) }}">
                                                    </div>
                                                    <div class="col-2 col-md-1">
                                                        <label class="form-label small mb-1">P</label>
                                                        <input type="number" name="protein" class="form-control form-control-sm free-p" min="0" step="0.1" placeholder="任意" value="{{ old('protein', $editMeal->protein) }}">
                                                    </div>
                                                    <div class="col-2 col-md-1">
                                                        <label class="form-label small mb-1">F</label>
                                                        <input type="number" name="fat" class="form-control form-control-sm free-f" min="0" step="0.1" placeholder="任意" value="{{ old('fat', $editMeal->fat) }}">
                                                    </div>
                                                    <div class="col-2 col-md-1">
                                                        <label class="form-label small mb-1">C</label>
                                                        <input type="number" name="carbs" class="form-control form-control-sm free-c" min="0" step="0.1" placeholder="任意" value="{{ old('carbs', $editMeal->carbs) }}">
                                                    </div>
                                                    <div class="col-2 col-md-1 d-flex align-items-end justify-content-end">
                                                        {{-- 編集対象の行は削除できない（削除はテーブル側の削除ボタンで） --}}
                                                        <button type="button" class="btn btn-sm btn-ghost free-item-remove d-none" aria-label="この行を削除">✕</button>
                                                    </div>
                                                </div>
                                                @foreach (old('free_items', []) as $i => $item)
                                                    <div class="row g-3 free-item-row mb-2">
                                                        <div class="col-12 col-md-5">
                                                            <label class="form-label small mb-1">食品名（自由入力）</label>
                                                            <input
                                                                type="text" name="free_items[{{ $i }}][food_name_free]" class="form-control form-control-sm free-name"
                                                                maxlength="100" placeholder="例: コンビニのカルボナーラ"
                                                                value="{{ $item['food_name_free'] ?? '' }}"
                                                            >
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">kcal</label>
                                                            <input type="number" name="free_items[{{ $i }}][kcal]" class="form-control form-control-sm free-kcal" min="0" max="9999" placeholder="任意" value="{{ $item['kcal'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">P</label>
                                                            <input type="number" name="free_items[{{ $i }}][protein]" class="form-control form-control-sm free-p" min="0" step="0.1" placeholder="任意" value="{{ $item['protein'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">F</label>
                                                            <input type="number" name="free_items[{{ $i }}][fat]" class="form-control form-control-sm free-f" min="0" step="0.1" placeholder="任意" value="{{ $item['fat'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">C</label>
                                                            <input type="number" name="free_items[{{ $i }}][carbs]" class="form-control form-control-sm free-c" min="0" step="0.1" placeholder="任意" value="{{ $item['carbs'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1 d-flex align-items-end justify-content-end">
                                                            <button type="button" class="btn btn-sm btn-ghost free-item-remove" aria-label="この行を削除">✕</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="freeItemAdd" class="btn btn-sm btn-ghost mt-1">＋ 行を追加</button>
                                            <div class="text-secondary small mt-1">追加した行は新しい記録として登録されます。</div>
                                        @else
                                            {{-- 新規登録は「＋ 行を追加」で増やして一括登録 --}}
                                            @php
                                                $oldFreeItems = old('free_items', [['food_name_free' => '', 'kcal' => '', 'protein' => '', 'fat' => '', 'carbs' => '']]);
                                            @endphp
                                            <div id="freeItems">
                                                @foreach ($oldFreeItems as $i => $item)
                                                    <div class="row g-3 free-item-row mb-2">
                                                        <div class="col-12 col-md-5">
                                                            <label class="form-label small mb-1">食品名（自由入力）</label>
                                                            <input
                                                                type="text" name="free_items[{{ $i }}][food_name_free]" class="form-control form-control-sm free-name"
                                                                maxlength="100" placeholder="例: コンビニのカルボナーラ"
                                                                value="{{ $item['food_name_free'] ?? '' }}"
                                                            >
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">kcal</label>
                                                            <input type="number" name="free_items[{{ $i }}][kcal]" class="form-control form-control-sm free-kcal" min="0" max="9999" placeholder="任意" value="{{ $item['kcal'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">P</label>
                                                            <input type="number" name="free_items[{{ $i }}][protein]" class="form-control form-control-sm free-p" min="0" step="0.1" placeholder="任意" value="{{ $item['protein'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">F</label>
                                                            <input type="number" name="free_items[{{ $i }}][fat]" class="form-control form-control-sm free-f" min="0" step="0.1" placeholder="任意" value="{{ $item['fat'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1">
                                                            <label class="form-label small mb-1">C</label>
                                                            <input type="number" name="free_items[{{ $i }}][carbs]" class="form-control form-control-sm free-c" min="0" step="0.1" placeholder="任意" value="{{ $item['carbs'] ?? '' }}">
                                                        </div>
                                                        <div class="col-2 col-md-1 d-flex align-items-end justify-content-end">
                                                            <button type="button" class="btn btn-sm btn-ghost free-item-remove" aria-label="この行を削除">✕</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="freeItemAdd" class="btn btn-sm btn-ghost mt-1">＋ 行を追加</button>
                                        @endif

                                        @if ($recentFreeNames->isNotEmpty())
                                            <div class="form-label small mb-0 mt-3">最近の入力</div>
                                            <div class="recent">
                                                @foreach ($recentFreeNames as $name)
                                                    <button type="button" data-name="{{ $name }}">{{ $name }}</button>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="text-secondary small mt-2">栄養値は分かる範囲でOK。空欄は「未計上」として集計に注記されます。</div>
                                    </div>

                                    {{-- モードC: クイック記録（名前だけ） --}}
                                    <div id="mealModeQuick" class="d-none">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-8">
                                                <label class="form-label small mb-1">なにを食べた？（これだけでOK）</label>
                                                <input
                                                    type="text" name="food_name_free" class="form-control form-control-sm"
                                                    maxlength="100" placeholder="例: 焼肉食べた"
                                                    value="{{ old('food_name_free', $editMeal?->food_name_free) }}"
                                                >
                                            </div>
                                        </div>
                                        <div class="text-secondary small mt-2">栄養値なしで名前だけ記録。続けることが最優先。あとから編集で数値を足せます。</div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-sm btn-accent px-4">{{ $editMeal ? '更新する' : '＋ 追加' }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // modal=1 / 編集中 / バリデーションエラー時はモーダルを自動で開く
        @if (request()->boolean('modal') || $editLog || $editMeal || $errors->any())
            new bootstrap.Modal(document.getElementById('dayModal')).show();
        @endif

        // ===== 食事タブ: ハイブリッド入力 =====
        const mealSeg = document.getElementById('mealModeSeg');
        if (mealSeg) {
            const modeInput = document.getElementById('mealMode');
            const panes = {
                master: document.getElementById('mealModeMaster'),
                free: document.getElementById('mealModeFree'),
                quick: document.getElementById('mealModeQuick'),
            };

            // 非表示モードの入力は disabled にして送信させない
            const applyMode = (mode) => {
                modeInput.value = mode;

                mealSeg.querySelectorAll('button').forEach((btn) => {
                    btn.classList.toggle('active', btn.dataset.mode === mode);
                });

                Object.entries(panes).forEach(([key, pane]) => {
                    const isActive = key === mode;
                    pane.classList.toggle('d-none', !isActive);
                    pane.querySelectorAll('input, select').forEach((el) => {
                        el.disabled = !isActive;
                    });
                });
            };

            mealSeg.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn) {
                    applyMode(btn.dataset.mode);
                }
            });

            applyMode(modeInput.value || 'master');

            // マスタ選択時のPFC自動算出プレビュー（FR-02-4）: 行単位で計算
            const mealForm = document.getElementById('mealForm');

            const calcRow = (row) => {
                const sel = row.querySelector('.food-sel');
                const amt = row.querySelector('.amount-input');
                const out = row.querySelector('.calc-out');

                if (!sel || !out) {
                    return;
                }

                const opt = sel.selectedOptions[0];

                if (!opt || !opt.dataset.kcal) {
                    out.textContent = '— kcal / P — / F — / C —';
                    return;
                }

                const ratio = (parseFloat(amt.value) || 0) / 100;
                const r = (x) => Math.round(parseFloat(x) * ratio * 10) / 10;
                out.textContent = `${Math.round(opt.dataset.kcal * ratio)} kcal / P ${r(opt.dataset.p)} / F ${r(opt.dataset.f)} / C ${r(opt.dataset.c)}`;
            };

            ['change', 'input'].forEach((eventName) => {
                mealForm.addEventListener(eventName, (e) => {
                    const row = e.target.closest('.meal-item-row');
                    if (row) {
                        calcRow(row);
                    }
                });
            });

            mealForm.querySelectorAll('.meal-item-row').forEach(calcRow);

            // 既存行のname属性から次のインデックスを求める（重複送信を防ぐ）
            const nextIndex = (wrap, pattern) => {
                let max = -1;

                wrap.querySelectorAll('select, input').forEach((el) => {
                    const m = el.name.match(pattern);
                    if (m) {
                        max = Math.max(max, parseInt(m[1], 10));
                    }
                });

                return max + 1;
            };

            // 「＋ 食品を追加」で行を増やし、複数のマスタ食品をまとめて登録できる
            const itemsWrap = document.getElementById('mealItems');
            const addBtn = document.getElementById('mealItemAdd');

            if (itemsWrap && addBtn) {
                let itemIndex = nextIndex(itemsWrap, /^items\[(\d+)\]/);

                addBtn.addEventListener('click', () => {
                    const row = itemsWrap.querySelector('.meal-item-row').cloneNode(true);

                    const sel = row.querySelector('.food-sel');
                    sel.name = `items[${itemIndex}][food_id]`;
                    sel.value = '';

                    const amt = row.querySelector('.amount-input');
                    amt.name = `items[${itemIndex}][amount_g]`;
                    amt.value = 100;

                    row.querySelector('.calc-out').textContent = '— kcal / P — / F — / C —';
                    // 編集対象の行（✕非表示）を複製した場合でも追加行は削除できるようにする
                    row.querySelector('.meal-item-remove').classList.remove('d-none');

                    itemsWrap.appendChild(row);
                    itemIndex++;
                });

                // ✕ボタンで行を削除（最後の1行は残す）
                itemsWrap.addEventListener('click', (e) => {
                    const removeBtn = e.target.closest('.meal-item-remove');

                    if (removeBtn && itemsWrap.querySelectorAll('.meal-item-row').length > 1) {
                        removeBtn.closest('.meal-item-row').remove();
                    }
                });
            }

            // 「＋ 行を追加」で自由記述の行を増やし、まとめて登録できる
            const freeWrap = document.getElementById('freeItems');
            const freeAddBtn = document.getElementById('freeItemAdd');
            let freeItemIndex = freeWrap ? nextIndex(freeWrap, /^free_items\[(\d+)\]/) : 0;

            const addFreeRow = () => {
                const index = freeItemIndex++;
                const row = freeWrap.querySelector('.free-item-row').cloneNode(true);

                const fields = [
                    ['.free-name', 'food_name_free'],
                    ['.free-kcal', 'kcal'],
                    ['.free-p', 'protein'],
                    ['.free-f', 'fat'],
                    ['.free-c', 'carbs'],
                ];

                fields.forEach(([selector, key]) => {
                    const input = row.querySelector(selector);
                    input.name = `free_items[${index}][${key}]`;
                    input.value = '';
                });

                // 編集対象の行（✕非表示）を複製した場合でも追加行は削除できるようにする
                row.querySelector('.free-item-remove').classList.remove('d-none');

                freeWrap.appendChild(row);
                return row;
            };

            if (freeWrap && freeAddBtn) {
                freeAddBtn.addEventListener('click', addFreeRow);

                // ✕ボタンで行を削除（最後の1行は残す）
                freeWrap.addEventListener('click', (e) => {
                    const removeBtn = e.target.closest('.free-item-remove');

                    if (removeBtn && freeWrap.querySelectorAll('.free-item-row').length > 1) {
                        removeBtn.closest('.free-item-row').remove();
                    }
                });
            }

            // 「最近の入力」候補をワンタップで再利用
            // 空の行があればそこに、なければ行を増やして入れる
            document.querySelectorAll('.recent button').forEach((btn) => {
                btn.addEventListener('click', () => {
                    let target = null;

                    if (freeWrap) {
                        target = [...freeWrap.querySelectorAll('.free-name')].find((el) => el.value === '')
                            ?? addFreeRow().querySelector('.free-name');
                    } else {
                        // 編集モードは1件単位なのでそのまま上書き
                        target = document.querySelector('#mealModeFree .free-name');
                    }

                    if (target) {
                        target.value = btn.dataset.name;
                    }
                });
            });
        }

        // 種目選択に連動して画像と部位を自動表示する
        const select = document.getElementById('logMaterial');
        if (select) {
            const wrap = document.getElementById('materialPreview');
            const img = document.getElementById('materialPreviewImg');
            const type = document.getElementById('materialPreviewType');

            const updatePreview = () => {
                const opt = select.selectedOptions[0];
                const hasValue = opt && opt.value !== '';

                wrap.classList.toggle('d-none', !hasValue);
                wrap.classList.toggle('d-flex', hasValue);

                if (!hasValue) {
                    return;
                }

                type.textContent = opt.dataset.type || '';

                if (opt.dataset.image) {
                    img.src = opt.dataset.image;
                    img.classList.remove('d-none');
                } else {
                    img.classList.add('d-none');
                }
            };

            select.addEventListener('change', updatePreview);
            updatePreview();
        }
    });
</script>
@endpush
