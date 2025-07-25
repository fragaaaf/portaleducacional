<?php
// gerador_de_provas.php (Agora atuando como um Controlador mais limpo)

// Inclui os arquivos essenciais para este controlador
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/utils.php'; // Contém validar_dados_server_side, carregar_json, sanitizar_dados
require_once __DIR__ . '/modules/prova_generation/generator.php'; // Contém processar_geracao_prova

$app_config = require __DIR__ . '/core/config.php'; // Carrega o array de configuração global

// --- Fluxo Principal ---

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Carrega os dados JSON usando os caminhos definidos em core/config.php
    $config_json = carregar_json($app_config['data_files']['config']);
    $turmas_info_completa = carregar_json($app_config['data_files']['turmas']);
    $disciplina_assuntos_info_completa = carregar_json($app_config['data_files']['disciplina_assuntos']);
    $formatos_perguntas_disponiveis = carregar_json($app_config['data_files']['formato_perguntas']);

    // Realiza a validação server-side usando a função de core/utils.php
    $resultado_validacao = validar_dados_server_side(
        $_POST,
        $config_json, // Passa o config_json carregado
        $turmas_info_completa,
        $disciplina_assuntos_info_completa,
        $formatos_perguntas_disponiveis
    );
    // DEBUGAR AS VALIDAÇÕES
    echo "<pre>"; print_r($disciplina_assuntos_info_completa);echo "</pre>";
    echo "<pre>"; print_r($resultado_validacao);echo "</pre>";
    // Inicia a saída HTML para o resultado
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado da Geração da Prova</title>
        <link rel="stylesheet" href="public/css/style.css"> </head>
    <body>
        <div class="container">
            <?php
            if ($resultado_validacao['sucesso']) {
                // Chama a função principal de geração da prova e ecoa o HTML retornado
                echo processar_geracao_prova(
                    $resultado_validacao['dados_validados'],
                    $turmas_info_completa,
                    $disciplina_assuntos_info_completa,
                    $config_json // Agora passamos o config_json carregado
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
    // Caso a página seja acessada diretamente sem POST (mantido como estava)
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acesso Inválido</title>
        <link rel="stylesheet" href="public/css/style.css"> </head>
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