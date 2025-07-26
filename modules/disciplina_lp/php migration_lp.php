<?php
// config.php - Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'toor');
define('DB_NAME', 'provas');
// migration_lp.php - Script de migração
//require_once 'core/config.php';

// Conectar ao MySQL
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// 1. Verificar se a disciplina já existe
function getDisciplinaId($pdo, $nome) {
    $stmt = $pdo->prepare("SELECT id FROM disciplinas WHERE nome = ?");
    $stmt->execute([$nome]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : false;
}

// 2. Inserir disciplina se não existir
function insertDisciplina($pdo, $nome) {
    $stmt = $pdo->prepare("INSERT INTO disciplinas (nome) VALUES (?)");
    $stmt->execute([$nome]);
    return $pdo->lastInsertId();
}

// 3. Processar o arquivo JSON
function processarQuestoes($pdo, $jsonFile) {
    if (!file_exists($jsonFile)) {
        die("Arquivo JSON não encontrado: $jsonFile");
    }

    $json = file_get_contents($jsonFile);
    $questoes = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Erro ao decodificar JSON: " . json_last_error_msg());
    }

    // Verificar/inserir disciplina
    $disciplinaNome = 'Lógica de Programação';
    $disciplinaId = getDisciplinaId($pdo, $disciplinaNome);
    
    if (!$disciplinaId) {
        $disciplinaId = insertDisciplina($pdo, $disciplinaNome);
        echo "Disciplina '{$disciplinaNome}' inserida com ID: {$disciplinaId}\n";
    }

    // 4. Inserir questões e alternativas
    $questoesInseridas = 0;
    $alternativasInseridas = 0;
    
    foreach ($questoes as $questao) {
        try {
            // Inserir questão principal
            $stmt = $pdo->prepare("
                INSERT INTO questoes (
                    disciplina_id, 
                    tipo, 
                    enunciado, 
                    dificuldade, 
                    topico, 
                    status,
                    codigo,
                    resposta_correta
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $tipo = ($questao['tipo'] == 'TIPO4') ? 'multipla_escolha' : 'resposta_aberta';
            
            $stmt->execute([
                $disciplinaId,
                $tipo,
                $questao['enunciado'],
                $questao['dificuldade'],
                $questao['assunto'],
                'aprovada',
                $questao['codigo'] ?? null,
                $questao['resposta'] ?? null
            ]);
            
            $questaoId = $pdo->lastInsertId();
            $questoesInseridas++;
            
            // Se for múltipla escolha (TIPO4), inserir alternativas
            if ($questao['tipo'] == 'TIPO4') {
                $alternativas = gerarAlternativas($questao['resposta']);
                
                foreach ($alternativas as $alt) {
                    $stmt = $pdo->prepare("
                        INSERT INTO alternativas (questao_id, texto, correta)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$questaoId, $alt['texto'], $alt['correta']]);
                    $alternativasInseridas++;
                }
            }
            
        } catch (PDOException $e) {
            echo "Erro ao inserir questão: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    echo "Migração concluída!\n";
    echo "Questões inseridas: {$questoesInseridas}\n";
    echo "Alternativas inseridas: {$alternativasInseridas}\n";
}

// 5. Gerar alternativas plausíveis para questões TIPO4
function gerarAlternativas($respostaCorreta) {
    $alternativas = [];
    
    // Adiciona a resposta correta
    $alternativas[] = ['texto' => $respostaCorreta, 'correta' => true];
    
    // Gera alternativas incorretas baseadas na resposta correta
    if (is_numeric($respostaCorreta)) {
        // Para respostas numéricas (contagem de repetições)
        $variacoes = [-2, -1, +1, +2];
        foreach ($variacoes as $var) {
            $valor = (int)$respostaCorreta + $var;
            if ($valor > 0) {
                $alternativas[] = ['texto' => strval($valor), 'correta' => false];
            }
        }
    } else {
        // Para respostas de código (valor inicial, passo, etc.)
        $padroes = [
            '0', '1', '-1', 
            ($respostaCorreta == '$i++') ? '$i--' : '$i++',
            'null', 'true', 'false'
        ];
        
        foreach ($padroes as $padrao) {
            if ($padrao !== $respostaCorreta && count($alternativas) < 4) {
                $alternativas[] = ['texto' => $padrao, 'correta' => false];
            }
        }
    }
    
    // Garante pelo menos 3 alternativas
    while (count($alternativas) < 3) {
        $alternativas[] = ['texto' => 'Nenhuma das anteriores', 'correta' => false];
    }
    
    // Embaralha as alternativas
    shuffle($alternativas);
    
    return array_slice($alternativas, 0, 4); // Retorna no máximo 4 alternativas
}

// Executar migração
processarQuestoes($pdo, 'questoes_lp_2025-07-21_19-45-31.json');
?>