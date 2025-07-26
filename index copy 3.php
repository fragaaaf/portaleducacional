<?php
// index.php

// Inclui as funções utilitárias e o arquivo de configuração global
require_once __DIR__ . '/core/utils.php'; // Caminho ajustado
$app_config = require __DIR__ . '/core/config.php';

// Carrega os dados de configuração e opções para o formulário
// Caminhos ajustados para a pasta 'data/'
$config_json = carregar_json(__DIR__ . '/data/' . $app_config['data_files']['config']);
$turmas_info_completa = carregar_json(__DIR__ . '/data/' . $app_config['data_files']['turmas']);
$disciplina_assuntos_info_completa = carregar_json(__DIR__ . '/data/' . $app_config['data_files']['disciplina_assuntos']);
$formatos_perguntas_disponiveis = carregar_json(__DIR__ . '/data/' . $app_config['data_files']['formato_perguntas']);

$formulario_campos = $config_json['formulario_campos'] ?? [];
$configs_gerais = $config_json['configs_gerais'] ?? [];

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
                        case 'select':
                            ?>
                            <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" required>
                                <option value="">Selecione...</option>
                                <?php
                                if ($id === 'disciplina') {
                                    foreach ($disciplina_assuntos_info_completa as $disciplina_id_opt => $disciplina_data) {
                                        echo '<option value="' . htmlspecialchars($disciplina_id_opt) . '">' . htmlspecialchars($disciplina_data['nome']) . '</option>';
                                    }
                                } elseif ($id === 'tipoProva') {
                                    foreach ($options as $tipo_id => $tipo_label) {
                                        echo '<option value="' . htmlspecialchars($tipo_id) . '">' . htmlspecialchars($tipo_label) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <?php
                            break;
                        case 'radio':
                            if ($id === 'turma') {
                                foreach ($turmas_info_completa as $turma_id_opt => $turma_data) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($turma_id_opt) . '" class="turma-radio" ' . (isset($campo['default']) && $campo['default'] === $turma_id_opt ? 'checked' : '') . '> ' . htmlspecialchars($turma_data['nome']);
                                    echo '</label>';
                                }
                            } else {
                                foreach ($options as $option_value => $option_label) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($option_value) . '" ' . (isset($campo['default']) && $campo['default'] === $option_value ? 'checked' : '') . '> ' . htmlspecialchars($option_label);
                                    echo '</label>';
                                }
                            }
                            break;
                        case 'checkbox_group_dynamic':
                            // Checkbox 'Selecionar Todos'
                            echo '<label class="select-all-label" id="selecionarTodos' . ucfirst($id) . 'Label">';
                            echo '<input type="checkbox" id="selecionarTodos' . ucfirst($id) . '"> ' . htmlspecialchars($campo['select_all_label'] ?? 'Selecionar Todos');
                            echo '</label>';

                            echo '<div id="' . $id . '-list-container" class="dynamic-group-container">';

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
                            } elseif ($id === 'assuntos') {
                                foreach ($disciplina_assuntos_info_completa as $disciplina_data) {
                                    $disciplina_id = htmlspecialchars($disciplina_data['id']);
                                    if (isset($disciplina_data['assuntos']) && is_array($disciplina_data['assuntos'])) {
                                        foreach ($disciplina_data['assuntos'] as $assunto_id_opt => $assunto_label) {
                                            echo '<label data-disciplina-id="' . $disciplina_id . '" style="display: none;">';
                                            echo '<input type="checkbox" name="assuntos[]" value="' . htmlspecialchars($assunto_id_opt) . '" class="assunto-checkbox" data-disciplina-id="' . $disciplina_id . '"> ' . htmlspecialchars($assunto_label);
                                            echo '</label>';
                                        }
                                    }
                                }
                            } elseif ($id === 'formatosPerguntas') {
                                // Este é o bloco para Formatos de Perguntas dinâmico
                                foreach ($formatos_perguntas_disponiveis as $formato_id => $formato_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="formatosPerguntas[]" value="' . htmlspecialchars($formato_id) . '" class="formato-pergunta-checkbox"> ' . htmlspecialchars($formato_label);
                                    echo '</label>';
                                }
                            } elseif ($id === 'subtiposRespostaAberta') {
                                $subtipos_ra_options = $campo['options'] ?? [];
                                foreach ($subtipos_ra_options as $subtipo_id => $subtipo_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="subtiposRespostaAberta[]" value="' . htmlspecialchars($subtipo_id) . '"> ' . htmlspecialchars($subtipo_label);
                                }
                                echo '</label>';
                            }
                            echo '</div>'; // .dynamic-group-container
                            break;
                        case 'checkbox': // Para gabaritoNaProva, gabaritoGeral, folhaResposta
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