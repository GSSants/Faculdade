<?php

require_once __DIR__ . '/src/Controllers/AuthController.php';

if (AuthController::verificarSessao() !== null) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Imobiliário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="card auth-card p-4">
            <!-- Cabeçalho -->
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">Gestão Imobiliária</h4>
                <p class="text-muted small mb-0">Acesso ao painel de imóveis e clientes</p>
            </div>

            <div id="alertaAuth" class="alert d-none py-2 small" role="alert"></div>

            <!-- Formulário Login -->
            <form id="formLogin">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">E-mail</label>
                    <input type="email" id="loginEmail" class="form-control" value="admin@imobiliaria.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Senha</label>
                    <input type="password" id="loginSenha" class="form-control" value="123456" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>
                <div class="text-center">
                    <button type="button" id="btnMostrarCadastro" class="btn btn-link btn-sm text-decoration-none">
                        Criar nova conta
                    </button>
                </div>
            </form>

            <!-- Formulário Cadastro -->
            <form id="formCadastro" class="d-none">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nome</label>
                    <input type="text" id="cadNome" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">E-mail</label>
                    <input type="email" id="cadEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Senha</label>
                    <input type="password" id="cadSenha" class="form-control" minlength="4" required>
                </div>
                <button type="submit" class="btn btn-success w-100 mb-3">Cadastrar</button>
                <div class="text-center">
                    <button type="button" id="btnMostrarLogin" class="btn btn-link btn-sm text-decoration-none">
                        Voltar para o login
                    </button>
                </div>
            </form>

            <hr class="my-3">
            <div class="text-muted text-center small">
                Demo: <strong>admin@imobiliaria.com</strong> / <strong>123456</strong>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="public/assets/js/auth.js"></script>
</body>
</html>
