<?php

session_start();

function exigirLoginApi(): array
{
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.']);
        exit;
    }

    return $_SESSION['usuario'];
}
