<?php

require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/AuthController.php';

// Controlador de Clientes

class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }

    // Processar CRUD de clientes
    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        AuthController::exigirLoginApi();

        $acao = $_GET['acao'] ?? $_POST['acao'] ?? 'listar';

        try {
            if ($acao === 'listar') {
                $busca = trim($_GET['busca'] ?? '');
                echo json_encode(['sucesso' => true, 'dados' => $this->model->listar($busca)]);
                return;
            }

            if ($acao === 'buscar') {
                $id = (int) ($_GET['id'] ?? 0);
                $registro = $this->model->buscarPorId($id);
                echo json_encode(['sucesso' => $registro !== null, 'dados' => $registro]);
                return;
            }

            if ($acao === 'salvar') {
                $id = (int) ($_POST['id'] ?? 0);
                if (trim($_POST['nome'] ?? '') === '' || trim($_POST['telefone'] ?? '') === '') {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome e telefone são obrigatórios.']);
                    return;
                }

                if ($id > 0) {
                    $this->model->atualizar($id, $_POST);
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Cliente atualizado com sucesso.']);
                } else {
                    $novoId = $this->model->inserir($_POST);
                    echo json_encode(['sucesso' => true, 'id' => $novoId, 'mensagem' => 'Cliente cadastrado com sucesso.']);
                }
                return;
            }

            if ($acao === 'excluir') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
                    return;
                }
                $this->model->excluir($id);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Cliente excluído com sucesso.']);
                return;
            }

            http_response_code(400);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
}
