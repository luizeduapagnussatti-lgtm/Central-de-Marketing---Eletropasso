<?php
declare(strict_types=1);
/** @var string $page_title */
$page_title = $page_title ?? 'Central de Marketing';
$app_name = 'Central de Marketing';

$icon_svg = marketing_brand_asset_url('assets/brand/icon_central_marketing.svg');
$icon_32 = marketing_brand_asset_url('assets/brand/icon_central_marketing-32.png');
$icon_180 = marketing_brand_asset_url('assets/brand/icon_central_marketing-180.png');
$manifest = marketing_brand_asset_url('manifest.webmanifest');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($app_name, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="application-name" content="<?= htmlspecialchars($app_name, ENT_QUOTES, 'UTF-8') ?>">
<meta name="theme-color" content="#991b1b">
<link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($icon_svg, ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($icon_32, ENT_QUOTES, 'UTF-8') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($icon_180, ENT_QUOTES, 'UTF-8') ?>">
<link rel="manifest" href="<?= htmlspecialchars($manifest, ENT_QUOTES, 'UTF-8') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
