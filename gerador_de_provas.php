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
   function ex($msg, $vetor){
    echo "<pre>";
    if(is_array($vetor)){
        echo $msg;
        print_r($vetor);
    }else{
        echo "$msg, $vetor";
    }
    echo "</pre>";
   }

   ex("POST[assuntos]: ", $_POST['assuntos']);
   echo "===========";
   ex("disciplina_assuntos[completa]: ", $disciplina_assuntos_info_completa);
    // Realiza a validação server-side usando a função de core/utils.php
    $resultado_validacao = validar_dados_server_side(
        $_POST,
        $config_json, // Passa o config_json carregado
        $turmas_info_completa,
        $disciplina_assuntos_info_completa,
        $formatos_perguntas_disponiveis
    );
    //Mostrar ou não o debug
        if(true){
        // Debug visível no navegador
            echo '<div style="background:#f0f0f0; padding:20px; margin:20px; border:2px solid red; font-family:monospace;">';
            echo '<h3 style="color:red;">DEBUG - Dados Recebidos</h3>';

            // 1. Dados brutos do POST
            //echo '<h4>RAW POST DATA:</h4>';
            //echo '<pre>' . htmlspecialchars(file_get_contents('php://input')) . '</pre>';

            // 2. Array POST completo
             echo '<h4>1. Validadção $resultado_validacao:</h4>';
            echo '<pre>' . print_r($resultado_validacao, true) . '</pre>';
            echo '<h4>2. Array POST completo: $_POST:</h4>';
            echo '<pre>' . print_r($_POST, true) . '</pre>';
            echo '<h4>-----------------------------</h4>';
            echo '<h4>DISCIPLINA ASSUNTOS </h4>';
            $disciplina_assuntos_info  =$_POST['assuntos'];
            echo '<pre>Assuntos extraidos' . print_r($disciplina_assuntos_info, true) . '</pre>';
            // 3. Dados das disciplinas e assuntos
            echo '<h4>Disciplina e Assuntos:</h4>';
            if (!empty($_POST['disciplina'])) {
                $disciplina = $_POST['disciplina'];
                echo '<p>Disciplina selecionada: <strong>' . htmlspecialchars($disciplina) . '</strong></p>';
                
                $assuntosValidos = $disciplina_assuntos_info ?? [];//['assuntos'] ?? [];
                echo '<p>Assuntos válidos:</p>';
                echo '<pre>' . print_r($assuntosValidos, true) . '</pre>';
                
                echo '<p>Assuntos enviados:</p>';
                echo '<pre>' . print_r($_POST['assuntos'] ?? [], true) . '</pre>';
            } else {
                echo '<p style="color:red;">Nenhuma disciplina foi selecionada</p>';
            }

            // 4. Formatos de perguntas
            echo '<h4>Formatos de Pergunta:</h4>';
            if (!empty($_POST['formatosPerguntas'])) {
                echo '<p>Formatos selecionados:</p>';
                echo '<pre>' . print_r($_POST['formatosPerguntas'], true) . '</pre>';
                
                echo '<p>Formatos disponíveis:</p>';
                echo '<pre>' . print_r($formatos_perguntas_disponiveis, true) . '</pre>';
            } else {
                echo '<p style="color:red;">Nenhum formato de pergunta foi selecionado</p>';
            }

            echo '</div>'; // Fecha a div de debug
        }
            //-----------------------------------------
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