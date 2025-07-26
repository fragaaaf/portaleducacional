<?php
// funcoes.php

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

    // Turma
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

    // Estudantes
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


    // Disciplina
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

    // Assuntos
    $assuntos_selecionados = $dados_recebidos['assuntos'] ?? [];
    $dados_validados['assuntos'] = []; // Inicializa como array vazio

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


    // Formato das Perguntas
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

        // Validação condicional para subtipos de Resposta Aberta
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

    // Nível de Dificuldade
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

    // Tipo de Prova
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

    // Data da Prova
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

    // Gabarito na Prova (checkbox)
    $dados_validados['gabaritoNaProva'] = isset($dados_recebidos['gabaritoNaProva']) && $dados_recebidos['gabaritoNaProva'] === '1';

    // Gabarito Geral (checkbox)
    $dados_validados['gabaritoGeral'] = isset($dados_recebidos['gabaritoGeral']) && $dados_recebidos['gabaritoGeral'] === '1';

    // Folha de Resposta (checkbox)
    $dados_validados['folhaResposta'] = isset($dados_recebidos['folhaResposta']) && $dados_recebidos['folhaResposta'] === '1';

    // Número de Questões
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


// ==============================================
// FUNÇÕES PARA GERAÇÃO DE QUESTÕES DE REPETIÇÃO
// ==============================================

/**
 * Gera um conjunto de questões com base nos parâmetros fornecidos.
 * Esta é a função principal que integra as seleções do usuário.
 *
 * @param int $quantidade Número de questões a serem geradas.
 * @param array $assuntos_selecionados_form Lista de assuntos selecionados no formulário.
 * @param array $formatos_selecionados_form Lista de formatos de pergunta selecionados no formulário.
 * @param array $subtipos_resposta_aberta_selecionados_form Lista de subtipos de RA selecionados (TIPO1-4).
 * @return array Um array de objetos de questão.
 */
function gerar_questoes_adaptadas(
    int $quantidade,
    array $assuntos_selecionados_form,
    array $formatos_selecionados_form,
    array $subtipos_resposta_aberta_selecionados_form
): array {
    $questoes = [];
    $tipos_laco_disponiveis = []; // FOR, WHILE, DO-WHILE baseados nos assuntos selecionados
    $tipos_questao_resposta_aberta = []; // TIPO1, TIPO2, TIPO3, TIPO4 se 'Resposta Aberta' foi selecionada

    // Determina quais tipos de laço foram selecionados (FOR, WHILE, DO-WHILE)
    foreach (['FOR', 'WHILE', 'DO-WHILE'] as $laco) {
        if (in_array($laco, $assuntos_selecionados_form)) {
            $tipos_laco_disponiveis[] = $laco;
        }
    }

    // Se "Resposta Aberta" foi selecionado como formato, usa seus subtipos
    $usar_tipos_resposta_aberta = in_array('Resposta Aberta', $formatos_selecionados_form);
    if ($usar_tipos_resposta_aberta && !empty($subtipos_resposta_aberta_selecionados_form)) {
        $tipos_questao_resposta_aberta = $subtipos_resposta_aberta_selecionados_form;
    }

    // Se nenhum tipo de laço ou subtipo de RA foi selecionado, retorna vazio
    if (empty($tipos_laco_disponiveis) && (!$usar_tipos_resposta_aberta || empty($tipos_questao_resposta_aberta))) {
        error_log("Tentativa de gerar questões sem assuntos de laço ou subtipos de resposta aberta válidos.");
        return [];
    }

    for ($i = 0; $i < $quantidade; $i++) {
        $assunto_aleatorio = null;
        if (!empty($tipos_laco_disponiveis)) {
            $assunto_aleatorio = $tipos_laco_disponiveis[array_rand($tipos_laco_disponiveis)];
        }

        $tipo_questao_aleatorio = null;
        
        // Prioriza a geração de questões de "Resposta Aberta" com seus subtipos se selecionados
        if ($usar_tipos_resposta_aberta && !empty($tipos_questao_resposta_aberta)) {
            $tipo_questao_aleatorio = $tipos_questao_resposta_aberta[array_rand($tipos_questao_resposta_aberta)];
        } else if (!empty($tipos_laco_disponiveis)) {
             // Se 'Resposta Aberta' não foi selecionada, mas há tipos de laço, assume-se que as questões de laço ainda são o foco.
             // Neste ponto, sem lógica para outros formatos (Múltipla Escolha, etc.), ainda só podemos gerar nos tipos 1-4.
             // Assumimos que se a disciplina de Lógica de Programação e seus assuntos de laço foram escolhidos,
             // e 'Resposta Aberta' não foi, a intenção é ter questões de laço, e o sistema só sabe gerar nos tipos 1-4.
             // Isso é uma limitação atual baseada no seu código original.
             $tipo_questao_aleatorio = 'TIPO4'; // Default, ou podemos fazer mais inteligente
        }


        // Garante que temos um assunto de laço e um tipo de questão válidos para continuar
        if ($assunto_aleatorio && $tipo_questao_aleatorio) {
            $questoes[] = gerar_questao_loop($assunto_aleatorio, $tipo_questao_aleatorio);
        } else {
            // Caso não seja possível gerar uma questão com as combinações atuais, adiciona uma questão de fallback ou pula.
            error_log("Não foi possível gerar uma questão válida para as combinações selecionadas.");
        }
    }
    
    // Filtra questões nulas que podem ter sido geradas se a lógica não encontrou um match
    return array_filter($questoes);
}

