<?php
// Configurações
$tipos_questao = ['TIPO1', 'TIPO2', 'TIPO3', 'TIPO4'];
$loops = ['FOR', 'WHILE', 'DO-WHILE'];
$questoes = [];

// Gerar 20 questões para cada tipo de loop
foreach ($loops as $loop) {
    for ($i = 0; $i < 20; $i++) {
        $tipo = $tipos_questao[array_rand($tipos_questao)];
        $vi = rand(-10, 10);
        $vf = rand(-10, 10);
        $op = determinar_operador($vi, $vf);
        $passo = determine_step($vi, $vf);
        
        $questoes[] = [
            'disciplina' => 'Lógica de Programação',
            'assunto' => $loop,
            'tipo' => $tipo,
            'dificuldade' => ['facil', 'medio', 'dificil'][rand(0, 2)],
            'enunciado' => montar_enunciado($loop, $tipo, $vi, $vf, $op, $passo),
            'codigo' => montar_codigo($loop, $tipo, $vi, $vf, $op, $passo),
            'resposta' => calcular_resposta($loop, $tipo, $vi, $vf, $op, $passo)
        ];
    }
}

function calcular_repeticoes($vi, $vf, $op, $passo, $is_do_while = false) {
    if ($passo == 0) return $is_do_while ? 1 : 0; // Passo zero inválido
    
    $count = 0;
    $i = $vi;
    $max_iterations = 1000;

    // Simulação do loop
    do {
        if (!$is_do_while && $count == 0) {
            // FOR/WHILE: Verifica condição antes da 1ª iteração
            if (!verificar_condicao($i, $op, $vf)) break;
        }

        $count++;
        $i += $passo;

        // Verifica condição após incremento
        if (!verificar_condicao($i, $op, $vf)) break;

    } while ($count < $max_iterations);

    return $count;
}

function verificar_condicao($i, $op, $vf) {
    switch ($op) {
        case '<': return $i < $vf;
        case '<=': return $i <= $vf;
        case '>': return $i > $vf;
        case '>=': return $i >= $vf;
        case '==': return $i == $vf;
        case '!=': return $i != $vf;
        default: return false;
    }
}

function determine_step($inicial, $final) {
    if ($inicial == $final) {
        return rand(1, 3); // Passo padrão se valores iguais
    }
    $passo = abs($final - $inicial) / rand(1, 3); // Passo proporcional à diferença
    return ($inicial < $final) ? $passo : -$passo;
}

function determinar_operador($inicial, $final) {
    if ($inicial < $final) {
        return ['<', '<='][rand(0, 1)]; // Operadores para incremento
    } elseif ($inicial > $final) {
        return ['>', '>='][rand(0, 1)]; // Operadores para decremento
    } else {
        return '!='; // Evita loops infinitos se $passo != 0
    }
}

/**
 * Monta o enunciado da questão com base nos parâmetros
 * 
 * @param string $loop Tipo de loop (FOR, WHILE, DO-WHILE)
 * @param string $tipo Tipo de questão (TIPO1, TIPO2, TIPO3, TIPO4)
 * @param int $vi Valor inicial
 * @param int $vf Valor final
 * @param string $op Operador de comparação
 * @param int $passo Passo do incremento/decremento
 * @return string Enunciado formatado
 */
function montar_enunciado($loop, $tipo, $vi, $vf, $op, $passo) {
    $repeticoes = calcular_repeticoes($vi, $vf, $op, $passo, $loop === 'DO-WHILE');
    
    switch ($tipo) {
        case 'TIPO1': // Valor inicial
            return "Qual deve ser o valor inicial de `\$i` para que o laço $loop execute exatamente $repeticoes vezes?";
            
        case 'TIPO2': // Valor final
            return "Qual deve ser o valor final na condição para que o laço $loop execute exatamente $repeticoes vezes?";
            
        case 'TIPO3': // Passo/incremento
            $direcao = ($passo > 0) ? "incremento" : "decremento";
            return "Complete o $direcao do laço $loop para que ele execute exatamente $repeticoes vezes";
            
        case 'TIPO4': // Número de repetições
            return "Quantas vezes o seguinte laço $loop será executado?";
            
        default:
            return "Complete o código abaixo para que funcione corretamente:";
    }
}

/**
 * Monta o código da questão com lacunas conforme o tipo
 * 
 * @param string $loop Tipo de loop (FOR, WHILE, DO-WHILE)
 * @param string $tipo Tipo de questão (TIPO1, TIPO2, TIPO3, TIPO4)
 * @param int $vi Valor inicial
 * @param int $vf Valor final
 * @param string $op Operador de comparação
 * @param int $passo Passo do incremento/decremento
 * @return string Código com lacunas
 */
