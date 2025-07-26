<?php

// =================================================================
// PASSO 1: DEFINIÇÃO DOS CONJUNTOS DE ENTIDADES
// =================================================================
// Cada entidade é um array associativo com 3 atributos.

$conjuntoA = [
    ['id' => 'id_colaborador', 'nome' => 'Colaborador', 'atributo_extra' => 'data_admissao'],
    ['id' => 'id_departamento', 'nome' => 'Departamento', 'atributo_extra' => 'sigla_depto'],
    ['id' => 'id_produto', 'nome' => 'Produto', 'atributo_extra' => 'preco_unitario'],
    ['id' => 'id_fornecedor', 'nome' => 'Fornecedor', 'atributo_extra' => 'cidade_sede'],
    ['id' => 'id_cliente', 'nome' => 'Cliente', 'atributo_extra' => 'limite_credito'],
    ['id' => 'id_nota_fiscal', 'nome' => 'Nota Fiscal', 'atributo_extra' => 'data_emissao'],
    ['id' => 'id_motorista', 'nome' => 'Motorista', 'atributo_extra' => 'numero_cnh'],
    ['id' => 'id_veiculo', 'nome' => 'Veiculo', 'atributo_extra' => 'placa'],
    ['id' => 'id_aluno', 'nome' => 'Aluno', 'atributo_extra' => 'matricula'],
    ['id' => 'id_curso', 'nome' => 'Curso', 'atributo_extra' => 'carga_horaria'],
];

$conjuntoB = [
    ['id' => 'id_cargo', 'nome' => 'Cargo', 'atributo_extra' => 'salario_base'],
    ['id' => 'id_projeto', 'nome' => 'Projeto', 'atributo_extra' => 'data_inicio'],
    ['id' => 'id_categoria', 'nome' => 'Categoria', 'atributo_extra' => 'descricao_cat'],
    ['id' => 'id_pedido', 'nome' => 'Pedido', 'atributo_extra' => 'valor_total'],
    ['id' => 'id_endereco', 'nome' => 'Endereco', 'atributo_extra' => 'cep'],
    ['id' => 'id_item_nf', 'nome' => 'Item da Nota Fiscal', 'atributo_extra' => 'quantidade'],
    ['id' => 'id_viagem', 'nome' => 'Viagem', 'atributo_extra' => 'destino'],
    ['id' => 'id_seguro', 'nome' => 'Seguro', 'atributo_extra' => 'valor_apolice'],
    ['id' => 'id_disciplina', 'nome' => 'Disciplina', 'atributo_extra' => 'nome_professor'],
    ['id' => 'id_turma', 'nome' => 'Turma', 'atributo_extra' => 'sala_aula'],
];


// =================================================================
// PASSO 2: MAPA DE RELACIONAMENTOS, CARDINALIDADES E ENUNCIADOS
// =================================================================
// Chave do mapa: "nome_entidade_A-nome_entidade_B"
// Valor: um array com 'cardinalidade' e um array de 'enunciados'.