// Renomeada para evitar conflito com 'gerar_questao' original, e para ser mais descritiva
function gerar_questao_loop($assunto_laco, $tipo_questao_resposta_aberta) {
    // Valores aleatórios garantindo lógica
    // Para evitar divisões por zero ou loops infinitos
    $valor_inicial = rand(-10, 10);
    do {
        $valor_final = rand(-20, 20);
    } while ($valor_inicial == $valor_final); // Garante que inicial e final são diferentes para a maioria dos cenários

    $operador = determinar_operador($valor_inicial, $valor_final);
    $passo = determine_step($valor_inicial, $valor_final); // Corrigido para chamar a função correta
    
    // Ajuste para evitar loop infinito se $passo for 0
    if ($passo == 0) { 
        $passo = ($valor_inicial < $valor_final) ? 1 : -1; // Garante um passo válido
        if ($passo == 0) $passo = 1; // Fallback se inicial e final forem iguais e o rand deu 0
    }
    
    switch ($assunto_laco) {
        case 'FOR':
            return montar_questao_for($tipo_questao_resposta_aberta, $valor_inicial, $valor_final, $operador, $passo);
        case 'WHILE':
            return montar_questao_while($tipo_questao_resposta_aberta, $valor_inicial, $valor_final, $operador, $passo);
        case 'DO-WHILE': 
            return montar_questao_do_while($tipo_questao_resposta_aberta, $valor_inicial, $valor_final, $operador, $passo);
        default:
            return null;
    }
}

// ==============================================
// FUNÇÕES DE APOIO PARA GERAÇÃO DE QUESTÕES
// ==============================================

function determinar_operador($inicial, $final) {
    // Garante que o operador faça sentido com a relação inicial/final
    if ($inicial < $final) {
        $ops = ['<', '<='];
        return $ops[array_rand($ops)];
    } elseif ($inicial > $final) {
        $ops = ['>', '>='];
        return $ops[array_rand($ops)];
    } else { // $inicial == $final
        // Se inicial e final são iguais, a condição == faria o loop executar uma vez (do-while) ou 0/1 (for/while)
        // A condição != faria o loop não executar (for/while) ou executar uma vez e parar (do-while)
        $ops = ['==', '!=']; 
        return $ops[array_rand($ops)];
    }
}

