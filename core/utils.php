<?php

/**
 * Carrega e decodifica um arquivo JSON.
 * @param string $caminho_arquivo O caminho para o arquivo JSON.
 * @return array O conteúdo do JSON como um array associativo, ou um array vazio em caso de erro.
 */
function carregar_json(string $caminho_arquivo): array {
    if (!file_exists($caminho_arquivo)) {
        error_log("Erro: Arquivo JSON não encontrado em: " . $caminho_arquivo);
        return [];
    }
    $json_conteudo = file_get_contents($caminho_arquivo);
    if ($json_conteudo === false) {
        error_log("Erro: Não foi possível ler o arquivo: " . $caminho_arquivo);
        return [];
    }
    $dados = json_decode($json_conteudo, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erro ao decodificar JSON de " . $caminho_arquivo . ": " . json_last_error_msg());
        return [];
    }
    return $dados;
}

/**
 * Sanitiza uma string de entrada para exibição segura em HTML.
 * Remove espaços em branco do início/fim, barras invertidas e converte caracteres especiais em entidades HTML.
 * @param string $dado A string a ser sanitizada.
 * @return string A string sanitizada.
 */
function sanitizar_dados(string $dado): string {
    $dado = trim($dado);
    $dado = stripslashes($dado);
    $dado = htmlspecialchars($dado, ENT_QUOTES, 'UTF-8');
    return $dado;
}

/**
 * Valida os dados recebidos do formulário no lado do servidor.
 *
 * @param array $dados_recebidos O array $_POST.
 * @param array $configuracoes Conteúdo do config.json.
 * @param array $turmas_info Array completo de turmas e estudantes (turmas.json).
 * @param array $disciplina_assuntos_info Conteúdo do disciplina_assuntos.json (disciplinas e seus assuntos).
 * @param array $formatos_disponiveis Conteúdo do formato_perguntas.json.
 * @return array Um array com 'sucesso' (bool), 'mensagens_erro' (array de strings) e 'dados_validados' (array).
 */
