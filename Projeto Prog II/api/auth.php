<?php

// Endpoint autenticacao

require_once __DIR__ . '/../src/Controllers/AuthController.php';

$controller = new AuthController();
$controller->handle();
