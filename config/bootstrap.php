<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';

marketing_load_env(marketing_path('.env'));

require_once marketing_path('services/LoggerService.php');
require_once marketing_path('services/EncarteService.php');
require_once marketing_path('services/EncarteRenderService.php');
require_once marketing_path('services/HubPrecificacaoService.php');
require_once marketing_path('services/MarketingAssistant.php');
require_once marketing_path('services/ConfigService.php');
require_once marketing_path('services/ModeloLayoutService.php');
