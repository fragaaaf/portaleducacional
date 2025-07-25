<?php
// index.php

// Inclui as funções de carregamento JSON para uso no HTML/PHP
require_once 'funcoes.php';

// Carrega os dados de configuração e opções para o formulário
$config = carregar_json('config.json');
$turmas_info_completa = carregar_json('turmas.json'); // Informação completa de turmas e estudantes
$disciplina_assuntos_info_completa = carregar_json('disciplina_assuntos.json'); // Informação completa de disciplinas e assuntos
$formatos_perguntas_disponiveis = carregar_json('formato_perguntas.json'); // Formatos de perguntas

$formulario_campos = $config['formulario_campos'] ?? [];
$configs_gerais = $config['configs_gerais'] ?? [];

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
                // 'required' para validação client-side via JS, não via HTML attribute para checkboxes/radios em grupo
                $required_attr = ($campo['required'] ?? false) && !in_array($type, ['checkbox_group', 'radio_group', 'checkbox_group_dynamic']) ? 'required' : '';
                $validation_message = htmlspecialchars($campo['validation_message'] ?? '');

                echo '<div class="form-group">';
                echo '<label for="' . $id . '">' . $label . '</label>';

                switch ($type) {
                    case 'text':
                    case 'number':
                        $min = isset($campo['min']) ? 'min="' . htmlspecialchars($campo['min']) . '"' : '';
                        $max = isset($campo['max']) ? 'max="' . htmlspecialchars($campo['max']) . '"' : '';
                        $value = isset($campo['default']) ? 'value="' . htmlspecialchars($campo['default']) . '"' : '';
                        echo '<input type="' . $type . '" id="' . $id . '" name="' . $id . '" ' . $min . ' ' . $max . ' ' . $value . ' ' . $required_attr . '>';
                        break;

                    case 'radio_group':
                        echo '<div class="radio-group">';
                        $options = [];
                        if (isset($campo['options_source'])) {
                            $source_data = carregar_json($campo['options_source']);
                            // Turmas ou Disciplinas
                            foreach ($source_data as $item) {
                                $options[$item['id']] = $item['nome'];
                            }
                        } else {
                            $options = $campo['options'] ?? []; // Opções definidas diretamente no config.json (dificuldade, tipoProva, dataProva)
                        }

                        foreach ($options as $option_id => $option_name) {
                            $checked = (isset($campo['default']) && $campo['default'] == $option_id) ? 'checked' : '';
                            echo '<label>';
                            echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($option_id) . '" ' . $checked . '> ' . htmlspecialchars($option_name);
                            echo '</label>';
                        }
                        echo '</div>';
                        // Adicionar campo de data personalizada se o id for 'dataProva'
                        if ($id === 'dataProva') {
                            echo '<div id="customDateInputGroup" style="display:none; margin-top: 10px;">';
                            echo '<input type="date" id="dataPersonalizada" name="dataPersonalizada">';
                            echo '</div>';
                        }
                        break;

                    case 'checkbox_group': // Para formatos de perguntas
                        echo '<div class="checkbox-group">';
                        $options_data = [];
                        if (isset($campo['options_source'])) {
                            $options_data = carregar_json($campo['options_source']); // Ex: formato_perguntas.json
                        } else {
                            $options_data = $campo['options'] ?? []; // Fallback, se houver
                        }

                        foreach ($options_data as $option_value) {
                            echo '<label>';
                            echo '<input type="checkbox" name="' . $id . '[]" value="' . htmlspecialchars($option_value) . '"> ' . htmlspecialchars($option_value);
                            echo '</label>';
                        }
                        echo '</div>';
                        break;
                    
                    case 'checkbox_group_dynamic': // Especial para estudantes, assuntos E subtipos de Resposta Aberta
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
                                    echo '<label data-turma-id="' . $turma_id . '" style="display: none;">'; // Oculto por padrão, JS vai exibir
                                    echo '<input type="checkbox" name="estudantes[]" value="' . $matricula_completa . '" class="estudante-checkbox" data-turma-id="' . $turma_id . '"> ' . $nome_completo;
                                    echo '</label>';
                                }
                            }
                        } elseif ($id === 'assuntos') {
                            foreach ($disciplina_assuntos_info_completa as $disciplina_data) {
                                $disciplina_id = htmlspecialchars($disciplina_data['id']);
                                foreach ($disciplina_data['assuntos'] as $assunto_nome) {
                                    echo '<label data-disciplina-id="' . $disciplina_id . '" style="display: none;">'; // Oculto por padrão, JS vai exibir
                                    echo '<input type="checkbox" name="assuntos[]" value="' . htmlspecialchars($assunto_nome) . '" class="assunto-checkbox" data-disciplina-id="' . $disciplina_id . '"> ' . htmlspecialchars($assunto_nome);
                                    echo '</label>';
                                }
                            }
                        } elseif ($id === 'subtiposRespostaAberta') {
                            // Subtipos de Resposta Aberta vêm do próprio config.json
                            $subtipos_ra_options = $campo['options'] ?? [];
                            foreach ($subtipos_ra_options as $subtipo_id => $subtipo_label) {
                                echo '<label>';
                                echo '<input type="checkbox" name="subtiposRespostaAberta[]" value="' . htmlspecialchars($subtipo_id) . '"> ' . htmlspecialchars($subtipo_label);
                                echo '</label>';
                            }
                        }
                        echo '</div>';
                        break;

                    case 'checkbox': // Para gabaritoNaProva, gabaritoGeral, folhaResposta
                        $checked = (isset($campo['default']) && $campo['default'] === false) ? 'checked' : '';
                        echo '<label>'; // A label já envolve o checkbox e o texto
                        echo '<input type="checkbox" id="' . $id . '" name="' . $id . '" value="1" ' . $checked . '> ' . $label;
                        echo '</label>';
                        break;
                }
                echo '<div class="error-message" id="' . $id . 'Error">' . $validation_message . '</div>';
                echo '</div>'; // Fecha form-group
            }
            ?>
            
            <button type="submit">Gerar Prova</button>
        </form>
    </div>

    <script src="public/js/script.js"></script>
</body>
</html>