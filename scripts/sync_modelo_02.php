<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = marketing_pdo();
$sql = file_get_contents(dirname(__DIR__) . '/sql/003_modelo_02.sql');
$pdo->exec($sql);

echo "modelo_02 registrado\n";
