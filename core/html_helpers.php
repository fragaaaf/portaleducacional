<?php
// core/html_helpers.php

/**
 * Gera o HTML para os dados do cabeçalho da prova (professor, data, N° ATA para o layout padrão).
 * Esta função será usada por disciplinas que não sejam "Banco de Dados", que tem seu próprio layout.
 *
 * @param string $professor Nome do professor.
 * @param string $data Data da prova.
 * @param string $numero_ata Número da ATA.
 * @param string $disciplina Nome da disciplina.
 * @return string O HTML formatado.
 */
function get_dados_cabecalho(string $professor, string $data, string $numero_ata, string $disciplina): string {
    $html = '<div class="dados-prova">';
    $html .= '<h1>Prova de ' . htmlspecialchars($disciplina) . '</h1>';
    $html .= '<table>';
    $html .= '<tr><td>Professor:</td><td>' . htmlspecialchars($professor) . '</td></tr>';
    $html .= '<tr><td>Data:</td><td>' . htmlspecialchars($data) . '</td></tr>';
    $html .= '<tr><td>N° ATA:</td><td>' . htmlspecialchars($numero_ata) . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    return $html;
}