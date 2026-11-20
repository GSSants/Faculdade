<?php

// Endpoint imoveis

require_once __DIR__ . '/../src/Controllers/ImovelController.php';

$controller = new ImovelController();
$controller->handle();
