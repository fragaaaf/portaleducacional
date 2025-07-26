<?php
// gerador_de_provas.php

// Inclui as funções auxiliares
require_once 'funcoes.php';

// --- Fluxo Principal ---

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Carrega os dados de configuração e opções para validação
    $config = carregar_json('config.json');
    $turmas_info_completa = carregar_json('turmas.json'); // Usado para validar estudantes
    $disciplina_assuntos_info_completa = carregar_json('disciplina_assuntos.json'); // Usado para validar disciplinas e assuntos
    $formatos_perguntas_disponiveis = carregar_json('formato_perguntas.json');


    // Realiza a validação server-side
    $resultado_validacao = validar_dados_server_side(
        $_POST,
        $config,
        $turmas_info_completa,
        $disciplina_assuntos_info_completa,
        $formatos_perguntas_disponiveis
    );

    // Inicia a saída HTML para o resultado
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado da Geração da Prova</title>
        <link rel="stylesheet" href="public/css/style.css">
    </head>
    <body>
        <div class="container">
            <?php
            if ($resultado_validacao['sucesso']) {
                echo processar_geracao_prova(
                    $resultado_validacao['dados_validados'],
                    $turmas_info_completa,
                    $disciplina_assuntos_info_completa,
                    $config // Passa o array de configuração completo
                );
            } else {
                echo '<h1>Erro na Geração da Prova</h1>';
                echo '<ul class="error-list">';
                foreach ($resultado_validacao['mensagens_erro'] as $erro) {
                    echo '<li>' . htmlspecialchars($erro) . '</li>';
                }
                echo '</ul>';
                echo '<p>Por favor, volte e corrija os erros no formulário.</p>';
            }
            ?>
            <a href="index.php" class="back-link">Voltar ao Formulário</a>
        </div>
    </body>
    </html>
    <?php
} else {
    // Caso a página seja acessada diretamente sem POST
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acesso Inválido</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <h1>Acesso Inválido</h1>
            <p class="error-list">Você tentou acessar esta página diretamente. Por favor, utilize o formulário para gerar a prova.</p>
            <a href="index.php" class="back-link">Ir para o Formulário</a>
        </div>
    </body>
    </html>
    <?php
}
?>