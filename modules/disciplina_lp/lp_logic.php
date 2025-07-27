<?php
// modules/discipline_lp/lp_logic.php

require_once __DIR__ . '/../../core/utils.php'; // Para sanitizar_dados e htmlspecialchars


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
    int $quantidade,  // $num_questoes,
    array $assuntos_selecionados_form, // $assuntos_laco_para_gerar,
    array $formatos_selecionados_form,  //  $formatos_selecionados_form,
    array $subtipos_resposta_aberta_selecionados_form // $subtipos_resposta_aberta_selecionados_form
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
        
        // Prioriza a geração de questões de "Resposta Aberta" com seus subtipos se selected
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



?>