$mapaRelacionamentos = [
    // Exemplo 1: Relacionamento 1:1
    'Motorista-Veiculo' => [
        'cardinalidade' => '1:1',
        'enunciados' => [
            "Uma empresa de logística precisa controlar sua frota. Foi definido que cada **[ENTIDADE_A]** ('[ID_A]', '[ATTR_A]') só pode dirigir um **[ENTIDADE_B]** ('[ID_B]', '[ATTR_B]') por vez, e cada **[ENTIDADE_B]** é exclusivo para um **[ENTIDADE_A]**. Como você representaria essa relação em um modelo lógico?",
            "No sistema de controle de frotas, a regra de negócio estipula uma associação exclusiva e obrigatória entre um **[ENTIDADE_A]** e um **[ENTIDADE_B]**. Modele as tabelas para representar que um **[ENTIDADE_A]** está ligado a apenas um **[ENTIDADE_B]** e vice-versa.",
            "Para gerenciar as alocações, um **[ENTIDADE_A]** deve ser associado a um único **[ENTIDADE_B]**. Da mesma forma, um **[ENTIDADE_B]** só pode ter um **[ENTIDADE_A]** responsável. Desenhe o esquema de banco de dados para essa regra de cardinalidade um-para-um.",
            "Considerando as entidades **[ENTIDADE_A]** e **[ENTIDADE_B]**, crie o modelo lógico que garanta que um registro de **[ENTIDADE_A]** se relacione com no máximo um registro de **[ENTIDADE_B]**, e que um registro de **[ENTIDADE_B]** se relacione com no máximo um de **[ENTIDADE_A]**."
        ]
    ],
    // Exemplo 2: Relacionamento 1:N
    'Departamento-Colaborador' => [
        'cardinalidade' => '1:N',
        'enunciados' => [
            "Em uma empresa, um **[ENTIDADE_B]** ('[ID_B]', '[ATTR_B]') pode pertencer a apenas um **[ENTIDADE_A]** ('[ID_A]', '[ATTR_A]'), mas um **[ENTIDADE_A]** pode ter vários **[ENTIDADE_B]**s. Como você estruturaria as tabelas para refletir essa hierarquia?",
            "Modele o banco de dados para o sistema de RH da empresa. A regra principal é que um **[ENTIDADE_A]** agrupa múltiplos **[ENTIDADE_B]**s, mas cada **[ENTIDADE_B]** está lotado em um único **[ENTIDADE_A]**. Apresente o modelo lógico para este cenário.",
            "Considerando que um **[ENTIDADE_A]** possui um conjunto de **[ENTIDADE_B]**s e que cada **[ENTIDADE_B]** está vinculado a um e somente um **[ENTIDADE_A]**, desenhe as tabelas e seus relacionamentos.",
            "Desenvolva o esquema lógico para as entidades **[ENTIDADE_A]** e **[ENTIDADE_B]**. O sistema deve garantir que um **[ENTIDADE_B]** não pode existir sem estar associado a um **[ENTIDADE_A]**, e um **[ENTIDADE_A]** pode ter de zero a muitos **[ENTIDADE_B]**s."
        ]
    ],
    // Exemplo 3: Relacionamento N:N
    'Aluno-Disciplina' => [
        'cardinalidade' => 'N:N',
        'enunciados' => [
            "Num sistema acadêmico, um **[ENTIDADE_A]** ('[ID_A]', '[ATTR_A]') pode se inscrever em várias **[ENTIDADE_B]**s ('[ID_B]', '[ATTR_B]'), e uma **[ENTIDADE_B]** pode ter vários **[ENTIDADE_A]**s matriculados. Qual é a estrutura de tabelas correta para representar essa associação?",
            "É preciso modelar a relação entre **[ENTIDADE_A]** e **[ENTIDADE_B]**. Sabendo que um **[ENTIDADE_A]** cursa múltiplas **[ENTIDADE_B]**s ao longo do semestre e uma **[ENTIDADE_B]** tem uma lista com múltiplos **[ENTIDADE_A]**s, como você implementaria isso no banco de dados?",
            "Desenhe o modelo lógico para um cenário onde a relação entre **[ENTIDADE_A]** e **[ENTIDADE_B]** é de muitos-para-muitos. Um **[ENTIDADE_A]** pode estar em várias **[ENTIDADE_B]**s, e uma **[ENTIDADE_B]** é composta por vários **[ENTIDADE_A]**s.",
            "Crie o esquema de banco de dados para a secretaria de uma faculdade. O sistema deve permitir que um **[ENTIDADE_A]** se matricule em 'N' **[ENTIDADE_B]**s e que uma **[ENTIDADE_B]** tenha 'N' **[ENTIDADE_A]**s em sua lista de chamada. Apresente as tabelas necessárias."
        ]
    ],
];


// =================================================================
// PASSO 3: LÓGICA PRINCIPAL DO SCRIPT
// =================================================================

