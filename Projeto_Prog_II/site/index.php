<?php

require __DIR__ . '/sessao.php';

$usuario = $_SESSION['usuario'] ?? null;
if ($usuario === null) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gestão Imobiliária</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <!-- Barra superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-xl">
            <span class="navbar-brand fw-semibold">Gestão Imobiliária</span>
            <div class="d-flex align-items-center gap-3 text-light small">
                <span>Corretor: <strong><?= htmlspecialchars($usuario['nome']) ?></strong></span>
                <button id="btnLogout" class="btn btn-outline-light btn-sm">Sair</button>
            </div>
        </div>
    </nav>

    <div class="container-xl pb-5">
        <div id="areaAvisos"></div>

        <!-- Cartões de resumo -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="text-muted small">Total de Imóveis</div>
                    <div class="stat-value" id="statTotalImoveis">0</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="text-muted small">Imóveis Disponíveis</div>
                    <div class="stat-value text-success" id="statDisponiveis">0</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="text-muted small">Clientes Cadastrados</div>
                    <div class="stat-value text-primary" id="statTotalClientes">0</div>
                </div>
            </div>
        </div>

        <!-- Abas de navegação -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#abaImoveis" type="button">
                    Imóveis
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#abaClientes" type="button">
                    Clientes
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Aba Imóveis -->
            <div class="tab-pane fade show active" id="abaImoveis">
                <div class="card table-card p-3">
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-md-5">
                            <input type="text" id="filtroBuscaImovel" class="form-control form-control-sm" placeholder="Buscar por título, bairro, cidade ou rua...">
                        </div>
                        <div class="col-md-3">
                            <select id="filtroStatusImovel" class="form-select form-select-sm">
                                <option value="">Todos os status</option>
                                <option value="Disponível">Disponível</option>
                                <option value="Alugado">Alugado</option>
                                <option value="Vendido">Vendido</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button id="btnNovoImovel" class="btn btn-primary btn-sm">+ Novo Imóvel</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 small" id="tabelaImoveis">
                            <thead class="table-light">
                                <tr>
                                    <th>Imóvel</th>
                                    <th>Finalidade</th>
                                    <th>Valor</th>
                                    <th>Localização</th>
                                    <th>Proprietário</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Aba Clientes -->
            <div class="tab-pane fade" id="abaClientes">
                <div class="card table-card p-3">
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-md-6">
                            <input type="text" id="filtroBuscaCliente" class="form-control form-control-sm" placeholder="Buscar por nome, telefone, e-mail ou cidade...">
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button id="btnNovoCliente" class="btn btn-primary btn-sm">+ Novo Cliente</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelaClientes">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>E-mail</th>
                                    <th>Cidade/UF</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Imóvel -->
    <div class="modal fade" id="modalImovel" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formImovel">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalImovel">Novo Imóvel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="imovelId">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Título</label>
                            <input type="text" name="titulo" id="imovelTitulo" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tipo</label>
                            <select name="tipo" id="imovelTipo" class="form-select" required>
                                <option value="Apartamento">Apartamento</option>
                                <option value="Casa">Casa</option>
                                <option value="Comercial">Comercial</option>
                                <option value="Terreno">Terreno</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Finalidade</label>
                            <select name="finalidade" id="imovelFinalidade" class="form-select" required>
                                <option value="Venda">Venda</option>
                                <option value="Aluguel">Aluguel</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Valor (R$)</label>
                            <input type="number" step="0.01" name="valor" id="imovelValor" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Área (m²)</label>
                            <input type="number" step="0.1" name="area_m2" id="imovelArea" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" id="imovelStatus" class="form-select" required>
                                <option value="Disponível">Disponível</option>
                                <option value="Alugado">Alugado</option>
                                <option value="Vendido">Vendido</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Proprietário (Cliente)</label>
                            <select name="cliente_id" id="imovelClienteId" class="form-select">
                                <option value="">Sem vínculo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">CEP (API ViaCEP)</label>
                            <div class="input-group">
                                <input type="text" name="cep" id="imovelCep" class="form-control" placeholder="Ex.: 30112-010" required>
                                <button type="button" id="btnBuscarCepImovel" class="btn btn-outline-secondary">Buscar CEP</button>
                            </div>
                            <small class="text-muted" id="statusCepImovel"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Logradouro</label>
                            <input type="text" name="logradouro" id="imovelLogradouro" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bairro</label>
                            <input type="text" name="bairro" id="imovelBairro" class="form-control" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small fw-semibold">Cidade</label>
                            <input type="text" name="cidade" id="imovelCidade" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">UF</label>
                            <input type="text" name="uf" id="imovelUf" maxlength="2" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formCliente">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalCliente">Novo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="clienteId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nome</label>
                            <input type="text" name="nome" id="clienteNome" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Telefone</label>
                            <input type="text" name="telefone" id="clienteTelefone" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">E-mail</label>
                            <input type="email" name="email" id="clienteEmail" class="form-control">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">CEP (API ViaCEP)</label>
                            <div class="input-group">
                                <input type="text" name="cep" id="clienteCep" class="form-control" placeholder="Ex.: 30140-071">
                                <button type="button" id="btnBuscarCepCliente" class="btn btn-outline-secondary">Buscar CEP</button>
                            </div>
                            <small class="text-muted" id="statusCepCliente"></small>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Logradouro</label>
                            <input type="text" name="logradouro" id="clienteLogradouro" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Bairro</label>
                            <input type="text" name="bairro" id="clienteBairro" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Cidade</label>
                            <input type="text" name="cidade" id="clienteCidade" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">UF</label>
                            <input type="text" name="uf" id="clienteUf" maxlength="2" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Mapa -->
    <div class="modal fade" id="modalMapa" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="tituloModalMapa">Localização</h5>
                        <small class="text-muted" id="enderecoModalMapa"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="iframeMapa" class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="painel.js"></script>
</body>
</html>
