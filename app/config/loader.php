<?php

$loader = new \Phalcon\Autoload\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->setNamespaces(
    [
        'Holidays\Controllers' => $config->application->controllersDir,
        'Holidays\Models' => $config->application->modelsDir,
        'Holidays\Library' => $config->application->libraryDir,
        'Holidays\Forms' => $config->application->formsDir,
    ]
)->register();