// Renomeada para evitar conflito e ser mais clara
function determine_step($inicial, $final) {
    if ($inicial < $final) {
        return rand(1, 3); // Incremento positivo
    } else {
        return -rand(1, 3); // Decremento negativo
    }
}

function calcular_repeticoes($vi, $vf, $op, $passo, $is_do_while = false) {
    // Evita divisão por zero ou loops que não progridem
    if ($passo == 0) return 0; // Se o passo é zero, não há progressão, então 0 repetições (ou 1 para do-while se a condição for verdadeira na primeira iteração)

    $repeticoes = 0;
    $i = $vi;
    $max_iterations = 500; // Limite para evitar loops infinitos na simulação

    if ($is_do_while) {
        // Do-while executa pelo menos uma vez
        $repeticoes = 1;
        // Simula a primeira iteração antes de verificar a condição
        $i += $passo;
        $count = 1; // Já contou a primeira
    } else {
        $count = 0;
    }
    
    // Condição para continuar o loop de simulação
    $should_continue = false;
    switch ($op) {
        case '<':   $should_continue = ($i < $vf); break;
        case '<=':  $should_continue = ($i <= $vf); break;
        case '>':   $should_continue = ($i > $vf); break;
        case '>=':  $should_continue = ($i >= $vf); break;
        case '==':  $should_continue = ($i == $vf); break;
        case '!=':  $should_continue = ($i != $vf); break;
    }

    if ($is_do_while) {
        // Para do-while, se na primeira passada a condição já era falsa e não deveria ter entrado,
        // mas entrou por ser do-while, e agora a condição é falsa, ele para após 1.
        // Se a condição for verdadeira, ele continua a simular.
        // Já contamos a primeira, agora simula as restantes
        if ($should_continue) {
            // Continua a simulação enquanto a condição for verdadeira e dentro do limite
            while ($count < $max_iterations) {
                $prev_i = $i; // Para detectar se o valor de i não está mudando
                
                switch ($op) {
                    case '<':   if (!($i < $vf)) break 2; break;
                    case '<=':  if (!($i <= $vf)) break 2; break;
                    case '>':   if (!($i > $vf)) break 2; break;
                    case '>=':  if (!($i >= $vf)) break 2; break;
                    case '==':  if (!($i == $vf)) break 2; break;
                    case '!=':  if (!($i != $vf)) break 2; break;
                }
                
                $i += $passo;
                $count++;
                if ($i === $prev_i && $passo !== 0) { // Proteção contra loops que não progridem mas a condição é sempre true
                    break;
                }
            }
        }
        $repeticoes = $count;

    } else { // For e While
        while ($count < $max_iterations) {
            $prev_i = $i; // Para detectar se o valor de i não está mudando
            
            // Verifica a condição antes de cada iteração
            switch ($op) {
                case '<':   if (!($i < $vf)) break 2; break;
                case '<=':  if (!($i <= $vf)) break 2; break;
                case '>':   if (!($i > $vf)) break 2; break;
                case '>=':  if (!($i >= $vf)) break 2; break;
                case '==':  if (!($i == $vf)) break 2; break;
                case '!=':  if (!($i != $vf)) break 2; break;
            }
            
            $i += $passo;
            $count++;

            if ($i === $prev_i && $passo !== 0) { // Proteção contra loops que não progridem mas a condição é sempre true
                break;
            }
        }
        $repeticoes = $count;
    }
    
    return $repeticoes;
}


// ==============================================
// FUNÇÕES DE MONTAGEM DE QUESTÕES
// ==============================================

