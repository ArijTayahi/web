<?php

use Symfony\Component\HttpFoundation\Request;

if (is_file($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/index.php';
