<?php
declare(strict_types=1);

/**
 * Gera PNG palco placeholder para modelo_03 (1080x1920).
 * Uso: php scripts/gerar_palco_placeholder.php [codigo] [formato]
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

$codigo = $argv[1] ?? 'modelo_03';
$formato = $argv[2] ?? '9x16';
$dims = marketing_formatos()[$formato] ?? marketing_formatos()['9x16'];
$w = (int) $dims['width'];
$h = (int) $dims['height'];

if (!function_exists('imagecreatetruecolor')) {
    echo "ERRO: extensao GD nao disponivel.\n";
    exit(1);
}

$img = imagecreatetruecolor($w, $h);
$bg = imagecolorallocate($img, 3, 11, 23);
imagefill($img, 0, 0, $bg);

for ($i = 0; $i < 8; $i++) {
    $alpha = (int) (30 + $i * 8);
    $blue = imagecolorallocatealpha($img, 29, 78, 216, 127 - min(127, $alpha));
    imagefilledellipse($img, (int) ($w * 0.15), (int) ($h * 0.12) + $i * 20, 400, 400, $blue);
}

$red = imagecolorallocatealpha($img, 220, 38, 38, 110);
imagefilledellipse($img, (int) ($w * 0.85), (int) ($h * 0.75), 500, 500, $red);

$white = imagecolorallocate($img, 255, 255, 255);
$gray = imagecolorallocate($img, 156, 163, 175);
$redTxt = imagecolorallocate($img, 255, 26, 26);

imagestring($img, 5, 40, 80, 'PROMOCAO', $redTxt);
imagestring($img, 5, 40, 120, 'FECHA MES', $white);
imagestring($img, 3, 40, 170, 'OFERTAS IMPERDIVEIS!', $white);
imagestring($img, 2, (int) ($w / 2) - 180, 220, 'QUALIDADE, SEGURANCA E OS MELHORES PRECOS', $gray);

$logoPath = marketing_path(marketing_logo_relativo('branca'));
if (is_file($logoPath)) {
    $logo = @imagecreatefrompng($logoPath);
    if ($logo !== false) {
        $lw = imagesx($logo);
        $lh = imagesy($logo);
        $targetW = 180;
        $targetH = (int) round($lh * ($targetW / $lw));
        imagecopyresampled($img, $logo, $w - $targetW - 40, 60, 0, 0, $targetW, $targetH, $lw, $lh);
        imagedestroy($logo);
    }
}

$dir = marketing_path('assets/modelos/fundos');
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$output = $dir . '/' . $codigo . '_' . $formato . '.png';
imagepng($img, $output);
imagedestroy($img);

echo "Palco salvo em: assets/modelos/fundos/{$codigo}_{$formato}.png\n";
