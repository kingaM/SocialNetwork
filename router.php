<?php

    session_start();

    require_once(__DIR__ . '/libs/zaphpa.lib.php');

    foreach (glob('controllers/*.php') as $filename) {
        include_once $filename;
    }

    $router = new Zaphpa_Router();

    $router->addRoute(array(
        'path' => '/',
        'get' => array('Index', 'getPage'),
    ));

    try {
        $router->route();
    } catch (Zaphpa_InvalidPathException $ex) {
        header("Content-Type: application/json;", TRUE, 404);
        $out = array("error" => "not found");
        die(json_encode($out));
    }

?>
