// Aplicação principal - CRUD AJAX, ViaCEP e Mapa

$(function () {
    const modalImovel = new bootstrap.Modal(document.getElementById('modalImovel'));
    const modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));
    const modalMapa = new bootstrap.Modal(document.getElementById('modalMapa'));

    // Formatação auxiliar
    function formatarMoeda(valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function badgeStatus(status) {
        if (status === 'Disponível' || status === 'Disponivel') return '<span class="badge bg-success">Disponível</span>';
        if (status === 'Alugado') return '<span class="badge bg-warning text-dark">Alugado</span>';
        return '<span class="badge bg-secondary">Vendido</span>';
    }

    function mostrarAviso(mensagem, tipo = 'success') {
        const html = `
            <div class="alert alert-${tipo} alert-dismissible fade show py-2" role="alert">
                ${mensagem}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>`;
        $('#areaAvisos').html(html);
    }

    // Consulta API ViaCEP
    function buscarCep(cepBruto, prefixo, statusSelector) {
        const cep = cepBruto.replace(/\D/g, '');
        if (cep.length !== 8) {
            $(statusSelector).text('Informe um CEP com 8 dígitos.');
            return;
        }

        $(statusSelector).text('Consultando ViaCEP...');
        $.ajax({
            url: `https://viacep.com.br/ws/${cep}/json/`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.erro) {
                    $(statusSelector).text('CEP não encontrado.');
                    return;
                }
                $(`#${prefixo}Logradouro`).val(data.logradouro || '');
                $(`#${prefixo}Bairro`).val(data.bairro || '');
                $(`#${prefixo}Cidade`).val(data.localidade || '');
                $(`#${prefixo}Uf`).val(data.uf || '');
                $(statusSelector).text('Endereço preenchido via ViaCEP.');
            },
            error: function () {
                $(statusSelector).text('Erro ao consultar a API ViaCEP.');
            }
        });
    }

    $('#btnBuscarCepImovel').on('click', function () {
        buscarCep($('#imovelCep').val(), 'imovel', '#statusCepImovel');
    });

    $('#btnBuscarCepCliente').on('click', function () {
        buscarCep($('#clienteCep').val(), 'cliente', '#statusCepCliente');
    });

    // Listar imóveis
    function carregarImoveis() {
        $.ajax({
            url: 'api/imoveis.php',
            method: 'GET',
            dataType: 'json',
            data: {
                acao: 'listar',
                busca: $('#filtroBuscaImovel').val(),
                status: $('#filtroStatusImovel').val()
            },
            success: function (res) {
                if (!res.sucesso) return;

                if (res.resumo) {
                    $('#statTotalImoveis').text(res.resumo.total_imoveis);
                    $('#statDisponiveis').text(res.resumo.disponiveis);
                    $('#statTotalClientes').text(res.resumo.total_clientes);
                }

                const tbody = $('#tabelaImoveis tbody');
                tbody.empty();

                if (res.dados.length === 0) {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted py-4">Nenhum imóvel encontrado.</td></tr>');
                    return;
                }

                res.dados.forEach(function (item) {
                    const endereco = `${item.logradouro}, ${item.bairro} - ${item.cidade}/${item.uf}`;
                    const linha = `
                        <tr>
                            <td>
                                <div class="fw-semibold">${item.titulo}</div>
                                <small class="text-muted">${item.tipo} | ${item.area_m2} m²</small>
                            </td>
                            <td>${item.finalidade}</td>
                            <td class="fw-semibold">${formatarMoeda(item.valor)}</td>
                            <td>
                                <div>${item.bairro} - ${item.cidade}/${item.uf}</div>
                                <small class="text-muted">CEP ${item.cep}</small>
                            </td>
                            <td>${item.cliente_nome || '<span class="text-muted">-</span>'}</td>
                            <td>${badgeStatus(item.status)}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-outline-info btn-acao btn-mapa" data-titulo="${item.titulo}" data-endereco="${endereco}">Mapa</button>
                                <button class="btn btn-outline-primary btn-acao btn-editar-imovel" data-id="${item.id}">Editar</button>
                                <button class="btn btn-outline-danger btn-acao btn-excluir-imovel" data-id="${item.id}">Excluir</button>
                            </td>
                        </tr>`;
                    tbody.append(linha);
                });
            }
        });
    }

    // Listar clientes e preencher select do modal de imóvel
    function carregarClientes() {
        $.ajax({
            url: 'api/clientes.php',
            method: 'GET',
            dataType: 'json',
            data: {
                acao: 'listar',
                busca: $('#filtroBuscaCliente').val()
            },
            success: function (res) {
                if (!res.sucesso) return;

                const tbody = $('#tabelaClientes tbody');
                tbody.empty();

                if (res.dados.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">Nenhum cliente encontrado.</td></tr>');
                } else {
                    res.dados.forEach(function (c) {
                        const local = c.cidade ? `${c.cidade}/${c.uf}` : '-';
                        tbody.append(`
                            <tr>
                                <td class="fw-semibold">${c.nome}</td>
                                <td>${c.telefone}</td>
                                <td>${c.email || '-'}</td>
                                <td>${local}</td>
                                <td class="text-end text-nowrap">
                                    <button class="btn btn-outline-primary btn-acao btn-editar-cliente" data-id="${c.id}">Editar</button>
                                    <button class="btn btn-outline-danger btn-acao btn-excluir-cliente" data-id="${c.id}">Excluir</button>
                                </td>
                            </tr>`);
                    });
                }

                // Atualizar select de proprietários
                const select = $('#imovelClienteId');
                const valorAtual = select.val();
                select.empty().append('<option value="">Sem vínculo</option>');
                res.dados.forEach(function (c) {
                    select.append(`<option value="${c.id}">${c.nome}</option>`);
                });
                if (valorAtual) select.val(valorAtual);
            }
        });
    }

    // Filtros
    $('#filtroBuscaImovel').on('input', carregarImoveis);
    $('#filtroStatusImovel').on('change', carregarImoveis);
    $('#filtroBuscaCliente').on('input', carregarClientes);

    // Novo imóvel
    $('#btnNovoImovel').on('click', function () {
        $('#formImovel')[0].reset();
        $('#imovelId').val('');
        $('#statusCepImovel').text('');
        $('#tituloModalImovel').text('Novo Imóvel');
        modalImovel.show();
    });

    // Salvar imóvel
    $('#formImovel').on('submit', function (e) {
        e.preventDefault();
        const dados = $(this).serialize() + '&acao=salvar';
        $.ajax({
            url: 'api/imoveis.php',
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function (res) {
                if (res.sucesso) {
                    modalImovel.hide();
                    mostrarAviso(res.mensagem);
                    carregarImoveis();
                } else {
                    mostrarAviso(res.mensagem || 'Erro ao salvar imóvel.', 'danger');
                }
            }
        });
    });

    // Editar imóvel
    $(document).on('click', '.btn-editar-imovel', function () {
        const id = $(this).data('id');
        $.getJSON('api/imoveis.php', { acao: 'buscar', id: id }, function (res) {
            if (!res.sucesso || !res.dados) return;
            const d = res.dados;
            $('#imovelId').val(d.id);
            $('#imovelTitulo').val(d.titulo);
            $('#imovelTipo').val(d.tipo);
            $('#imovelFinalidade').val(d.finalidade);
            $('#imovelValor').val(d.valor);
            $('#imovelArea').val(d.area_m2);
            $('#imovelStatus').val(d.status);
            $('#imovelClienteId').val(d.cliente_id || '');
            $('#imovelCep').val(d.cep);
            $('#imovelLogradouro').val(d.logradouro);
            $('#imovelBairro').val(d.bairro);
            $('#imovelCidade').val(d.cidade);
            $('#imovelUf').val(d.uf);
            $('#statusCepImovel').text('');
            $('#tituloModalImovel').text('Editar Imóvel');
            modalImovel.show();
        });
    });

    // Excluir imóvel
    $(document).on('click', '.btn-excluir-imovel', function () {
        const id = $(this).data('id');
        if (!confirm('Deseja excluir este imóvel?')) return;
        $.post('api/imoveis.php', { acao: 'excluir', id: id }, function (res) {
            if (res.sucesso) {
                mostrarAviso(res.mensagem);
                carregarImoveis();
            }
        }, 'json');
    });

    // Exibir mapa externo (Google Maps Embed)
    $(document).on('click', '.btn-mapa', function () {
        const titulo = $(this).data('titulo');
        const endereco = $(this).data('endereco');
        const query = encodeURIComponent(endereco);
        $('#tituloModalMapa').text(titulo);
        $('#enderecoModalMapa').text(endereco);
        $('#iframeMapa').attr('src', `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`);
        modalMapa.show();
    });

    // Novo cliente
    $('#btnNovoCliente').on('click', function () {
        $('#formCliente')[0].reset();
        $('#clienteId').val('');
        $('#statusCepCliente').text('');
        $('#tituloModalCliente').text('Novo Cliente');
        modalCliente.show();
    });

    // Salvar cliente
    $('#formCliente').on('submit', function (e) {
        e.preventDefault();
        const dados = $(this).serialize() + '&acao=salvar';
        $.ajax({
            url: 'api/clientes.php',
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function (res) {
                if (res.sucesso) {
                    modalCliente.hide();
                    mostrarAviso(res.mensagem);
                    carregarClientes();
                    carregarImoveis();
                } else {
                    mostrarAviso(res.mensagem || 'Erro ao salvar cliente.', 'danger');
                }
            }
        });
    });

    // Editar cliente
    $(document).on('click', '.btn-editar-cliente', function () {
        const id = $(this).data('id');
        $.getJSON('api/clientes.php', { acao: 'buscar', id: id }, function (res) {
            if (!res.sucesso || !res.dados) return;
            const c = res.dados;
            $('#clienteId').val(c.id);
            $('#clienteNome').val(c.nome);
            $('#clienteTelefone').val(c.telefone);
            $('#clienteEmail').val(c.email);
            $('#clienteCep').val(c.cep);
            $('#clienteLogradouro').val(c.logradouro);
            $('#clienteBairro').val(c.bairro);
            $('#clienteCidade').val(c.cidade);
            $('#clienteUf').val(c.uf);
            $('#statusCepCliente').text('');
            $('#tituloModalCliente').text('Editar Cliente');
            modalCliente.show();
        });
    });

    // Excluir cliente
    $(document).on('click', '.btn-excluir-cliente', function () {
        const id = $(this).data('id');
        if (!confirm('Deseja excluir este cliente?')) return;
        $.post('api/clientes.php', { acao: 'excluir', id: id }, function (res) {
            if (res.sucesso) {
                mostrarAviso(res.mensagem);
                carregarClientes();
                carregarImoveis();
            }
        }, 'json');
    });

    // Logout
    $('#btnLogout').on('click', function () {
        $.post('api/auth.php', { acao: 'logout' }, function () {
            window.location.href = 'login.php';
        }, 'json');
    });

    // Carga inicial
    carregarClientes();
    carregarImoveis();
});
