<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>{{ $data->periodLabel() }} MEO 月次レポート</title>
<style>
body { font-family: ipaexgothic; font-size: 10pt; color: #1f2937; line-height: 1.5; }
.cover { text-align: center; padding-top: 120pt; }
.cover h1 { font-size: 26pt; margin: 28pt 0 4pt; color: #0d6efd; font-weight: bold; }
.cover .subtitle { font-size: 12pt; color: #6b7280; }
.cover .meta { margin-top: 60pt; font-size: 14pt; line-height: 2; }
.cover .meta .company { font-size: 18pt; font-weight: bold; }
.cover-footer { margin-top: 100pt; font-size: 9pt; color: #6b7280; }
.section-title { font-size: 14pt; font-weight: bold; margin: 0 0 10pt; padding-bottom: 4pt; border-bottom: 2pt solid #0d6efd; color: #0d6efd; }
.section-sub { font-size: 9pt; color: #6b7280; margin: -6pt 0 8pt; }
.header-bar { font-size: 9pt; color: #6b7280; margin-bottom: 8pt; }
.header-bar .right { float: right; }
.kpi-table { width: 100%; border-collapse: separate; border-spacing: 4pt 0; margin-bottom: 16pt; }
.kpi-table td { background: #f8f9fa; border: 1pt solid #e5e7eb; border-radius: 4pt; padding: 12pt 8pt; text-align: center; width: 25%; }
.kpi-label { font-size: 9pt; color: #6b7280; }
.kpi-value { font-size: 22pt; font-weight: bold; margin-top: 4pt; color: #0d6efd; }
.kpi-value.muted { color: #9ca3af; font-size: 16pt; }
table.matrix { width: 100%; border-collapse: collapse; }
table.matrix th, table.matrix td { padding: 3pt 4pt; border: 0.5pt solid #d1d5db; font-size: 7.5pt; text-align: center; }
table.matrix th { background: #f3f4f6; font-weight: bold; }
table.matrix td.kw-cell { text-align: left; min-width: 90pt; font-size: 8pt; }
table.matrix td.stat { background: #f9fafb; font-weight: bold; }
table.matrix .rank-top { background: #d1fae5; font-weight: bold; }
table.matrix .rank-good { background: #fef9c3; }
table.matrix .rank-out { color: #9ca3af; }
.comment-box { border: 1pt solid #d1d5db; border-radius: 4pt; padding: 12pt; min-height: 250pt; background: #fafafa; white-space: pre-wrap; }
</style>
</head>
<body>

{{-- ===== Page 1: 表紙 ===== --}}
<div class="cover">
    <img src="{{ public_path('img/laiweb-dash-icon.png') }}" style="width: 80pt;" alt="">
    <h1>MEO 月次レポート</h1>
    <p class="subtitle">{{ $data->periodLabel() }}</p>
    <div class="meta">
        <p class="company">{{ $data->store->company->name }} 御中</p>
        <p>店舗：{{ $data->store->name }}</p>
        <p>出力日：{{ now()->isoFormat('Y 年 M 月 D 日') }}</p>
    </div>
    <div class="cover-footer">
        株式会社 L'aide / laiweb-dash
    </div>
</div>

<pagebreak />

{{-- ===== Page 2: KPI + 平均順位グラフ ===== --}}
<div class="header-bar">
    {{ $data->store->company->name }} / {{ $data->store->name }}
    <span class="right">{{ $data->periodLabel() }} 月次レポート</span>
</div>

<h2 class="section-title">KPI サマリー</h2>
<p class="section-sub">期間内（{{ $data->periodStart()->format('Y/m/d') }} 〜 {{ $data->periodEnd()->format('Y/m/d') }}）の集計値</p>

<table class="kpi-table">
    <tr>
        <td>
            <div class="kpi-label">計測キーワード数</div>
            <div class="kpi-value">{{ $kpis['keyword_count'] }}</div>
        </td>
        <td>
            <div class="kpi-label">平均順位</div>
            <div class="kpi-value {{ $kpis['avg_rank'] === null ? 'muted' : '' }}">
                {{ $kpis['avg_rank'] ?? '—' }}
            </div>
        </td>
        <td>
            <div class="kpi-label">1〜3 位 KW</div>
            <div class="kpi-value">{{ $kpis['top3_count'] }}</div>
        </td>
        <td>
            <div class="kpi-label">圏外 KW</div>
            <div class="kpi-value {{ $kpis['out_ranked_count'] > 0 ? '' : 'muted' }}">{{ $kpis['out_ranked_count'] }}</div>
        </td>
    </tr>
</table>

<h2 class="section-title">全キーワード平均順位の推移（対象月内）</h2>
<img src="{{ $charts['avg_rank'] }}" style="width: 100%;" alt="">

<pagebreak />

{{-- ===== Page 3: KW 別グラフ ===== --}}
<div class="header-bar">
    {{ $data->store->company->name }} / {{ $data->store->name }}
    <span class="right">{{ $data->periodLabel() }} 月次レポート</span>
</div>

<h2 class="section-title">キーワード別 順位推移</h2>
<p class="section-sub">各キーワードの対象月内の順位推移（折れ線が下にあるほど上位）</p>
<img src="{{ $charts['keyword_history'] }}" style="width: 100%;" alt="">

<pagebreak orientation="L" />

{{-- ===== Page 4: KW × 日付マトリックス（横向き） ===== --}}
<div class="header-bar">
    {{ $data->store->company->name }} / {{ $data->store->name }}
    <span class="right">{{ $data->periodLabel() }} 月次レポート</span>
</div>

<h2 class="section-title">キーワード別 日次順位データ</h2>
<p class="section-sub">— = 圏外（または未取得）／緑 = 1〜3 位／黄 = 4〜10 位</p>

@if (count($matrix) > 0)
<table class="matrix">
    <thead>
        <tr>
            <th class="kw-cell">キーワード</th>
            <th>最高</th>
            <th>最低</th>
            <th>平均</th>
            <th>圏外</th>
            @foreach (array_keys($matrix[0]['days']) as $date)
                <th>{{ $date }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($matrix as $row)
            <tr>
                <td class="kw-cell">{{ $row['keyword'] }}</td>
                <td class="stat">{{ $row['best'] ?? '—' }}</td>
                <td class="stat">{{ $row['worst'] ?? '—' }}</td>
                <td class="stat">{{ $row['avg'] ?? '—' }}</td>
                <td class="stat">{{ $row['out_count'] }}</td>
                @foreach ($row['days'] as $position)
                    @php
                        $class = $position === null ? 'rank-out' : ($position <= 3 ? 'rank-top' : ($position <= 10 ? 'rank-good' : ''));
                    @endphp
                    <td class="{{ $class }}">{{ $position ?? '—' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@else
    <p style="text-align: center; color: #6b7280; padding: 40pt;">期間内に計測キーワードがありません。</p>
@endif

<pagebreak orientation="P" />

{{-- ===== Page 5: 担当者コメント ===== --}}
<div class="header-bar">
    {{ $data->store->company->name }} / {{ $data->store->name }}
    <span class="right">{{ $data->periodLabel() }} 月次レポート</span>
</div>

<h2 class="section-title">今月の所感・次月のアクション</h2>
<p class="section-sub">担当者からのコメント</p>

<div class="comment-box">{{ $comment ?? '' }}</div>

<div style="margin-top: 40pt; font-size: 9pt; color: #6b7280; text-align: center;">
    本レポートは laiweb-dash により自動生成されました。<br>
    ご不明な点は運営担当（info@laiweb-dash.com）までお問い合わせください。
</div>

</body>
</html>
