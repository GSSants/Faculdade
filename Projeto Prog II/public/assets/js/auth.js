// Autenticação via AJAX

$(function () {
    // Alternar abas login / cadastro
    $('#btnMostrarCadastro').on('click', function () {
        $('#formLogin').addClass('d-none');
        $('#formCadastro').removeClass('d-none');
        $('#alertaAuth').addClass('d-none');
    });

    $('#btnMostrarLogin').on('click', function () {
        $('#formCadastro').addClass('d-none');
        $('#formLogin').removeClass('d-none');
        $('#alertaAuth').addClass('d-none');
    });

    function mostrarAlerta(mensagem, tipo = 'danger') {
        $('#alertaAuth')
            .removeClass('d-none alert-danger alert-success')
            .addClass('alert-' + tipo)
            .text(mensagem);
    }

    // Login
    $('#formLogin').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'api/auth.php',
            method: 'POST',
            dataType: 'json',
            data: {
                acao: 'login',
                email: $('#loginEmail').val(),
                senha: $('#loginSenha').val()
            },
            success: function (res) {
                if (res.sucesso) {
                    window.location.href = 'index.php';
                } else {
                    mostrarAlerta(res.mensagem || 'Erro ao entrar.');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.mensagem : 'Falha na comunicação com o servidor.';
                mostrarAlerta(msg);
            }
        });
    });

    // Cadastro de usuário
    $('#formCadastro').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'api/auth.php',
            method: 'POST',
            dataType: 'json',
            data: {
                acao: 'registrar',
                nome: $('#cadNome').val(),
                email: $('#cadEmail').val(),
                senha: $('#cadSenha').val()
            },
            success: function (res) {
                if (res.sucesso) {
                    window.location.href = 'index.php';
                } else {
                    mostrarAlerta(res.mensagem || 'Erro ao cadastrar.');
                }
            },
            error: function () {
                mostrarAlerta('Falha ao cadastrar usuário.');
            }
        });
    });
});
