@extends('layouts.app')

@push('styles')
<style>
  :root {
    --bg: #f5f6f8;
    --panel: #ffffff;
    --panel-2: #f0f2f5;
    --line: #e3e6ea;
    --text: #1d2129;
    --muted: #6c727d;
    --accent: #e23744;
    --accent-soft: rgba(226, 55, 68, 0.14);
    --up: #1f9d63;
    --down: #e23744;
    --train: #3b6fe0;
    --glute: #c4547d;
    --shoulder: #3a6fd6;
    --leg: #1e8f5c;
    --back: #7d47c8;
    --arm: #a87520;
  }

  .sec-label {
    font-size: .74rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .08em;
    font-weight: 700;
  }

  .note {
    color: var(--muted);
    font-size: .78rem;
  }

  table {
    --bs-table-bg: transparent;
    color: var(--text);
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
  }

  thead th {
    color: var(--muted) !important;
    font-weight: 600;
    font-size: .76rem;
    border: none;
    padding: .6rem .85rem;
    text-align: right;
    white-space: nowrap;
  }

  thead th.exh {
    text-align: left;
  }

  thead th.cur {
    color: var(--text) !important;
  }

  tbody td {
    border-top: 1px solid var(--line) !important;
    vertical-align: middle;
    padding: .7rem .85rem;
    font-size: .92rem;
    text-align: right;
    white-space: nowrap;
  }

  tbody td.ex {
    text-align: left;
    font-weight: 600;
  }

  .num {
    font-variant-numeric: tabular-nums;
  }

  .cur-cell {
    font-weight: 800;
    color: var(--text);
  }

  .past {
    color: var(--muted);
  }

  .trend {
    font-size: .74rem;
    font-weight: 700;
    margin-left: .15rem;
  }

  .trend.up {
    color: var(--up);
  }

  .trend.down {
    color: var(--down);
  }

  .trend.flat {
    color: var(--muted);
  }

  .na {
    color: #aab0b9;
  }

  .chip {
    font-size: .66rem;
    padding: .1rem .45rem;
    border-radius: 999px;
    font-weight: 600;
    margin-left: .4rem;
    vertical-align: middle;
    border: 1px solid var(--line);
  }

  .chip.glute {
    color: var(--glute);
    border-color: rgba(196, 84, 125, .4);
    background: rgba(196, 84, 125, .08);
  }

  .chip.shoulder {
    color: var(--shoulder);
    border-color: rgba(58, 111, 214, .4);
    background: rgba(58, 111, 214, .08);
  }

  .chip.leg {
    color: var(--leg);
    border-color: rgba(30, 143, 92, .4);
    background: rgba(30, 143, 92, .08);
  }

  .chip.back {
    color: var(--back);
    border-color: rgba(125, 71, 200, .4);
    background: rgba(125, 71, 200, .08);
  }

  .chip.arm {
    color: var(--arm);
    border-color: rgba(168, 117, 32, .4);
    background: rgba(168, 117, 32, .08);
  }

  .cur-col {
    background: rgba(226, 55, 68, .05);
  }

  .mini-spark {
    display: inline-block;
    width: 54px;
    height: 18px;
    vertical-align: middle;
    margin-left: .3rem;
  }

  .empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--muted);
  }

  .empty-state .big {
    font-size: 2rem;
    margin-bottom: .5rem;
  }
</style>
@endpush

@section('content')
<main class="container py-4 py-lg-5">
  <h1 class="h3 mb-4">比較表</h1>

  <div class="note mb-3">種目別 重量推移マトリクス（行=種目 / 列=月 / セル=その月のMAX重量）</div>
  <div class="panel p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="sec-label">種目別 MAX重量の推移</span>
      <span class="note">直近5ヶ月 / 単位 kg / 矢印は前月比</span>
    </div>

    @if ($matrixData->isEmpty())
      <div class="empty-state">
        <div class="big">📊</div>
        <div>今月の重量記録がありません</div>
        <div class="note mt-2">ダッシュボードからトレーニング記録を追加してください</div>
      </div>
    @else
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="exh">種目</th>
              @foreach ($monthLabels as $index => $label)
                <th class="{{ $index === 4 ? 'cur cur-col' : '' }}">{{ $label }}</th>
              @endforeach
              <th>推移</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($matrixData as $row)
              <tr>
                <td class="ex">
                  {{ $row['material']->title }}
                  @php
                    $typeMap = [
                      'legs' => ['leg', '脚'],
                      'shoulders_arms' => $row['material']->title === 'ダンベルカール' || str_contains($row['material']->title, 'カール') || str_contains($row['material']->title, 'エクステンション') || str_contains($row['material']->title, 'プッシュダウン') ? ['arm', '腕'] : ['shoulder', '肩'],
                      'back' => ['back', '背中'],
                    ];
                    $chip = $typeMap[$row['material']->type] ?? ['off', 'その他'];
                  @endphp
                  <span class="chip {{ $chip[0] }}">{{ $chip[1] }}</span>
                </td>
                @foreach ($row['monthlyMax'] as $index => $weight)
                  <td class="num {{ $weight === null ? 'na' : ($index === 4 ? 'cur-cell cur-col' : 'past') }} {{ $index === 4 ? 'cur-col' : '' }}">
                    {{ $weight !== null ? rtrim(rtrim(number_format($weight, 1), '0'), '.') : '—' }}
                    @if ($index === 4 && $row['trend'])
                      <span class="trend {{ $row['trend'] }}">
                        @if ($row['trend'] === 'up') ▲{{ $row['trendValue'] }}
                        @elseif ($row['trend'] === 'down') ▼{{ $row['trendValue'] }}
                        @else {{ $row['trendValue'] }}
                        @endif
                      </span>
                    @endif
                  </td>
                @endforeach
                <td>
                  @if ($row['sparklineSvg'])
                    @php
                      $sparkColor = $row['trend'] === 'up' ? '#1f9d63' : ($row['trend'] === 'down' ? '#e23744' : '#6c727d');
                    @endphp
                    <svg class="mini-spark" viewBox="0 0 54 18">
                      <polyline points="{{ $row['sparklineSvg'] }}" fill="none" stroke="{{ $sparkColor }}" stroke-width="1.5"/>
                    </svg>
                  @else
                    <span class="na">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="note mt-3">「—」はその月に記録なし。今月列を強調し、横に読むと各種目の成長が分かる。</div>
    @endif
  </div>
</main>
@endsection
