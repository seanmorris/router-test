<?php
use \SeanMorris\RouteTest\Router;
use \SeanMorris\RouteTest\Request;

require '/app/vendor/autoload.php';

Router::resource('patients');
Router::resource('patients.metrics');

$request = new Request();

echo Router::route($request);
