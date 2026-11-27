$(function () {
    const modalImovel = new bootstrap.Modal(document.getElementById('modalImovel'));
    const modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));
    const modalMapa = new bootstrap.Modal(document.getElementById('modalMapa'));
    let pedidoImoveis;
    let pedidoClientes;

    function mostrarAviso(mensagem, tipo = 'success') {
        const alerta = $('<div class="alert alert-dismissible py-2" role="alert"></div>');
        alerta.addClass('alert-' + tipo).text(mensagem);
        alerta.append('<button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Fechar"></button>');
        $('#areaAvisos').empty().append(alerta);
    }

    function mostrarErro(xhr, estado) {
        if (estado === 'abort') return;

        if (xhr.status === 401) {
            window.location.href = 'login.php';
        } else {
            const resposta = xhr.responseJSON;
            const mensagem = resposta ? resposta.mensagem : 'Não foi possível acessar o servidor.';
            mostrarAviso(mensagem, 'danger');
        }
    }

    function escaparTexto(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function formatarMoeda(valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function etiquetaStatus(status) {
        if (status === 'Disponível' || status === 'Disponivel') {
            return '<span class="badge bg-success">Disponível</span>';
        }
        if (status === 'Alugado') {
            return '<span class="badge bg-warning text-dark">Alugado</span>';
        }
        return '<span class="badge bg-secondary">Vendido</span>';
    }

    function buscarCep(prefixo, statusSelector) {
        const cep = $('#' + prefixo + 'Cep').val().replace(/\D/g, '');
        const status = $(statusSelector);

        if (cep.length !== 8) {
            status.text('Informe um CEP com 8 dígitos.');
            return;
        }

        status.text('Consultando ViaCEP...');
        $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function (endereco) {
            if (endereco.erro) {
                status.text('CEP não encontrado.');
                return;
            }

            $('#' + prefixo + 'Logradouro').val(endereco.logradouro || '');
            $('#' + prefixo + 'Bairro').val(endereco.bairro || '');
            $('#' + prefixo + 'Cidade').val(endereco.localidade || '');
            $('#' + prefixo + 'Uf').val(endereco.uf || '');
            status.text('Endereço preenchido via ViaCEP.');
        }).fail(function () {
            status.text('Erro ao consultar o ViaCEP.');
        });
    }

    $('#btnBuscarCepImovel').on('click', function () {
        buscarCep('imovel', '#statusCepImovel');
    });

    $('#btnBuscarCepCliente').on('click', function () {
        buscarCep('cliente', '#statusCepCliente');
    });

    function carregarImoveis() {
        if (pedidoImoveis) pedidoImoveis.abort();

        pedidoImoveis = $.getJSON('imoveis.php', {
            busca: $('#filtroBuscaImovel').val(),
            status: $('#filtroStatusImovel').val()
        }, function (resposta) {
            $('#statTotalImoveis').text(resposta.resumo.total_imoveis);
            $('#statDisponiveis').text(resposta.resumo.disponiveis);
            $('#statTotalClientes').text(resposta.resumo.total_clientes);

            const tabela = $('#tabelaImoveis tbody').empty();
            if (resposta.dados.length === 0) {
                tabela.append('<tr><td colspan="7" class="text-center text-muted py-4">Nenhum imóvel encontrado.</td></tr>');
                return;
            }

            for (const imovel of resposta.dados) {
                const local = imovel.bairro + ' - ' + imovel.cidade + '/' + imovel.uf;
                const endereco = imovel.logradouro + ', ' + local;
                const id = Number(imovel.id);

                tabela.append(`
                    <tr>
                        <td>
                            <div class="fw-semibold">${escaparTexto(imovel.titulo)}</div>
                            <small class="text-muted">${escaparTexto(imovel.tipo)} | ${Number(imovel.area_m2)} m²</small>
                        </td>
                        <td>${escaparTexto(imovel.finalidade)}</td>
                        <td class="fw-semibold">${formatarMoeda(imovel.valor)}</td>
                        <td>
                            <div>${escaparTexto(local)}</div>
                            <small class="text-muted">CEP ${escaparTexto(imovel.cep)}</small>
                        </td>
                        <td>${escaparTexto(imovel.cliente_nome || '-')}</td>
                        <td>${etiquetaStatus(imovel.status)}</td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-outline-info btn-acao btn-mapa"
                                data-titulo="${escaparTexto(imovel.titulo)}"
                                data-endereco="${escaparTexto(endereco)}">Mapa</button>
                            <button class="btn btn-outline-primary btn-acao btn-editar-imovel" data-id="${id}">Editar</button>
                            <button class="btn btn-outline-danger btn-acao btn-excluir-imovel" data-id="${id}">Excluir</button>
                        </td>
                    </tr>`);
            }
        }).fail(mostrarErro);
    }

    function carregarClientes() {
        if (pedidoClientes) pedidoClientes.abort();

        pedidoClientes = $.getJSON('clientes.php', { busca: $('#filtroBuscaCliente').val() }, function (resposta) {
            const tabela = $('#tabelaClientes tbody').empty();
            if (resposta.dados.length === 0) {
                tabela.append('<tr><td colspan="5" class="text-center text-muted py-4">Nenhum cliente encontrado.</td></tr>');
                return;
            }

            for (const cliente of resposta.dados) {
                const local = cliente.cidade ? cliente.cidade + '/' + cliente.uf : '-';
                const id = Number(cliente.id);

                tabela.append(`
                    <tr>
                        <td class="fw-semibold">${escaparTexto(cliente.nome)}</td>
                        <td>${escaparTexto(cliente.telefone)}</td>
                        <td>${escaparTexto(cliente.email || '-')}</td>
                        <td>${escaparTexto(local)}</td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-outline-primary btn-acao btn-editar-cliente" data-id="${id}">Editar</button>
                            <button class="btn btn-outline-danger btn-acao btn-excluir-cliente" data-id="${id}">Excluir</button>
                        </td>
                    </tr>`);
            }
        }).fail(mostrarErro);
    }

    // O formulário sempre lista todos os proprietários, mesmo com a tabela de clientes filtrada.
    function abrirFormularioImovel(clienteId) {
        $.getJSON('clientes.php', function (resposta) {
            const campo = $('#imovelClienteId').empty();
            campo.append('<option value="">Sem vínculo</option>');

            for (const cliente of resposta.dados) {
                campo.append($('<option></option>').val(cliente.id).text(cliente.nome));
            }

            campo.val(clienteId);
            $('#statusCepImovel').text('');
            modalImovel.show();
        }).fail(mostrarErro);
    }

    $('#filtroBuscaImovel').on('input', carregarImoveis);
    $('#filtroStatusImovel').on('change', carregarImoveis);
    $('#filtroBuscaCliente').on('input', carregarClientes);

    // Imóveis
    $('#btnNovoImovel').on('click', function () {
        $('#formImovel')[0].reset();
        $('#imovelId').val('');
        $('#tituloModalImovel').text('Novo Imóvel');
        abrirFormularioImovel('');
    });

    $('#tabelaImoveis').on('click', '.btn-editar-imovel', function () {
        $.getJSON('imoveis.php', { acao: 'buscar', id: $(this).data('id') }, function (resposta) {
            if (!resposta.sucesso) {
                mostrarAviso('Imóvel não encontrado.', 'danger');
                return;
            }

            const imovel = resposta.dados;
            $('#imovelId').val(imovel.id);
            $('#imovelTitulo').val(imovel.titulo);
            $('#imovelTipo').val(imovel.tipo);
            $('#imovelFinalidade').val(imovel.finalidade);
            $('#imovelValor').val(imovel.valor);
            $('#imovelArea').val(imovel.area_m2);
            $('#imovelStatus').val(imovel.status === 'Disponivel' ? 'Disponível' : imovel.status);
            $('#imovelCep').val(imovel.cep);
            $('#imovelLogradouro').val(imovel.logradouro);
            $('#imovelBairro').val(imovel.bairro);
            $('#imovelCidade').val(imovel.cidade);
            $('#imovelUf').val(imovel.uf);
            $('#tituloModalImovel').text('Editar Imóvel');
            abrirFormularioImovel(imovel.cliente_id || '');
        }).fail(mostrarErro);
    });

    $('#formImovel').on('submit', function (evento) {
        evento.preventDefault();
        const dados = $(this).serialize() + '&acao=salvar';
        $.post('imoveis.php', dados, function (resposta) {
            if (resposta.sucesso) {
                modalImovel.hide();
                mostrarAviso(resposta.mensagem);
                carregarImoveis();
            } else {
                mostrarAviso(resposta.mensagem, 'danger');
            }
        }, 'json').fail(mostrarErro);
    });

    $('#tabelaImoveis').on('click', '.btn-excluir-imovel', function () {
        if (!confirm('Deseja excluir este imóvel?')) return;

        $.post('imoveis.php', { acao: 'excluir', id: $(this).data('id') }, function (resposta) {
            mostrarAviso(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');
            if (resposta.sucesso) carregarImoveis();
        }, 'json').fail(mostrarErro);
    });

    $('#tabelaImoveis').on('click', '.btn-mapa', function () {
        const endereco = $(this).attr('data-endereco');
        $('#tituloModalMapa').text($(this).attr('data-titulo'));
        $('#enderecoModalMapa').text(endereco);
        $('#iframeMapa').attr('src', 'https://maps.google.com/maps?q=' + encodeURIComponent(endereco) + '&z=15&output=embed');
        modalMapa.show();
    });

    // Clientes
    $('#btnNovoCliente').on('click', function () {
        $('#formCliente')[0].reset();
        $('#clienteId').val('');
        $('#statusCepCliente').text('');
        $('#tituloModalCliente').text('Novo Cliente');
        modalCliente.show();
    });

    $('#tabelaClientes').on('click', '.btn-editar-cliente', function () {
        $.getJSON('clientes.php', { acao: 'buscar', id: $(this).data('id') }, function (resposta) {
            if (!resposta.sucesso) {
                mostrarAviso('Cliente não encontrado.', 'danger');
                return;
            }

            const cliente = resposta.dados;
            $('#clienteId').val(cliente.id);
            $('#clienteNome').val(cliente.nome);
            $('#clienteTelefone').val(cliente.telefone);
            $('#clienteEmail').val(cliente.email);
            $('#clienteCep').val(cliente.cep);
            $('#clienteLogradouro').val(cliente.logradouro);
            $('#clienteBairro').val(cliente.bairro);
            $('#clienteCidade').val(cliente.cidade);
            $('#clienteUf').val(cliente.uf);
            $('#statusCepCliente').text('');
            $('#tituloModalCliente').text('Editar Cliente');
            modalCliente.show();
        }).fail(mostrarErro);
    });

    $('#formCliente').on('submit', function (evento) {
        evento.preventDefault();
        const dados = $(this).serialize() + '&acao=salvar';
        $.post('clientes.php', dados, function (resposta) {
            if (resposta.sucesso) {
                modalCliente.hide();
                mostrarAviso(resposta.mensagem);
                carregarClientes();
                carregarImoveis();
            } else {
                mostrarAviso(resposta.mensagem, 'danger');
            }
        }, 'json').fail(mostrarErro);
    });

    $('#tabelaClientes').on('click', '.btn-excluir-cliente', function () {
        if (!confirm('Deseja excluir este cliente?')) return;

        $.post('clientes.php', { acao: 'excluir', id: $(this).data('id') }, function (resposta) {
            mostrarAviso(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');
            if (resposta.sucesso) {
                carregarClientes();
                carregarImoveis();
            }
        }, 'json').fail(mostrarErro);
    });

    $('#btnLogout').on('click', function () {
        $.post('auth.php', { acao: 'logout' }, function () {
            window.location.href = 'login.php';
        }, 'json').fail(mostrarErro);
    });

    carregarImoveis();
    carregarClientes();
});
