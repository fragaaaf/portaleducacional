<?php
// modules/discipline_bd/bd_logic.php

require_once __DIR__ . '/bd_data.php'; // Inclui os dados de enunciados de BD
require_once __DIR__ . '/../../core/utils.php'; // Para sanitizar_dados se necessário internamente
// Se gerarCorpoProvaBancoDeDados usar html_helpers, também incluir:
// require_once __DIR__ . '/../../core/html_helpers.php';

/**
 * Gera uma prova aleatória de Banco de Dados, garantindo um enunciado de cada cardinalidade.
 * Acessa a variável global $enunciadosBD definida em bd_data.php.
 *
 * @return array Um array associativo com os enunciados selecionados para cada cardinalidade.
 */
function gerarProvaBD(): array {
    global $enunciadosBD; // Agora $enunciadosBD vem de bd_data.php
    return [
        '1_1' => $enunciadosBD['1_1'][array_rand($enunciadosBD['1_1'])],
        '1_N' => $enunciadosBD['1_N'][array_rand($enunciadosBD['1_N'])],
        'N_N' => $enunciadosBD['N_N'][array_rand($enunciadosBD['N_N'])]
    ];
}

/**
 * Gera o corpo HTML da prova para a disciplina de Banco de Dados.
 *
 * @param array $provaGerada Array com os enunciados já selecionados para a prova.
 * @return string O HTML com as questões e orientações específicas de Banco de Dados.
 */
function gerarCorpoProvaBancoDeDados(array $provaGerada): string {
    $html_corpo_bd = '';

    // Orientações específicas para Banco de Dados
    $html_corpo_bd .= '<div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; page-break-inside: avoid;">';
    $html_corpo_bd .= '<h3 style="margin-top: 0; color: #2c3e50;">Orientações:</h3>';
    $html_corpo_bd .= '<ol style="margin-bottom: 0;">';
    $html_corpo_bd .= '<li>Baseado nos enunciados das questões, desenhe o <strong>DER (Diagrama Entidade-Relacionamento) </strong> com os atributos citados, destacando o atributo chave</li>';
    $html_corpo_bd .= '<li>Depois desenhe as <strong>tabelas</strong> baseadas no DER feito por você. Crie os relacionamentos nas tabelas de acordo com cada cardinalidade</li>';
    $html_corpo_bd .= '<li>Identifique claramente as <strong>chaves primárias com [PK]</strong> e <strong>estrangeiras com [FK]</strong></li>';
    $html_corpo_bd .= '<li>Represente corretamente as <strong>cardinalidades</strong> (1:1, 1:N, N:N) no DER e faça a conexão das tabelas com as colunas da chave primária e chave estrangeira</li>';
    $html_corpo_bd .= '</ol>';
    $html_corpo_bd .= '</div>';

    // Questões de Banco de Dados
    $html_corpo_bd .= '<div class="enunciado">';
    $html_corpo_bd .= '<span class="cardinalidade">Cardinalidade 1:1</span><br>';
    $html_corpo_bd .= '<span class="numero-enunciado">1.</span> ' . htmlspecialchars($provaGerada['1_1']);
    $html_corpo_bd .= '</div>';

    $html_corpo_bd .= '<div class="enunciado">';
    $html_corpo_bd .= '<span class="cardinalidade">Cardinalidade 1:N</span><br>';
    $html_corpo_bd .= '<span class="numero-enunciado">2.</span> ' . htmlspecialchars($provaGerada['1_N']);
    $html_corpo_bd .= '</div>';

    $html_corpo_bd .= '<div class="enunciado">';
    $html_corpo_bd .= '<span class="cardinalidade">Cardinalidade N:N</span><br>';
    $html_corpo_bd .= '<span class="numero-enunciado">3.</span> ' . htmlspecialchars($provaGerada['N_N']);
    $html_corpo_bd .= '</div>';

    return $html_corpo_bd;
}