function montar_codigo($loop, $tipo, $vi, $vf, $op, $passo) {
    $simbolo_passo = ($passo > 0) ? '++' : '--';
    $texto_passo = ($passo > 0) ? '$i++' : '$i--';
    
    switch ($loop) {
        case 'FOR':
            switch ($tipo) {
                case 'TIPO1': // Falta valor inicial
                    return "for(\$i = ______; \$i $op $vf; \$i$simbolo_passo) {\n    echo \$i;\n}";
                    
                case 'TIPO2': // Falta valor final
                    return "for(\$i = $vi; \$i $op ______; \$i$simbolo_passo) {\n    echo \$i;\n}";
                    
                case 'TIPO3': // Falta passo
                    return "for(\$i = $vi; \$i $op $vf; ______) {\n    echo \$i;\n}";
                    
                case 'TIPO4': // Completo (pergunta repetições)
                    return "for(\$i = $vi; \$i $op $vf; \$i$simbolo_passo) {\n    echo \$i;\n}";
            }
            break;
            
        case 'WHILE':
            switch ($tipo) {
                case 'TIPO1': // Falta valor inicial
                    return "\$i = ______;\nwhile (\$i $op $vf) {\n    echo \$i;\n    \$i$simbolo_passo;\n}";
                    
                case 'TIPO2': // Falta valor final
                    return "\$i = $vi;\nwhile (\$i $op ______) {\n    echo \$i;\n    \$i$simbolo_passo;\n}";
                    
                case 'TIPO3': // Falta passo
                    return "\$i = $vi;\nwhile (\$i $op $vf) {\n    echo \$i;\n    ______;\n}";
                    
                case 'TIPO4': // Completo
                    return "\$i = $vi;\nwhile (\$i $op $vf) {\n    echo \$i;\n    \$i$simbolo_passo;\n}";
            }
            break;
            
        case 'DO-WHILE':
            switch ($tipo) {
                case 'TIPO1': // Falta valor inicial
                    return "\$i = ______;\ndo {\n    echo \$i;\n    \$i$simbolo_passo;\n} while (\$i $op $vf);";
                    
                case 'TIPO2': // Falta valor final
                    return "\$i = $vi;\ndo {\n    echo \$i;\n    \$i$simbolo_passo;\n} while (\$i $op ______);";
                    
                case 'TIPO3': // Falta passo
                    return "\$i = $vi;\ndo {\n    echo \$i;\n    ______;\n} while (\$i $op $vf);";
                    
                case 'TIPO4': // Completo
                    return "\$i = $vi;\ndo {\n    echo \$i;\n    \$i$simbolo_passo;\n} while (\$i $op $vf);";
            }
            break;
    }
    
    return "Código não disponível";
}

/**
 * Função auxiliar para determinar a resposta correta
 */
function calcular_resposta($loop, $tipo, $vi, $vf, $op, $passo) {
    $repeticoes = calcular_repeticoes($vi, $vf, $op, $passo, $loop === 'DO-WHILE');
    
    switch ($tipo) {
        case 'TIPO1': // Valor inicial
            return $vi;
            
        case 'TIPO2': // Valor final
            return $vf;
            
        case 'TIPO3': // Passo
            return ($passo > 0) ? '$i++' : '$i--';
            
        case 'TIPO4': // Número de repetições
            return $repeticoes;
            
        default:
            return '';
    }
}

// Salvar em JSON (para migração posterior)
//file_put_contents('questoes_lp.json', json_encode($questoes, JSON_PRETTY_PRINT));

$dataHora = date('Y-m-d_H-i-s'); // Formato: 2023-11-15_14-30-22
$nomeArquivo = "questoes_lp_{$dataHora}.json";
$caminho = "/home/aaf/Documentos/ETE/app_prova/{$nomeArquivo}";  // Local alternativo

file_put_contents($caminho, $json);
file_put_contents($caminho, json_encode($questoes, JSON_PRETTY_PRINT));

$arquivo = $nomeArquivo;

// Verifica se a pasta é gravável
if (!is_writable(dirname($caminho))) {
    die("ERRO: A pasta " . dirname($caminho) . " não tem permissão de escrita.
         Soluções:
         1. Clique com botão direito na pasta, selecione Propriedades > Segurança
         2. Adicione permissão de 'Controle total' para 'Usuários'
         3. Execute o XAMPP como administrador");
}

if (file_put_contents($caminho) === false) {
    die("Falha ao escrever no arquivo. Tente:
        1. Salvar em C:/temp/questoes_lp.json
        2. Verificar se o antivírus não está bloqueando");
}
?>