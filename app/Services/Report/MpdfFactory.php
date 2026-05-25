<?php

namespace App\Services\Report;

use Mpdf\Mpdf;

/**
 * mPDF インスタンスの生成集約。IPAex Gothic 日本語フォント設定込み。
 */
class MpdfFactory
{
    public function createA4Landscape(): Mpdf
    {
        return new Mpdf($this->baseConfig() + ['format' => 'A4-L']);
    }

    public function createA4Portrait(): Mpdf
    {
        return new Mpdf($this->baseConfig() + ['format' => 'A4']);
    }

    private function baseConfig(): array
    {
        $tempDir = storage_path('mpdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        return [
            'mode' => 'utf-8',
            'default_font_size' => 10,
            'default_font' => 'ipaexgothic',
            'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [storage_path('fonts')]),
            'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
                'ipaexgothic' => [
                    'R' => 'ipaexg.ttf',
                    'B' => 'ipaexg.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'tempDir' => $tempDir,
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_header' => 6,
            'margin_footer' => 6,
        ];
    }
}