function montar_questao_for($tipo, $vi, $vf, $op, $passo) {
    $enunciado = '';
    $codigo = '';
    $resposta = '';
    
    $repeticoes = calcular_repeticoes($vi, $vf, $op, $passo, false);
    
    switch ($tipo) {
        case 'TIPO1': // Lacuna: Valor Inicial
            $enunciado = "Qual o valor inicial de `\$i` para que o laço `for` execute **" . $repeticoes . "** vezes?";
            $codigo = "for(\$i = _____; \$i $op $vf; \$i" . ($passo > 0 ? "++" : "--") . ") {<br>    echo \$i;<br>}";
            $resposta = $vi;
            break;
            
        case 'TIPO2': // Lacuna: Valor Final
            $enunciado = "Qual o valor final (na condição) para que o laço `for` execute **" . $repeticoes . "** vezes?";
            $codigo = "for(\$i = $vi; \$i $op ______; \$i" . ($passo > 0 ? "++" : "--") . ") {<br>    echo \$i;<br>}";
            $resposta = $vf;
            break;
            
        case 'TIPO3': // Lacuna: Passo
            $enunciado = "Complete a lacuna com o passo (incremento ou decremento) para que o laço `for` execute **" . $repeticoes . "** vezes?";
            $codigo = "for(\$i = $vi; \$i $op $vf; _____) {<br>    echo \$i;<br>}";
            $resposta = ($passo > 0 ? "\$i++" : "\$i--"); // Simplificado para ++ ou --
            break;
            
        case 'TIPO4': // Pede o número de repetições
            $enunciado = "Quantas repetições ocorrerão no seguinte laço `for`?";
            $codigo = "for(\$i = $vi; \$i $op $vf; \$i" . ($passo > 0 ? "++" : "--"). ") {<br>    echo \$i;<br>}";
            $resposta = $repeticoes;
            break;
    }
    
    return [
        'assunto_laco' => 'FOR',
        'tipo_questao' => $tipo,
        'enunciado' => $enunciado,
        'codigo' => $codigo,
        'resposta' => $resposta
    ];
}

function montar_questao_while($tipo, $vi, $vf, $op, $passo) {
    $enunciado = '';
    $codigo = '';
    $resposta = '';
    
    $repeticoes = calcular_repeticoes($vi, $vf, $op, $passo, false);
    
    switch ($tipo) {
        case 'TIPO1': // Lacuna: Valor Inicial
            $enunciado = "Qual o valor inicial de `\$i` para que o laço `while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = _____;<br>while(\$i $op $vf) {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>}";
            $resposta = $vi;
            break;
            
        case 'TIPO2': // Lacuna: Valor Final
            $enunciado = "Qual o valor final (na condição) para que o laço `while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = $vi;<br>while(\$i $op ______) {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>}";
            $resposta = $vf;
            break;
            
        case 'TIPO3': // Lacuna: Passo
            $enunciado = "Complete a lacuna com o passo (incremento ou decremento) para que o laço `while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = $vi;<br>while(\$i $op $vf) {<br>    echo \$i;<br>    \$i ____;<br>}";
            $resposta = ($passo > 0 ? "\$i++" : "\$i--");
            break;
            
        case 'TIPO4': // Pede o número de repetições
            $enunciado = "Quantas repetições ocorrerão no seguinte laço `while`?";
            $codigo = "\$i = $vi;<br>while(\$i $op $vf) {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>}";
            $resposta = $repeticoes;
            break;
    }
    
    return [
       'assunto_laco' => 'WHILE',
       'tipo_questao' => $tipo,
        'enunciado' => $enunciado,
        'codigo' => $codigo,
        'resposta' => $resposta
    ];
}

