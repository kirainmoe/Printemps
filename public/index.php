<?php

require dirname(__FILE__) . '/../Printemps.php';

$printemps = Printemps::init(array(
    "database" => array(
        "host" => "localhost",
        "user" => "root",
        "password" => "root",
        "name" => "printemps",
        "port" => 3306,
        "encode" => "utf8",
        "method" => "mysqli"
        ),
    "initial" => array(
        "APP_ROOT_DIR" => dirname(__FILE__),
        "APP_NAME" => "printemps",
        "APP_VERSION" => "alpha",
        "APP_DEBUG_MODE" => true,
        "APP_ENTRY_MODE" => 2
        ),
    "router" => array(
        "class" => array(
            /* e.g. visit /example/index => call `indexController->index()` */
            "example" => "index"
            ),
        "method" => array(
            /* e.g. visit /index/example  => call `indexController->index()`*/
            "index:example" => "index"
            )
        )
    ));
