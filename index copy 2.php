<?php
// index.php

// Inclui as funções utilitárias e o arquivo de configuração global
require_once __DIR__ . '/core/utils.php';
$app_config = require __DIR__ . '/core/config.php'; // Carrega o array de configuração

// Carrega os dados de configuração e opções para o formulário usando os caminhos do app_config
$config_json = carregar_json($app_config['data_files']['config']); // Usado para acessar 'formulario_campos' e 'configs_gerais'
$turmas_info_completa = carregar_json($app_config['data_files']['turmas']);
$disciplina_assuntos_info_completa = carregar_json($app_config['data_files']['disciplina_assuntos']);
$formatos_perguntas_disponiveis = carregar_json($app_config['data_files']['formato_perguntas']);

$formulario_campos = $config_json['formulario_campos'] ?? [];
$configs_gerais = $config_json['configs_gerais'] ?? [];
echo "<pre>"; 
print_r($formulario_campos[1]);
echo "</pre>"; 
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
            // O código do loop foreach permanece o mesmo do seu index.php original
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
                                // Lógica para preencher as opções do select com base no ID
                                if ($id === 'turma') {
                                    foreach ($turmas_info_completa as $turma_id_opt => $turma_data) {
                                        echo '<option value="' . htmlspecialchars($turma_id_opt) . '">' . htmlspecialchars($turma_data['nome']) . '</option>';
                                    }
                                } elseif ($id === 'disciplina') {
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
                            // *** CORREÇÃO AQUI: LÓGICA PARA RENDERIZAR TURMAS COMO RÁDIO ***
                            if ($id === 'turma') {
                                // Itera sobre os dados de turmas.json para criar os radios
                                foreach ($turmas_info_completa as $turma_id_opt => $turma_data) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($turma_id_opt) . '" ' . (isset($campo['default']) && $campo['default'] === $turma_id_opt ? 'checked' : '') . '> ' . htmlspecialchars($turma_data['nome']);
                                    echo '</label>';
                                }
                            } else {
                                // Para outros campos de rádio genéricos (não "turma")
                                foreach ($options as $option_value => $option_label) {
                                    echo '<label>';
                                    echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($option_value) . '" ' . (isset($campo['default']) && $campo['default'] === $option_value ? 'checked' : '') . '> ' . htmlspecialchars($option_label);
                                    echo '</label>';
                                }
                            }
                            break;
                            /*foreach ($options as $option_value => $option_label) {
                                echo '<label>';
                                echo '<input type="radio" name="' . $id . '" value="' . htmlspecialchars($option_value) . '" ' . (isset($campo['default']) && $campo['default'] === $option_value ? 'checked' : '') . '> ' . htmlspecialchars($option_label);
                                echo '</label>';
                            }
                            break; */
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
                        case 'checkbox_group_dynamic': // Para assuntos, formatosPerguntas
                            echo '<div class="checkbox-options">';
                            if ($id === 'assuntos') {
                                // A lista de assuntos será populada via JS dependendo da disciplina
                                // Por enquanto, mantenha o placeholder ou preencha com todos os assuntos disponíveis
                                foreach ($disciplina_assuntos_info_completa as $disc_id => $disc_data) {
                                    if (isset($disc_data['assuntos']) && is_array($disc_data['assuntos'])) {
                                        foreach ($disc_data['assuntos'] as $assunto_id_opt => $assunto_label) {
                                            // Exemplo: mostrar todos, mas o JS vai filtrar
                                            echo '<label class="subject-checkbox ' . htmlspecialchars($disc_id) . '-subject" style="display: none;">';
                                            echo '<input type="checkbox" name="assuntos[]" value="' . htmlspecialchars($assunto_id_opt) . '"> ' . htmlspecialchars($assunto_label);
                                            echo '</label>';
                                        }
                                    }
                                }

                            } elseif ($id === 'formatosPerguntas') {
                                foreach ($formatos_perguntas_disponiveis as $formato_id => $formato_label) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="formatosPerguntas[]" value="' . htmlspecialchars($formato_id) . '"> ' . htmlspecialchars($formato_label);
                                    echo '</label>';
                                }
                            }
                            echo '</div>';
                            break;
                        case 'subtipos_resposta_aberta': // Para subtiposRespostaAberta
                            echo '<div class="checkbox-options">';
                            if (isset($campo['options']) && is_array($campo['options'])) {
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
                            $checked = (isset($campo['default']) && $campo['default'] === false) ? '' : 'checked'; // Ajuste aqui para o seu padrão se true é padrão
                            echo '<label>'; // A label já envolve o checkbox e o texto
                            echo '<input type="checkbox" id="' . $id . '" name="' . $id . '" value="1" ' . $checked . '> ' . $label;
                            echo '</label>';
                            break;
                    }
                    echo '<div class="error-message" id="' . $id . 'Error"></div>'; // Mensagens de erro virão via JS, ou podem ser tratadas aqui se houver erros PHP
                    echo '</div>'; // Fecha form-group
                }
                ?>
            <button type="submit">Gerar Prova</button>
        </form>
    </div>

    <script src="public/js/script.js"></script>
</body>
</html>