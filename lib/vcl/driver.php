<?php
$controllerCall = 'home';
$action = 'logout';

session_start();

if (isset($_SESSION['Id']) || strtolower($_REQUEST['action']) == "setlogin") {
    $controllerCall = isset($_REQUEST['controller']) ? strtolower($_REQUEST['controller']) : 'home';
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
}

$dir = __DIR__ . "/../../ctl/$controllerCall.php";

require_once file_exists($dir) ? $dir : __DIR__ . '/../mvc/controller.php';
$controller = (file_exists($dir) ? ucwords($controllerCall) : "") . 'Controller';

if (file_exists($dir)) {
    $controller = new $controller($action);
} else {
    $controller = new $controller($controllerCall, $action);
}

call_user_func(array($controller, $action));
?>