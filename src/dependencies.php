<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// database
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    if ($db['sgbd'] == 'oci') {
        $dsn = $db['sgbd']
            . ':dbname= (DESCRIPTION =(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP) (HOST = ' . $db['host'] . ')(PORT = '
            . (isset($db['port']) ? $db['port'] : '1521') . ')))(CONNECT_DATA = (SID = ' . $db['dbname'] . ')))';
    } else {
        $dsn = $db['sgbd']
            . ':host=' . $db['host']
            . (isset($db['port']) ? ';port=' . $db['port'] : '')
            . ';dbname=' . $db['dbname'];
    }
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