function montar_questao_do_while($tipo, $vi, $vf, $op, $passo) {
    $enunciado = '';
    $codigo = '';
    $resposta = '';
    
    $repeticoes = calcular_repeticoes($vi, $vf, $op, $passo, true); // Passa true para indicar do-while
    
    switch ($tipo) {
        case 'TIPO1': // Lacuna: Valor Inicial
            $enunciado = "Qual o valor inicial de `\$i` para que o laço `do-while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = _____;<br>do {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>} while (\$i $op $vf);";
            $resposta = $vi;
            break;
            
        case 'TIPO2': // Lacuna: Valor Final
            $enunciado = "Qual o valor final (na condição) para que o laço `do-while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = $vi;<br>do {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>} while (\$i $op ______);";
            $resposta = $vf;
            break;
            
        case 'TIPO3': // Lacuna: Passo
            $enunciado = "Complete a lacuna com o passo (incremento ou decremento) para que o laço `do-while` execute **" . $repeticoes . "** vezes?";
            $codigo = "\$i = $vi;<br>do {<br>    echo \$i;<br>    \$i ____;<br>} while (\$i $op $vf);";
            $resposta = ($passo > 0 ? "\$i++" : "\$i--");
            break;
            
        case 'TIPO4': // Pede o número de repetições
            $enunciado = "Quantas repetições ocorrerão no seguinte laço `do-while`?";
            $codigo = "\$i = $vi;<br>do {<br>    echo \$i;<br>    \$i" . ($passo > 0 ? "++" : "--") . ";<br>} while (\$i $op $vf);";
            $resposta = $repeticoes;
            break;
    }
    
    return [
       'assunto_laco' => 'DO-WHILE',
       'tipo_questao' => $tipo,
        'enunciado' => $enunciado,
        'codigo' => $codigo,
        'resposta' => $resposta
    ];
}


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
    array $dados_validados,
    array $turmas_info_completa,
    array $disciplina_assuntos_info_completa,
    array $configuracoes
): string {
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
    $saida .= "<div class='quebra-de-pagina'></div>"; // QUEBRA DE PÁGINA AQUI


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
    $saida .= "<h2>Ata de Presença</h2>";
    $saida .= "<pre>";
    $saida .= sanitizar_dados($cabecalho_template['escola']) . "<br>";
    $saida .= sanitizar_dados($cabecalho_template['curso']) . "<br>";
    $saida .= "Professor: " . sanitizar_dados($cabecalho_template['professor']) . "<br>";
    $saida .= "Turma: " . sanitizar_dados($nome_turma) . "<br>";
    $saida .= "Disciplina: " . sanitizar_dados($nome_disciplina) . "<br>";
    $saida .= "Data: " . $data_prova_str . "<br>";
    $saida .= "</pre>";
    $saida .= "<table border='1' style='width:100%; border-collapse: collapse;'>";
    $saida .= "<thead><tr><th>Nº</th><th>Nome Completo</th><th>Matrícula</th><th>Assinatura</th><th>Data</th></tr></thead>";
    $saida .= "<tbody>";
    
    // Certifica-se de que $turma_completa_obj foi encontrado
    if ($turma_completa_obj && !empty($turma_completa_obj['estudantes'])) {
        // Ordena os estudantes pelo nome completo para a ata
        usort($turma_completa_obj['estudantes'], function($a, $b) {
            return strcmp($a['nome_completo'], $b['nome_completo']);
        });

        foreach ($turma_completa_obj['estudantes'] as $idx => $estudante_turma) {
            $saida .= "<tr>";
            $saida .= "<td>" . ($idx + 1) . "</td>";
            $saida .= "<td>" . htmlspecialchars($estudante_turma['nome_completo']) . "</td>";
            $saida .= "<td>" . htmlspecialchars($estudante_turma['matricula']) . "</td>";
            $saida .= "<td style='width: 30%;'>&nbsp;</td>"; // Coluna para assinatura
            $saida .= "<td style='width: 15%;'>&nbsp;</td>"; // Coluna para data
            $saida .= "</tr>";
        }
    } else {
        $saida .= "<tr><td colspan='5'>Nenhum estudante encontrado para esta turma.</td></tr>";
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
                $saida .= "Prova Nº" . ($p_idx + 1) . " (" . htmlspecialchars($estudantes_selecionados_obj[$p_idx]['nome_completo']) . ")";
            }
            $saida .= ":</b> ";
            foreach ($respostas_prova as $r_idx => $resposta_str) {
                $saida .= "[" . $resposta_str . "]";
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