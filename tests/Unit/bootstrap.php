<?php

$extensionRoot = dirname(__DIR__, 2);
$projectRoot = dirname($extensionRoot, 2);

require $projectRoot.'/vendor/autoload.php';

spl_autoload_register(static function (string $class) use ($extensionRoot): void {
    $prefixes = [
        'Yuyuko233\\VfxskillDailyCheckInStreak\\Tests\\' => $extensionRoot.'/tests/',
        'Yuyuko233\\VfxskillDailyCheckInStreak\\' => $extensionRoot.'/src/',
    ];

    foreach ($prefixes as $prefix => $basePath) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
        $filePath = $basePath.$relativePath;

        if (is_file($filePath)) {
            require $filePath;
        }

        return;
    }
}, true, true);
