$(function () {
    $('#btnMostrarCadastro').on('click', function () {
        $('#formLogin, #alertaAuth').addClass('d-none');
        $('#formCadastro').removeClass('d-none');
    });

    $('#btnMostrarLogin').on('click', function () {
        $('#formCadastro, #alertaAuth').addClass('d-none');
        $('#formLogin').removeClass('d-none');
    });

    function mostrarAlerta(mensagem) {
        $('#alertaAuth').removeClass('d-none').addClass('alert-danger').text(mensagem);
    }

    function enviarFormulario(dados) {
        $.post('auth.php', dados, function (resposta) {
            if (resposta.sucesso) {
                window.location.href = 'index.php';
            } else {
                mostrarAlerta(resposta.mensagem);
            }
        }, 'json').fail(function (xhr) {
            const resposta = xhr.responseJSON;
            mostrarAlerta(resposta ? resposta.mensagem : 'Não foi possível acessar o servidor.');
        });
    }

    $('#formLogin').on('submit', function (evento) {
        evento.preventDefault();
        enviarFormulario({
            acao: 'login',
            email: $('#loginEmail').val(),
            senha: $('#loginSenha').val()
        });
    });

    $('#formCadastro').on('submit', function (evento) {
        evento.preventDefault();
        enviarFormulario({
            acao: 'registrar',
            nome: $('#cadNome').val(),
            email: $('#cadEmail').val(),
            senha: $('#cadSenha').val()
        });
    });
});
