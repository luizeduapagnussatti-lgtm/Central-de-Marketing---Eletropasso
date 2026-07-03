<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = marketing_pdo();
$sql = file_get_contents(dirname(__DIR__) . '/sql/007_fabric_state.sql');
$pdo->exec($sql);
echo "Migration 007 OK\n";
