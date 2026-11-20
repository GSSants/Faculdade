<?php

require_once __DIR__ . '/../Models/Imovel.php';
require_once __DIR__ . '/AuthController.php';

// Controlador de Imóveis

class ImovelController
{
    private Imovel $model;

    public function __construct()
    {
        $this->model = new Imovel();
    }

    // Processar CRUD de imóveis
    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $usuario = AuthController::exigirLoginApi();

        $acao = $_GET['acao'] ?? $_POST['acao'] ?? 'listar';

        try {
            if ($acao === 'listar') {
                $busca = trim($_GET['busca'] ?? '');
                $status = trim($_GET['status'] ?? '');
                echo json_encode([
                    'sucesso' => true,
                    'dados' => $this->model->listar($busca, $status),
                    'resumo' => $this->model->resumo(),
                ]);
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
                $obrigatorios = ['titulo', 'tipo', 'finalidade', 'valor', 'area_m2', 'cep', 'logradouro', 'bairro', 'cidade', 'uf'];

                foreach ($obrigatorios as $campo) {
                    if (trim((string) ($_POST[$campo] ?? '')) === '') {
                        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.']);
                        return;
                    }
                }

                if ($id > 0) {
                    $this->model->atualizar($id, $_POST);
                    echo json_encode(['sucesso' => true, 'mensagem' => 'Imóvel atualizado com sucesso.']);
                } else {
                    $novoId = $this->model->inserir($_POST, (int) $usuario['id']);
                    echo json_encode(['sucesso' => true, 'id' => $novoId, 'mensagem' => 'Imóvel cadastrado com sucesso.']);
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
                echo json_encode(['sucesso' => true, 'mensagem' => 'Imóvel excluído com sucesso.']);
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