function validar_dados_server_side(
    array $dados_recebidos,
    array $configuracoes,
    array $turmas_info,
    array $disciplina_assuntos_info,
    array $formatos_disponiveis
): array {
    $erros = [];
    $dados_validados = [];

    // Mapeamentos para validação (obtidos do config.json dinamicamente)
    $niveis_dificuldade_map = $configuracoes['formulario_campos'][array_search('dificuldade', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];
    $tipos_prova_map = $configuracoes['formulario_campos'][array_search('tipoProva', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];
    $min_questoes = $configuracoes['configs_gerais']['min_questoes'] ?? 1;
    $max_questoes = $configuracoes['configs_gerais']['max_questoes'] ?? 50;
    
    $subtipos_resposta_aberta_map = $configuracoes['formulario_campos'][array_search('subtiposRespostaAberta', array_column($configuracoes['formulario_campos'], 'id'))]['options'] ?? [];


    // --- Validação Campo a Campo ---

    // ============Turma
    $turma_id_selecionada = $dados_recebidos['turma'] ?? '';
    $turma_valida = false;
    $turma_selecionada_detalhes = null; // Para armazenar o objeto completo da turma

    if (empty($turma_id_selecionada)) {
        $erros[] = "Selecione uma turma.";
    } else {
        foreach ($turmas_info as $turma) {
            if ($turma['id'] === $turma_id_selecionada) {
                $turma_valida = true;
                $turma_selecionada_detalhes = $turma;
                break;
            }
        }
        if (!$turma_valida) {
            $erros[] = "Turma selecionada é inválida.";
        } else {
            $dados_validados['turma'] = sanitizar_dados($turma_id_selecionada);
        }
    }

    // ==============Estudantes
    $estudantes_selecionados_matriculas = $dados_recebidos['estudantes'] ?? [];
    $dados_validados['estudantes'] = []; // Inicializa como array vazio

    if ($turma_valida && $turma_selecionada_detalhes) {
        if (empty($estudantes_selecionados_matriculas) || !is_array($estudantes_selecionados_matriculas)) {
            $erros[] = "Selecione pelo menos um estudante da turma " . htmlspecialchars($turma_selecionada_detalhes['nome']) . ".";
        } else {
            $estudantes_validos_na_turma = [];
            $matriculas_da_turma = array_column($turma_selecionada_detalhes['estudantes'], 'matricula');

            foreach ($estudantes_selecionados_matriculas as $matricula_bruta) {
                $matricula_sanitizada = sanitizar_dados($matricula_bruta);
                if (in_array($matricula_sanitizada, $matriculas_da_turma)) {
                    // Adiciona a matrícula validada e procura os detalhes completos do estudante
                    foreach ($turma_selecionada_detalhes['estudantes'] as $estudante_detalhe) {
                        if ($estudante_detalhe['matricula'] === $matricula_sanitizada) {
                            $estudantes_validos_na_turma[] = $estudante_detalhe; // Armazena o objeto completo do estudante
                            break;
                        }
                    }
                } else {
                    $erros[] = "Matrícula de estudante inválida ou não pertence à turma selecionada: " . $matricula_sanitizada;
                }
            }
            if (empty($estudantes_validos_na_turma)) { // Se o count for 0, e a turma tem estudantes
                 $erros[] = "Nenhum estudante válido foi selecionado para a turma " . htmlspecialchars($turma_selecionada_detalhes['nome']) . ".";
            } else {
                $dados_validados['estudantes'] = $estudantes_validos_na_turma;
            }
        }
    } else if (empty($turma_id_selecionada)) {
        // Se a turma não foi selecionada, o erro de estudantes já é tratado por "Selecione uma turma."
    } else {
        $erros[] = "Não foi possível validar os estudantes. Turma inválida ou não encontrada.";
    }


    // ============Disciplina
    $disciplina_id_selecionada = $dados_recebidos['disciplina'] ?? '';
    $disciplina_valida = false;
    $disciplina_selecionada_detalhes = null; // Para armazenar o objeto completo da disciplina

    if (empty($disciplina_id_selecionada)) {
        $erros[] = "Selecione uma disciplina.";
    } else {
        foreach ($disciplina_assuntos_info as $disciplina_data) {
            if ($disciplina_data['id'] === $disciplina_id_selecionada) {
                $disciplina_valida = true;
                $disciplina_selecionada_detalhes = $disciplina_data;
                break;
            }
        }
        if (!$disciplina_valida) {
            $erros[] = "Disciplina selecionada é inválida.";
        } else {
            $dados_validados['disciplina'] = sanitizar_dados($disciplina_id_selecionada);
        }
    }

    // ================Assuntos
    $assuntos_selecionados = $dados_recebidos['assuntos'] ?? [];
    $dados_validados['assuntos'] = []; // Inicializa como array vazio
    //DEBUG
    echo __file__;
    echo "<pre>Array assuntos_selecionados: ";print_r($assuntos_selecionados); echo "</pre>";
    echo "<pre>Array disciplina_selecionada_detalhes['assuntos']: ";print_r($disciplina_selecionada_detalhes['assuntos']); echo "</pre>";
    if ($disciplina_valida && $disciplina_selecionada_detalhes) {
        if (empty($assuntos_selecionados) || !is_array($assuntos_selecionados)) {
            $erros[] = "Selecione pelo menos um assunto da disciplina " . htmlspecialchars($disciplina_selecionada_detalhes['nome']) . ".";
        } else {
            $assuntos_validos_da_disciplina = [];
            $assuntos_disponiveis_da_disciplina = $disciplina_selecionada_detalhes['assuntos'] ?? [];

            // Contagem específica para FOR/WHILE/DO-WHILE se a disciplina for Lógica de Programação
            $controle_repeticao_assuntos = ['FOR', 'WHILE', 'DO-WHILE'];
            $tem_assunto_controle_repeticao_selecionado = false;

            foreach ($assuntos_selecionados as $assunto_bruto) {
                $assunto_sanitizado = sanitizar_dados($assunto_bruto);
                if (in_array($assunto_sanitizado, $assuntos_disponiveis_da_disciplina)) {
                    $assuntos_validos_da_disciplina[] = $assunto_sanitizado;
                    if (in_array($assunto_sanitizado, $controle_repeticao_assuntos)) {
                        $tem_assunto_controle_repeticao_selecionado = true;
                    }
                } else {
                    $erros[] = "Assunto inválido ou não pertence à disciplina selecionada: " . $assunto_sanitizado;
                }
            }
            if (empty($assuntos_validos_da_disciplina)) { // Se o count for 0, e a disciplina tem assuntos
                 $erros[] = "Nenhum assunto válido foi selecionado para a disciplina " . htmlspecialchars($disciplina_selecionada_detalhes['nome']) . ".";
            } else {
                $dados_validados['assuntos'] = $assuntos_validos_da_disciplina;
            }
        }
    } else if (empty($disciplina_id_selecionada)) {
        // Se a disciplina não foi selecionada, o erro de assuntos já é tratado por "Selecione uma disciplina."
    } else {
        $erros[] = "Não foi possível validar os assuntos. Disciplina inválida ou não encontrada.";
    }


    // ===================Formato das Perguntas
    $formatos_selecionados = $dados_recebidos['formatoPerguntas'] ?? [];
    if (empty($formatos_selecionados) || !is_array($formatos_selecionados)) {
        $erros[] = "Selecione pelo menos um formato de pergunta.";
    } else {
        $formatos_validos = [];
        $resposta_aberta_selecionada = false;
        foreach ($formatos_selecionados as $formato_bruto) {
            $formato_sanitizado = sanitizar_dados($formato_bruto);
            if (in_array($formato_sanitizado, $formatos_disponiveis)) {
                $formatos_validos[] = $formato_sanitizado;
                if ($formato_sanitizado === "Resposta Aberta") {
                    $resposta_aberta_selecionada = true;
                }
            } else {
                $erros[] = "Formato de pergunta inválido detectado: " . $formato_sanitizado;
            }
        }
        if (empty($formatos_validos)) {
            $erros[] = "Nenhum formato de pergunta válido foi selecionado.";
        } else {
            $dados_validados['formatoPerguntas'] = $formatos_validos;
        }

        // =============Validação condicional para subtipos de Resposta Aberta
        if ($resposta_aberta_selecionada) {
            $subtipos_ra_selecionados = $dados_recebidos['subtiposRespostaAberta'] ?? [];
            if (empty($subtipos_ra_selecionados) || !is_array($subtipos_ra_selecionados)) {
                $erros[] = "Selecione pelo menos um subtipo de questão para 'Resposta Aberta'.";
            } else {
                $subtipos_ra_validos = [];
                foreach ($subtipos_ra_selecionados as $subtipo_bruto) {
                    $subtipo_sanitizado = sanitizar_dados($subtipo_bruto);
                    if (array_key_exists($subtipo_sanitizado, $subtipos_resposta_aberta_map)) {
                        $subtipos_ra_validos[] = $subtipo_sanitizado;
                    } else {
                        $erros[] = "Subtipo de Resposta Aberta inválido detectado: " . $subtipo_sanitizado;
                    }
                }
                if (empty($subtipos_ra_validos)) {
                    $erros[] = "Nenhum subtipo de Resposta Aberta válido foi selecionado.";
                } else {
                    $dados_validados['subtiposRespostaAberta'] = $subtipos_ra_validos;
                }
            }
        }
    }

    // ==================Nível de Dificuldade
    $nivel_dificuldade_selecionado = $dados_recebidos['dificuldade'] ?? '';
    if (empty($nivel_dificuldade_selecionado)) {
        $erros[] = "Nível de Dificuldade é obrigatório.";
    } else {
        if (!array_key_exists(sanitizar_dados($nivel_dificuldade_selecionado), $niveis_dificuldade_map)) {
            $erros[] = "Nível de Dificuldade inválido.";
        } else {
            $dados_validados['dificuldade'] = sanitizar_dados($nivel_dificuldade_selecionado);
        }
    }

    // ================Tipo de Prova
    $tipo_prova_selecionado = $dados_recebidos['tipoProva'] ?? '';
    if (empty($tipo_prova_selecionado)) {
        $erros[] = "Tipo de Prova é obrigatório.";
    } else {
        if (!array_key_exists(sanitizar_dados($tipo_prova_selecionado), $tipos_prova_map)) {
            $erros[] = "Tipo de Prova inválido.";
        } else {
            $dados_validados['tipoProva'] = sanitizar_dados($tipo_prova_selecionado);
        }
    }

    // ================Data da Prova
    $data_prova_opcao = $dados_recebidos['dataProva'] ?? '';
    if (empty($data_prova_opcao)) {
        $erros[] = "Selecione uma opção para a data da prova.";
    } else {
        if ($data_prova_opcao === 'today') {
            $dados_validados['dataProva'] = date('d/m/Y');
        } elseif ($data_prova_opcao === 'custom') {
            $data_personalizada = $dados_recebidos['dataPersonalizada'] ?? '';
            if (empty($data_personalizada)) {
                $erros[] = "Insira a data personalizada para a prova.";
            } else {
                // Validação de formato de data básica (yyyy-mm-dd)
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data_personalizada)) {
                    $erros[] = "Formato de data personalizada inválido. Use AAAA-MM-DD.";
                } else {
                    $dados_validados['dataProva'] = date('d/m/Y', strtotime($data_personalizada));
                }
            }
        } else {
            $erros[] = "Opção de data da prova inválida.";
        }
    }

    // ===========Gabarito na Prova (checkbox)
    $dados_validados['gabaritoNaProva'] = isset($dados_recebidos['gabaritoNaProva']) && $dados_recebidos['gabaritoNaProva'] === '1';

    // ===========Gabarito Geral (checkbox)
    $dados_validados['gabaritoGeral'] = isset($dados_recebidos['gabaritoGeral']) && $dados_recebidos['gabaritoGeral'] === '1';

    // ===========Folha de Resposta (checkbox)
    $dados_validados['folhaResposta'] = isset($dados_recebidos['folhaResposta']) && $dados_recebidos['folhaResposta'] === '1';

    // ===========Número de Questões
    if (empty($dados_recebidos['numQuestoes']) || !is_numeric($dados_recebidos['numQuestoes'])) {
        $erros[] = "Número de Questões é obrigatório e deve ser um número.";
    } else {
        $num_questoes = (int) $dados_recebidos['numQuestoes'];
        if ($num_questoes < $min_questoes || $num_questoes > $max_questoes) {
            $erros[] = "O número de questões deve ser entre " . $min_questoes . " e " . $max_questoes . ".";
        } else {
            $dados_validados['numQuestoes'] = $num_questoes;
        }
    }

    return [
        'sucesso' => empty($erros),
        'mensagens_erro' => $erros,
        'dados_validados' => $dados_validados
    ];
}

?>