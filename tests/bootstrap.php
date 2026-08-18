<?php

require dirname(__DIR__).'/vendor/autoload.php';

/**
 * Laravel RefreshDatabase carga database/schema/mysql-schema.sql con el cliente
 * `mysql`. En Windows + Laragon ese binario no suele estar en PATH.
 */
if (PHP_OS_FAMILY === 'Windows') {
    $mysql = null;

    foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
        if ($dir !== '' && is_file($dir.DIRECTORY_SEPARATOR.'mysql.exe')) {
            $mysql = $dir.DIRECTORY_SEPARATOR.'mysql.exe';
            break;
        }
    }

    if ($mysql === null) {
        $candidates = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysql.exe') ?: [];

        if ($candidates !== []) {
            $binDir = dirname($candidates[0]);
            $path = $binDir.PATH_SEPARATOR.getenv('PATH');
            putenv('PATH='.$path);
            $_ENV['PATH'] = $path;
            $_SERVER['PATH'] = $path;
        }
    }
}
