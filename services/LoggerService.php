<?php
declare(strict_types=1);

/**
 * Logger simples em arquivo mensal.
 */
class LoggerService
{
    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = marketing_path('storage/logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/marketing_' . date('Y-m') . '.log';
        $ctx = $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = sprintf("[%s] [%s] %s%s\n", date('Y-m-d H:i:s'), strtoupper($level), $message, $ctx);

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }
}