function gerarGabarito($entidadeA, $entidadeB, $cardinalidade) {
    $nomeA = $entidadeA['nome'];
    $idA = $entidadeA['id'];
    $attrA = $entidadeA['atributo_extra'];

    $nomeB = $entidadeB['nome'];
    $idB = $entidadeB['id'];
    $attrB = $entidadeB['atributo_extra'];

    $output = "-------------------- GABARITO --------------------<br>";

    switch ($cardinalidade) {
        case '1:1':
            $output .= ">> Cardinalidade 1:1. A chave estrangeira (FK) pode ficar em qualquer uma das tabelas.<br>";
            $output .= ">> Exemplo com a FK em $nomeB:<br>";
            $output .= "Tabela: $nomeA<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idA (PK)          |<br>";
            $output .= "| $attrA             |<br>";
            $output .= "+--------------------+<br>";
            
            $output .= "Tabela: $nomeB<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idB (PK)          |<br>";
            $output .= "| $attrB             |<br>";
            $output .= "| $idA (FK)          |<br>";
            $output .= "+--------------------+<br>";
            break;

        case '1:N':
            $output .= ">> Cardinalidade 1:N. A chave estrangeira (FK) fica na tabela do lado 'N' ($nomeB).<br>";
            $output .= "Tabela: $nomeA (Lado 1)<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idA (PK)          |<br>";
            $output .= "| $attrA             |<br>";
            $output .= "+--------------------+<br>";
            
            $output .= "Tabela: $nomeB (Lado N)<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idB (PK)          |<br>";
            $output .= "| $attrB             |<br>";
            $output .= "| $idA (FK)          |<br>";
            $output .= "+--------------------+<br>";
            break;

        case 'N:N':
            $nomeTabelaAssociativa = $nomeA . '_' . $nomeB;
            $output .= ">> Cardinalidade N:N. É necessário criar uma terceira tabela (associativa).<br>";
            $output .= "Tabela: $nome<br><br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idA (PK)          |<br>";
            $output .= "| $attrA             |<br>";
            $output .= "+--------------------+<br>";

            $output .= "Tabela: $nomeB<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idB (PK)          |<br>";
            $output .= "| $attrB             |<br>";
            $output .= "+--------------------+<br>";

            $output .= "Tabela Associativa: $nomeTabelaAssociativa<br>";
            $output .= "+--------------------+<br>";
            $output .= "| $idA (PK, FK)      |<br>";
            $output .= "| $idB (PK, FK)      |<br>";
            $output .= "+--------------------+<br>";
            break;
    }
    return $output;
}

// --- Início da Execução ---

// Escolhe aleatoriamente um dos pares de exemplo definidos no mapa. [1, 3, 4, 5, 6]
$chavesDoMapa = array_keys($mapaRelacionamentos);
$chaveSorteada = $chavesDoMapa[array_rand($chavesDoMapa)];

// Separa os nomes das entidades da chave
list($nomeEntidadeA, $nomeEntidadeB) = explode('-', $chaveSorteada);

// Encontra as entidades completas nos conjuntos originais
$entidadeA = null;
$entidadeB = null;

foreach ($conjuntoA as $ent) {
    if ($ent['nome'] === $nomeEntidadeA) {
        $entidadeA = $ent;
        break;
    }
}
// Tratamento especial para o par Departamento-Colaborador (ordem invertida)
if ($nomeEntidadeA === 'Departamento') {
     foreach ($conjuntoA as $ent) {
        if ($ent['nome'] === 'Colaborador') {
            $entidadeB = $ent;
            break;
        }
    }
} else {
    foreach ($conjuntoB as $ent) {
        if ($ent['nome'] === $nomeEntidadeB) {
            $entidadeB = $ent;
            break;
        }
    }
}


if (!$entidadeA || !$entidadeB) {
    echo "Erro: Não foi possível encontrar as entidades para a chave '$chaveSorteada'.";
    exit;
}

// Pega os dados do relacionamento (cardinalidade e enunciados)
$relacionamento = $mapaRelacionamentos[$chaveSorteada];
$cardinalidade = $relacionamento['cardinalidade'];
$enunciados = $relacionamento['enunciados'];

// Escolhe um dos 4 enunciados aleatoriamente
$enunciadoEscolhido = $enunciados[array_rand($enunciados)];

// Substitui os placeholders no enunciado
$enunciadoFinal = str_replace(
    ['[ENTIDADE_A]', '[ID_A]', '[ATTR_A]', '[ENTIDADE_B]', '[ID_B]', '[ATTR_B]'],
    [$entidadeA['nome'], $entidadeA['id'], $entidadeA['atributo_extra'], $entidadeB['nome'], $entidadeB['id'], $entidadeB['atributo_extra']],
    $enunciadoEscolhido
);

// Gera o gabarito
$gabarito = gerarGabarito($entidadeA, $entidadeB, $cardinalidade);

// Imprime o resultado final
echo "===================================================<br>";
echo "QUESTÃO GERADA<br>";
echo "===================================================<br>";
echo $enunciadoFinal . "<br>";
echo $gabarito;
echo "<br>===================================================<br>";

?>
