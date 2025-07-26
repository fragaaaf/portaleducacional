<?php
// index.php

// Inclui as funções utilitárias e o arquivo de configuração global
require_once __DIR__ . '/core/utils.php'; // Caminho ajustado
$app_config = require __DIR__ . '/core/config.php';

// Carrega os dados de configuração e opções para o formulário

$config_json = carregar_json($app_config['data_files']['config']);
$turmas_info_completa = carregar_json($app_config['data_files']['turmas']);

$disciplina_assuntos_info_completa = carregar_json($app_config['data_files']['disciplina_assuntos']);
$formatos_perguntas_disponiveis = carregar_json($app_config['data_files']['formato_perguntas']);

$formulario_campos = $config_json['formulario_campos'] ?? [];
$configs_gerais = $config_json['configs_gerais'] ?? [];
///echo "<pre>"; 
///print_r($turmas_info_completa);
///echo "</pre>"; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Provas - Configurações</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Configurações da Prova</h1>
        <form id="quizForm" action="gerador_de_provas.php" method="POST">
            <?php
            // Itera sobre a definição dos campos no config.json para gerar o HTML
            //<!-- ============ FORMULÁRIO =============== -->
            foreach ($formulario_campos as $campo) {
                $id = htmlspecialchars($campo['id']);
                $label = htmlspecialchars($campo['label']);
                $type = htmlspecialchars($campo['type']);
                $placeholder = htmlspecialchars($campo['placeholder'] ?? '');
                $validation_message = htmlspecialchars($campo['validation_message'] ?? '');
                $options = $campo['options'] ?? [];
                ?>
                <div class="form-group">
                    <label for="<?php echo $id; ?>"><?php echo $label; ?>:</label>
                    <?php
                    switch ($type) {
                        case 'text':
                        case 'date':
                        case 'number':
                            ?>
                            <input type="<?php echo $type; ?>" id="<?php echo $id; ?>" name="<?php echo $id; ?>" placeholder="<?php echo $placeholder; ?>" required>
                            <?php
                            break;
                        case 'select': // Este 'case' é mantido, mas 'disciplina' sairá dele.
                            ?>
                            <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" required>
                                <option value="">Selecione...</option>
                                <?php
                                // Se você ainda tiver selects estáticos ou outros que não sejam disciplina
                                foreach ($options as $option_value => $option_label) {
                                    echo '<option value="' . htmlspecialchars($option_value) . '">' . htmlspecialchars($option_label) . '</option>';
                                }
                                ?>
                            </select>
                            <?php
                            break;
                        case 'radio': // Mantido para campos de rádio simples como 'turma'
                            //<!-- ============ TURMA =============== -->
                            if ($id === 'turma') {
                                /*
                                foreach ($turmas_info_completa as $turma_id_opt => $turma_data) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($turma_id_opt) . '" class="turma-radio" ' . (isset($campo['default']) && $campo['default'] === $turma_id_opt ? 'checked' : '') . '> ' . htmlspecialchars($turma_data['nome']);
                                    echo '</label>';
                                 
                                }
                                    */
                            //--------------------------------------------------
                            //------------ TURMAS -------------------------------
                            // Modifique o loop da turma para usar o ID correto:
                            foreach ($turmas_info_completa as $turma) { // $turma já é um objeto, não $turma_id_opt
                                echo "<pre>";
                                //echo $turma['id']."-".$turma['nome']."<br>";print_r($turma['estudantes'][0]['nome_completo']); //(print_r($turmas_info_completa);
                                echo "</pre>";
                                echo '<label>';
                                echo '<input type="radio" name="turma" value="' . htmlspecialchars($turma['id']) . '" class="turma-radio"> ' . htmlspecialchars($turma['nome']);
                                echo '</label>';
                            }        
                            } else {
                                // Se houver outros rádios que não são radio_group e usam 'options' diretamente
                                foreach ($options as $option_value => $option_label) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($option_value) . '" ' . (isset($campo['default']) && $campo['default'] === $option_value ? 'checked' : '') . '> ' . htmlspecialchars($option_label);
                                    echo '</label>';
                                }
                            }
                            break;
                        case 'radio_group':
                            $current_options = [];
                            //<!-- ============ DISCIPLINA =============== -->
                            if ($id === 'disciplina') {
                                // Usa $disciplina_assuntos_info_completa para gerar os radios
                                // Dentro do case 'radio_group' (disciplina):
                                foreach ($disciplina_assuntos_info_completa as $disciplina_data) {
                                    echo '<label>';
                                    echo '<input type="radio" name="disciplina" value="' . htmlspecialchars($disciplina_data['id']) . '"> ' . htmlspecialchars($disciplina_data['nome']);
                                    echo '</label>';
                                }    
                            } else {
                                // Outros radio_groups (como dificuldade, tipoProva)
                                foreach ($campo['options'] as $value => $label) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($value) . '"> ' . htmlspecialchars($label);
                                    echo '</label>';
                                }
                            }
                            break;    
                        case 'checkbox_group_dynamic':
                            //<!-- ============ CHECKBOX SELECIONAR TODOS =============== -->
                            // ucfirst — Transforma o primeiro caractere de uma string em maiúsculo
                            echo '<label class="select-all-label" id="selecionarTodos' . ucfirst($id) . 'Label">';
                            echo '<input type="checkbox" id="selecionarTodos' . ucfirst($id) . '"> ' . htmlspecialchars($campo['select_all_label'] ?? 'Selecionar Todos');
                            echo '</label>';

                            echo '<div id="' . $id . '-list-container" class="dynamic-group-container">';
                            //<!-- ============ ESTUDANTES =============== -->
                            if ($id === 'estudantes') {
                                foreach ($turmas_info_completa as $turma) {
                                    $turma_id = htmlspecialchars($turma['id']);
                                    foreach ($turma['estudantes'] as $estudante) {
                                        $matricula_completa = htmlspecialchars($estudante['matricula']);
                                        $nome_completo = htmlspecialchars($estudante['nome_completo']);
                                        echo '<label data-turma-id="' . $turma_id . '" style="display: none;">';
                                        echo '<input type="checkbox" name="estudantes[]" value="' . $matricula_completa . '" class="estudante-checkbox" data-turma-id="' . $turma_id . '"> ' . $nome_completo;
                                        echo '</label>';
                                    }
                                }
                            } 
                            //<!-- ============ ASSUNTOS =============== -->
                             elseif ($id === 'assuntos') {
                                foreach ($disciplina_assuntos_info_completa as $disciplina_data) {
                                    $disciplina_id = htmlspecialchars($disciplina_data['id']);
                                    if (isset($disciplina_data['assuntos']) && is_array($disciplina_data['assuntos'])) {
                                        foreach ($disciplina_data['assuntos'] as $index => $assunto) {
                                            echo '<label data-disciplina-id="' . $disciplina_id . '" style="display: none;">';
                                            echo '<input type="checkbox" name="assuntos[]" value="' . $index . '" class="assunto-checkbox"> ' . htmlspecialchars($assunto);
                                            echo '</label>';
                                        }
                                    }
                                }
                            }
                            //<!-- ============ FORMATOS DE PERGUNTAS =============== -->
                             elseif ($id === 'formatosPerguntas') { // AGORA MATCHES config.json ID (plural)
                                // Este é o bloco para Formatos de Perguntas dinâmico
                                foreach ($formatos_perguntas_disponiveis as $formato_id => $formato_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="formatosPerguntas[]" value="' . htmlspecialchars($formato_id) . '" class="formato-pergunta-checkbox"> ' . htmlspecialchars($formato_label);
                                    echo '</label>';
                                }
                            //<!-- ============ SUBTIPOS DE RESPOSTA ABERTA =============== -->
                            } elseif ($id === 'subtiposRespostaAberta') {
                                $subtipos_ra_options = $campo['options'] ?? [];
                                foreach ($subtipos_ra_options as $subtipo_id => $subtipo_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="subtiposRespostaAberta[]" value="' . htmlspecialchars($subtipo_id) . '"> ' . htmlspecialchars($subtipo_label);
                                    echo '</label>'; // <--- CORREÇÃO: A tag </label> foi adicionada AQUI, dentro do loop
                                }
                                // A linha 'echo '</label>';' que estava fora do loop FOI REMOVIDA
                            }
                            /*elseif ($id === 'subtiposRespostaAberta') {
                                $subtipos_ra_options = $campo['options'] ?? [];
                                foreach ($subtipos_ra_options as $subtipo_id => $subtipo_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="subtiposRespostaAberta[]" value="' . htmlspecialchars($subtipo_id) . '"> ' . htmlspecialchars($subtipo_label);
                                }
                                echo '</label>';
                            }
                                */
                            echo '</div>'; // .dynamic-group-container
                            break;
                        case 'checkbox': // Para gabaritoNaProva, gabaritoGeral, folhaResposta
                        //<!-- ============ GABARITOS[PROVA,GERAL], FOLHA DE RESPOSTA E ATA DE PRESENÇA =============== -->
                            $checked = (isset($campo['default']) && $campo['default'] === true) ? 'checked' : '';
                            echo '<label>';
                            echo '<input type="checkbox" id="' . $id . '" name="' . $id . '" value="1" ' . $checked . '> ' . $label;
                            echo '</label>';
                            break;
                    }
                    echo '<div class="error-message" id="' . $id . 'Error"></div>';
                    echo '</div>';
                }
                ?>

            <button type="submit">Gerar Prova</button>
        </form>
    </div>

    <script src="public/js/script.js"></script>
</body>
</html>