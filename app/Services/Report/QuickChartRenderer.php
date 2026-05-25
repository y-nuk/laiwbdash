<?php

namespace App\Services\Report;

/**
 * QuickChart.io (https://quickchart.io) を使って Chart.js 設定 JSON を PNG URL に変換するヘルパー。
 *
 * 無料枠：月 50,000 リクエスト。PNG 画像 URL を返すので、PDF テンプレ内で <img src="..."> として埋め込む。
 */
class QuickChartRenderer
{
    private const BASE = 'https://quickchart.io/chart';

    /**
     * 全 KW 平均順位の折れ線グラフ URL を返す。Y 軸反転で 1 位が上。
     *
     * @param array<string, float|null> $dailyAvg 'm/d' => 平均順位（null=データなし）
     */
    public function averageRankLine(array $dailyAvg, int $width = 700, int $height = 280): string
    {
        $config = [
            'type' => 'line',
            'data' => [
                'labels' => array_keys($dailyAvg),
                'datasets' => [[
                    'label' => '全 KW 平均順位',
                    'data' => array_values($dailyAvg),
                    'borderColor' => 'rgb(13, 110, 253)',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'borderWidth' => 2,
                    'tension' => 0.2,
                    'fill' => true,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'title' => ['display' => true, 'text' => '全キーワード平均順位の推移（対象月内）'],
                ],
                'scales' => [
                    'y' => [
                        'reverse' => true,
                        'min' => 1,
                        'title' => ['display' => true, 'text' => '順位（小さいほど上位）'],
                    ],
                ],
            ],
        ];
        return $this->buildUrl($config, $width, $height);
    }

    /**
     * キーワード別順位推移の折れ線グラフ（複数 KW を 1 グラフに重ねる）。
     *
     * @param array<int, array{keyword:string, days:array<string,int|null>}> $matrix
     */
    public function keywordHistoryLine(array $matrix, int $width = 700, int $height = 380): string
    {
        $palette = [
            'rgb(13, 110, 253)', 'rgb(220, 53, 69)', 'rgb(25, 135, 84)',
            'rgb(255, 193, 7)', 'rgb(111, 66, 193)', 'rgb(13, 202, 240)',
            'rgb(214, 51, 132)', 'rgb(102, 16, 242)', 'rgb(253, 126, 20)',
            'rgb(108, 117, 125)',
        ];

        $labels = $matrix ? array_keys($matrix[0]['days']) : [];
        $datasets = [];
        foreach ($matrix as $i => $row) {
            $datasets[] = [
                'label' => $row['keyword'],
                'data' => array_values($row['days']),
                'borderColor' => $palette[$i % count($palette)],
                'backgroundColor' => 'transparent',
                'borderWidth' => 1.5,
                'tension' => 0.2,
                'spanGaps' => false,
            ];
        }

        $config = [
            'type' => 'line',
            'data' => ['labels' => $labels, 'datasets' => $datasets],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom', 'labels' => ['boxWidth' => 12, 'font' => ['size' => 10]]],
                    'title' => ['display' => true, 'text' => 'キーワード別 順位推移（対象月内）'],
                ],
                'scales' => [
                    'y' => ['reverse' => true, 'min' => 1, 'title' => ['display' => true, 'text' => '順位']],
                ],
            ],
        ];
        return $this->buildUrl($config, $width, $height);
    }

    private function buildUrl(array $config, int $width, int $height): string
    {
        $query = http_build_query([
            'c' => json_encode($config, JSON_UNESCAPED_UNICODE),
            'w' => $width,
            'h' => $height,
            'bkg' => 'white',
            'devicePixelRatio' => 2,
        ]);
        return self::BASE . '?' . $query;
    }
}
