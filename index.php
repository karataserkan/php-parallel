<?php
//We loads autoloads
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/Psr4AutoloaderClass.php';

(new app\Psr4AutoloaderClass())->register();

$GLOBALS['config'] = $config = require __DIR__.'/config/config.php';

try {
    //first param is this file
    //second param is controller/action
    if (!isset($argv[1])) {
        throw new \OutOfRangeException('Missing parameter', 1);
    }

    $parts = explode('/', $argv[1]);
    if (count($parts) < 2) {
        throw new \InvalidArgumentException(
            'Call parameter must be type controller/action',
            1
        );
    }
    $controller = "app\controllers\\".ucfirst($parts[0]).'Controller';
    $method = $parts[1];

    if (!class_exists($controller)) {
        throw new \BadMethodCallException('Controller does not exists', 1);
    }

    $controller = new $controller();

    if (!method_exists($controller, $method)) {
        throw new \BadMethodCallException('Action does not exists', 1);
    }

    $controller->$method($argv);
} catch (\Exception $e) {
    $out = fopen('php://stdout', 'w');
    fputs($out, $e->getMessage().PHP_EOL);
    fclose($out);
}

exit();
