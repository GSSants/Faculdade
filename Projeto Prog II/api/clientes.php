<?php

// Endpoint clientes

require_once __DIR__ . '/../src/Controllers/ClienteController.php';

$controller = new ClienteController();
$controller->handle();
