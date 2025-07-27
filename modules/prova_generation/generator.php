<?php

// Incluir as dependências necessárias de outros módulos e do core
require_once __DIR__ . '/../../core/utils.php';         // Para carregar_json, sanitizar_dados, validar_dados_server_side
require_once __DIR__ . '/../../core/html_helpers.php';   // Para get_dados_cabecalho (se ainda usado no generator)
require_once __DIR__ . '/../disciplina_bd/bd_logic.php'; // Para gerarProvaBD, gerarCorpoProvaBancoDeDados
require_once __DIR__ . '/../disciplina_lp/lp_logic.php'; // Para gerar_questoes_adaptadas e suas auxiliares
require_once __DIR__ . '/../students/student_model.php'; // Para get_estudantes_por_turma

/**
 * Simula o processo de geração da prova e formata os dados para exibição.
 *
 * @param array $dados_validados Os dados do formulário já validados.
 * @param array $turmas_info_completa Array completo de turmas e estudantes (turmas.json).
 * @param array $disciplina_assuntos_info_completa Conteúdo do disciplina_assuntos.json (disciplinas e seus assuntos).
 * @param array $configuracoes Conteúdo completo do config.json.
 * @return string Uma string formatada com os detalhes da prova.
 */
function processar_geracao_prova(
    array $dados_validados, //  $resultado_validacao['dados_validados'],
    array $turmas_info_completa,  // $turmas_info_completa,
    array $disciplina_assuntos_info_completa,  //$disciplina_assuntos_info_completa,
    array $configuracoes  // $config_json 
): string {
    //DEBUG
    //ECHO "<PRE>Dados validados para geração: \n" . print_r($dados_validados, true))."</pre>";
    // Extrai dados validados
    $turma_id = $dados_validados['turma'] ?? '';
    $estudantes_selecionados_obj = $dados_validados['estudantes'] ?? [];
    $disciplina_id = $dados_validados['disciplina'] ?? '';
    $assuntos_selecionados_form = $dados_validados['assuntos'] ?? [];
    $formatos_selecionados_form = $dados_validados['formatoPerguntas'] ?? [];
    $subtipos_resposta_aberta_selecionados_form = $dados_validados['subtiposRespostaAberta'] ?? [];
    $dificuldade_id = $dados_validados['dificuldade'] ?? '';
    $tipo_prova_id = $dados_validados['tipoProva'] ?? '';
    $data_prova_str = $dados_validados['dataProva'] ?? date('d/m/Y');
    $gabarito_na_prova = $dados_validados['gabaritoNaProva']; 
    $gabarito_geral = $dados_validados['gabaritoGeral']; 
    $folha_resposta = $dados_validados['folhaResposta'];
    $num_questoes = $dados_validados['numQuestoes']; 
echo '$turma_id'.$turma_id;
    // Mapeamentos para nomes legíveis
    $niveis_dificuldade_map = $configuracoes['formulario_campos'][array_search('dificuldade', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];
    $tipos_prova_map = $configuracoes['formulario_campos'][array_search('tipoProva', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];
    $subtipos_resposta_aberta_map = $configuracoes['formulario_campos'][array_search('subtiposRespostaAberta', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];

    // Obter nome legível da turma
    $nome_turma = 'Turma desconhecida';
    $turma_completa_obj = null; // Para pegar todos os estudantes da turma
    foreach ($turmas_info_completa as $t) {
        if ($t['id'] === $turma_id) {
            $nome_turma = $t['nome'];
            $turma_completa_obj = $t;
            break;
        }
    }

    // Obter nome legível da disciplina
    $nome_disciplina = 'Disciplina desconhecida';
    foreach ($disciplina_assuntos_info_completa as $d) {
        if ($d['id'] === $disciplina_id) {
            $nome_disciplina = $d['nome'];
            break;
        }
    }

    $nome_dificuldade = $niveis_dificuldade_map[$dificuldade_id] ?? 'Nível desconhecido';
    $nome_tipo_prova = $tipos_prova_map[$tipo_prova_id] ?? 'Tipo desconhecido';

    $assuntos_formatados = !empty($assuntos_selecionados_form) ? implode(', ', $assuntos_selecionados_form) : 'Nenhum assunto selecionado';
    $formatos_perguntas_formatados = !empty($formatos_selecionados_form) ? implode(', ', $formatos_selecionados_form) : 'Nenhum formato selecionado';
    $subtipos_ra_formatados = !empty($subtipos_resposta_aberta_selecionados_form) ? implode(', ', array_map(function($key) use ($subtipos_resposta_aberta_map) {
        return $subtipos_resposta_aberta_map[$key] ?? $key;
    }, $subtipos_resposta_aberta_selecionados_form)) : 'Nenhum subtipo RA selecionado';


    $saida = "<div class='quiz-result-container'>";
    // Link "Voltar ao Formulário" no topo da página
    $saida .= "<div class='back-to-form-link'><a href='index.php'>&lt;&lt; Voltar ao Formulário</a></div>"; // Assumindo index.php é o formulário
    // INÍCIO do bloco que será ocultado na impressão
    $saida .= "<div class='config-section print-hidden'>"; 
    $saida .= "<h2>Configurações da Prova Gerada:</h2>";
    $saida .= "<p><strong>Turma:</strong> " . $nome_turma . "</p>";
    $saida .= "<p><strong>Estudantes Selecionados:</strong> " . count($estudantes_selecionados_obj) . "</p>";
    $saida .= "<p><strong>Disciplina:</strong> " . $nome_disciplina . "</p>";
    $saida .= "<p><strong>Assuntos:</strong> " . $assuntos_formatados . "</p>";
    $saida .= "<p><strong>Formato das Perguntas:</strong> " . $formatos_perguntas_formatados . "</p>";
    if (in_array("Resposta Aberta", $formatos_selecionados_form)) {
        $saida .= "<p><strong>Subtipos Resposta Aberta:</strong> " . $subtipos_ra_formatados . "</p>";
    }
    $saida .= "<p><strong>Nível de Dificuldade:</strong> " . $nome_dificuldade . "</p>";
    $saida .= "<p><strong>Tipo de Prova:</strong> " . $nome_tipo_prova . "</p>";
    $saida .= "<p><strong>Data da Prova:</strong> " . $data_prova_str . "</p>";
    $saida .= "<p><strong>Número de Questões:</strong> " . $num_questoes . "</p>";
    $saida .= "<p><strong>Gabarito na Própria Prova:</strong> " . ($gabarito_na_prova ? 'Sim' : 'Não') . "</p>";
    $saida .= "<p><strong>Gerar Gabarito Geral:</strong> " . ($gabarito_geral ? 'Sim' : 'Não') . "</p>";
    $saida .= "<p><strong>Gerar Folha de Respostas Separada:</strong> " . ($folha_resposta ? 'Sim' : 'Não') . "</p>";
    $saida .= "<hr>";
    $saida .= "</div>"; // FIM do bloco que será ocultado na impressão
    //$saida .= "<div class='quebra-de-pagina'></div>"; // QUEBRA DE PÁGINA AQUI


    $RespostaGeral = []; // Array para armazenar o gabarito geral de todas as provas

    $nprovas = count($estudantes_selecionados_obj); // Número de provas a serem geradas

    // Define os assuntos de laço que podem ser gerados
    $assuntos_laco_para_gerar = [];
    if (in_array("FOR", $assuntos_selecionados_form)) $assuntos_laco_para_gerar[] = "FOR";
    if (in_array("WHILE", $assuntos_selecionados_form)) $assuntos_laco_para_gerar[] = "WHILE";
    if (in_array("DO-WHILE", $assuntos_selecionados_form)) $assuntos_laco_para_gerar[] = "DO-WHILE";

    // Define os subtipos de questão de resposta aberta se o formato foi selecionado
    $usar_subtipos_resposta_aberta = in_array("Resposta Aberta", $formatos_selecionados_form);
    
    // Lógica para tipo de prova: "Por Turma" (mesmas questões para todos)
    $questoes_base_prova_turma = [];
    if ($tipo_prova_id === 'por_turma' || $tipo_prova_id === '50_50') {
        // Gera as questões base para "Por Turma" e para a primeira metade do "50/50"
        $questoes_base_prova_turma = gerar_questoes_adaptadas(
            $num_questoes,
            $assuntos_laco_para_gerar,
            $formatos_selecionados_form,
            $subtipos_resposta_aberta_selecionados_form
        );
    }

    $saida_folha_respostas = ""; // String para acumular o conteúdo da folha de respostas

    for ($np = 0; $np < $nprovas; $np++) {
        $estudante_atual = $estudantes_selecionados_obj[$np];
        $respostas_prova_atual = [];

        $questoes_geradas_para_prova = [];

        if ($tipo_prova_id === 'por_turma') {
            // Reutiliza as questões geradas para a turma
            $questoes_geradas_para_prova = $questoes_base_prova_turma;
        } elseif ($tipo_prova_id === '50_50') {
            $metade_estudantes = ceil($nprovas / 2); // Arredonda para cima para garantir que a primeira metade tenha mais ou igual
            if ($np < $metade_estudantes) {
                // Primeira metade dos estudantes recebe a prova "por_turma" (mesmas questões)
                $questoes_geradas_para_prova = $questoes_base_prova_turma;
            } else {
                // Segunda metade dos estudantes recebe a prova "por_estudante" (questões diferentes)
                $questoes_geradas_para_prova = gerar_questoes_adaptadas(
                    $num_questoes,
                    $assuntos_laco_para_gerar,
                    $formatos_selecionados_form,
                    $subtipos_resposta_aberta_selecionados_form
                );
            }
        } else { // 'por_estudante'
            // Gera questões individualmente para cada estudante
            $questoes_geradas_para_prova = gerar_questoes_adaptadas(
                $num_questoes,
                $assuntos_laco_para_gerar,
                $formatos_selecionados_form,
                $subtipos_resposta_aberta_selecionados_form
            );
        }

        // Determina a classe CSS para o tamanho da fonte
        $font_class = '';
        $is_cauafelipe = (strtoupper($estudante_atual['nome_completo']) === 'CAUA FELIPE SOARES VIRTUOZO' && $nome_turma === '2º TDS-A');
        if ($is_cauafelipe) {
            $font_class = ' font-size-large';
        }

        $saida .= "<div class='prova-content" . $font_class . "'>"; // Abre a div para o conteúdo da prova individual
        
        // Cabeçalho fixo do template
        $cabecalho_template = $configuracoes['cabecalho_template'];
        $cabecalho = sanitizar_dados($cabecalho_template['escola']) . "<br>";
        $cabecalho .= sanitizar_dados($cabecalho_template['curso']) . "  " . sanitizar_dados($cabecalho_template['professor']) . "    TURMA: " . sanitizar_dados($nome_turma) . "<br>";
        
        // Ajuste para CAUA FELIPE: Matrícula e Data na mesma linha
        if ($is_cauafelipe) {
            $cabecalho .= sanitizar_dados($cabecalho_template['n_ata']) . "     Disciplina: " . sanitizar_dados($nome_disciplina) . "<br>";
            $cabecalho .= "Assine aqui: _____________________________________________________<br>";
            $cabecalho .= "Nº Matrícula: " . htmlspecialchars($estudante_atual['matricula']) . "     Data: " . $data_prova_str . "<br>";
        } else {
            $cabecalho .= sanitizar_dados($cabecalho_template['n_ata']) . "     Disciplina: " . sanitizar_dados($nome_disciplina) . "     Data:" . $data_prova_str . "<br>";
            // Adiciona o campo de assinatura e matrícula ao cabeçalho
            $cabecalho .= "Assine aqui: _____________________________________________________<br>";
            $cabecalho .= "Nº Matrícula: " . htmlspecialchars($estudante_atual['matricula']) . "<br>";
        }

        // Adiciona link "Voltar ao Formulário" no cabeçalho de cada prova
        $cabecalho .= "<div class='back-to-form-link' style='text-align: right; font-size: 0.8em; border: none; padding: 0;'><a href='index.php'>Voltar ao Formulário</a></div>";


        $saida .= "<pre>" . $cabecalho . "</pre>";
        $saida .= "Nome Completo: <b>" . htmlspecialchars($estudante_atual['nome_completo']) . "</b><br>";
        $saida .= "<br><center>Avaliação de Lógica [nº<b> " . ($np + 1) . "</b> / " . ($nprovas) . "]</center>";

        // Acumula questões para a folha de respostas se necessário
        if ($folha_resposta) {
            $saida_folha_respostas .= "<div class='quebra-de-pagina'></div>";
            $saida_folha_respostas .= "<div class='prova-content" . $font_class . "'>"; // Abre a div para a folha de resposta
            
            $cabecalho_folha_respostas = sanitizar_dados($cabecalho_template['escola']) . "<br>";
            $cabecalho_folha_respostas .= sanitizar_dados($cabecalho_template['curso']) . "  " . sanitizar_dados($cabecalho_template['professor']) . "    TURMA: " . sanitizar_dados($nome_turma) . "<br>";
            
            // Ajuste para CAUA FELIPE na folha de respostas
            if ($is_cauafelipe) {
                $cabecalho_folha_respostas .= sanitizar_dados($cabecalho_template['n_ata']) . "     Disciplina: " . sanitizar_dados($nome_disciplina) . "<br>";
                $cabecalho_folha_respostas .= "Assine aqui: _____________________________________________________<br>";
                $cabecalho_folha_respostas .= "Nº Matrícula: " . htmlspecialchars($estudante_atual['matricula']) . "     Data: " . $data_prova_str . "<br>";
            } else {
                $cabecalho_folha_respostas .= sanitizar_dados($cabecalho_template['n_ata']) . "     Disciplina: " . sanitizar_dados($nome_disciplina) . "     Data:" . $data_prova_str . "<br>";
                $cabecalho_folha_respostas .= "Assine aqui: _____________________________________________________<br>";
                $cabecalho_folha_respostas .= "Nº Matrícula: " . htmlspecialchars($estudante_atual['matricula']) . "<br>";
            }

            // Adiciona link "Voltar ao Formulário" na folha de resposta
            $cabecalho_folha_respostas .= "<div class='back-to-form-link' style='text-align: right; font-size: 0.8em; border: none; padding: 0;'><a href='index.php'>Voltar ao Formulário</a></div>";
            $saida_folha_respostas .= "<pre>" . $cabecalho_folha_respostas . "</pre>";

            $saida_folha_respostas .= "Nome Completo: <b>" . htmlspecialchars($estudante_atual['nome_completo']) . "</b><br>";
            $saida_folha_respostas .= "<br><center>Folha de Respostas - Avaliação de Lógica</center>";
            $saida_folha_respostas .= "<p>Preencha as respostas nas linhas abaixo:</p>";
        }

        // Exibir as questões geradas para a prova atual
        foreach ($questoes_geradas_para_prova as $i => $questao) {
            $saida .= "<p><b>Questão " . ($i + 1) . "</b> - ";
            // C) REMOVIDA A IDENTIFICAÇÃO DO-WHILE(TIPO2) -
            $saida .= htmlspecialchars($questao['enunciado']) . "<br>";
            $saida .= "<pre class='question-code'>&lt;?php<br>" . $questao['codigo'] . "<br> ?></pre>"; // Removida a palavra "Código"
            $saida .= "</p>"; // Fecha o parágrafo da questão
            
            // Armazenar resposta para o gabarito da prova atual (para ser exibido no final da prova)
            $respostas_prova_atual[$i] = "Q" . ($i + 1) . ") " . htmlspecialchars($questao['resposta']);

            // Adicionar linha para resposta na folha de respostas
            if ($folha_resposta) {
                $saida_folha_respostas .= "<p>" . ($i + 1) . ". __________________________________________________________________________</p>";
            }
        }
        
        // A) EXIBIR GABARITO DA PRÓPRIA PROVA NO FINAL DE CADA PROVA
        if ($gabarito_na_prova) {
            $saida .= "<div class='gabarito-prova-individual'>";
            $saida .= "<h3>Gabarito da Prova:</h3>";
            foreach ($respostas_prova_atual as $resposta_str) {
                $saida .= "<span>" . $resposta_str . "</span> ";
            }
            $saida .= "</div>";
        }

        // D) GABARITO GERAL ÚNICO PARA TIPO 'POR TURMA'
        if ($tipo_prova_id === 'por_turma') {
            // Se for "Por Turma", armazena o gabarito apenas da primeira prova
            if ($np === 0) {
                $RespostaGeral[0] = $respostas_prova_atual; // Armazena o objeto completo da prova
            }
            } else {
            // Para outros tipos, armazena o gabarito de cada prova individual
            $RespostaGeral[$np] = $respostas_prova_atual; // Armazena o objeto completo da prova
        }


        $saida .= "</div>"; // Fecha a div .prova-content
        
        // Adiciona quebra de página entre as provas
        if ($np < ($nprovas - 1)) {
            $saida .= "<div class='quebra-de-pagina'></div>";
        }
        
        // Fecha a div da folha de resposta, se aberta
        if ($folha_resposta) {
            $saida_folha_respostas .= "</div>"; // Fecha a div .prova-content da folha de resposta
        }
    }

    // B) GERAR ATA DE PRESENÇA
        $saida .= "<div class='quebra-de-pagina'></div>";
        $saida .= "<div class='attendance-sheet'>";
        $saida .= "<pre style='margin-bottom: 0;'>"; // Estilo para evitar margem extra antes do título
        $saida .= sanitizar_dados($cabecalho_template['escola']) . "<br>";
        $saida .= "Curso: " . sanitizar_dados($cabecalho_template['curso']) . "    Professor: " . sanitizar_dados($cabecalho_template['professor']) . "<br>";
        $saida .= "Turma: " . sanitizar_dados($nome_turma) . "    Disciplina: " . sanitizar_dados($nome_disciplina) . "<br>";
        $saida .= "Data: " . $data_prova_str . "    Aplicador: ____________________________________________________<br>";
        $saida .= "</pre>";
        $saida .= "<h2 style='display: block; text-align: center; margin: 0 auto;'>ATA DE PRESENÇA</h2>";
        //$saida .= "<h2 style='text-align: center; margin-top: 10px;'>ATA DE PRESENÇA</h2>";
        $saida .= "<table border='1' style='width:100%; border-collapse: collapse; font-size: 11pt;'>"; // Font size 11pt
        $saida .= "<thead><tr><th>Nº</th><th>Nome Completo</th><th>Matrícula</th><th>Assinatura</th></tr></thead>"; // Data column removed
        $saida .= "<tbody>";
        
        // b) A lista de presença deve constar apenas os nomes dos estudantes selecionados.
        // Ordena os estudantes selecionados pelo nome completo para a ata
        usort($estudantes_selecionados_obj, function($a, $b) {
            return strcmp($a['nome_completo'], $b['nome_completo']);
        });

        if (!empty($estudantes_selecionados_obj)) {
            foreach ($estudantes_selecionados_obj as $idx => $estudante_selecionado) {
                $saida .= "<tr>";
                $saida .= "<td>" . ($idx + 1) . "</td>";
                $saida .= "<td>" . htmlspecialchars($estudante_selecionado['nome_completo']) . "</td>";
                $saida .= "<td>" . htmlspecialchars($estudante_selecionado['matricula']) . "</td>";
                $saida .= "<td style='width: 45%;'>&nbsp;</td>"; // Assinatura expandida, absorvendo espaço da data
                $saida .= "</tr>";
            }
        } else {
            $saida .= "<tr><td colspan='4'>Nenhum estudante selecionado para esta prova.</td></tr>";
        }

        $saida .= "</tbody>";
        $saida .= "</table>";
        $saida .= "</div>"; // Fecha attendance-sheet

    // Exibir Gabarito Geral (para todas as provas ou único para "Por Turma")
        if ($gabarito_geral) {
        $saida .= "<div class='quebra-de-pagina'></div>";
        $saida .= "<h2>Gabarito Geral:</h2>";
        // Se for "Por Turma", $RespostaGeral terá apenas um elemento
        foreach ($RespostaGeral as $p_idx => $respostas_prova) {
            $saida .= "<p><b>";
            // Ajusta o título para "Gabarito Único" se for por turma, senão mostra o número da prova ou o nome do estudante
            if ($tipo_prova_id === 'por_turma') {
                $saida .= "Gabarito Único da Prova";
            } else {
                // Find the correct student name based on index $p_idx
                $current_student_name = "Estudante Desconhecido";
                if (isset($estudantes_selecionados_obj[$p_idx]['nome_completo'])) {
                    $current_student_name = htmlspecialchars($estudantes_selecionados_obj[$p_idx]['nome_completo']);
                }
                $saida .= "Prova Nº" . ($p_idx + 1) . " (" . $current_student_name . ")";
            }
            $saida .= ":</b> ";
            foreach ($respostas_prova as $r_idx => $resposta_str) {
                $saida .= "<b>". $resposta_str ." </b>";
            }
            $saida .= "</p>";
        }
        }

    // Adicionar a folha de respostas ao final, se solicitado
        if ($folha_resposta && !empty($saida_folha_respostas)) {
            $saida .= $saida_folha_respostas;
        }

    $saida .= "</div>"; // Fecha quiz-result-container
    return $saida;
}
?>