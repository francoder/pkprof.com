<?php
define('SITE_WEBFOLDER', basename(__DIR__));
chdir(realpath(__DIR__ . '/..'));
require_once 'vendor/autoload.php';
require_once 'vendor/bigsiter/bigsiter-cms-core/src/bootstrap.php';
