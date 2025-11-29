<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/config/database.php';

// Configurar rutas y middlewares
$routes = require __DIR__ . '/../app/config/routes.php';
$corsMiddleware = require __DIR__ . '/../app/middlewares/Cors.php';

// Crear aplicación
$app = AppFactory::create();

// Aplicar middleware CORS
$corsMiddleware($app);

// Registrar rutas
$routes($app);

// Ejecutar aplicación
$app->run();