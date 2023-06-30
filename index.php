<?php
declare(strict_types=1);
spl_autoload_register(function ($class){
    require __DIR__ . "/src/$class.php"; //adding classes
});

set_error_handler("ErrorHandler::handleError"); //Undefined array key error
set_exception_handler("ErrorHandler::handleException");

header("Content-type: application/json; charset=UTF-8"); //CONVERTING CONTENT TYPE TO JSON

$parts = explode("/", $_SERVER["REQUEST_URI"]);
//  print_r($parts);

if($parts[2] != "products"){
    http_response_code(404);
    exit;
}

$id = $parts[3] ?? null;
$database = new Database("localhost", "product_db", "root", "");
$database->getConnection();

$gateway = new ProductGateway($database);

$controller = new ProductController($gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);