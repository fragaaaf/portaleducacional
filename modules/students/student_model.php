<?php
// modules/students/student_model.php

require_once __DIR__ . '/../../core/utils.php'; // Para carregar_json

/**
 * Retorna a lista de estudantes de uma turma específica, ou todos os estudantes se for 'por_turma'.
 *
 * @param string $turma_id O ID da turma selecionada.
 * @param array $modo_estudante 'por_turma' ou IDs de estudantes individuais.
 * @param array $turmas_info_completa Informações completas de turmas e estudantes carregadas de turmas.json.
 * @return array Um array de objetos de estudantes.
 */
function get_estudantes_por_turma(string $turma_id, array $modo_estudante, array $turmas_info_completa): array {
    $estudantes_selecionados_obj = [];

    if (isset($modo_estudante['modo_estudante']) && $modo_estudante['modo_estudante'] === 'por_turma') {
        // Caso seja para gerar prova para todos da turma
        if (isset($turmas_info_completa[$turma_id]['estudantes'])) {
            $estudantes_selecionados_obj = $turmas_info_completa[$turma_id]['estudantes'];
        }
    } elseif (isset($modo_estudante['estudantes']) && is_array($modo_estudante['estudantes'])) {
        // Caso sejam estudantes selecionados individualmente
        $estudantes_da_turma = $turmas_info_completa[$turma_id]['estudantes'] ?? [];
        foreach ($modo_estudante['estudantes'] as $estudante_id_selecionado) {
            foreach ($estudantes_da_turma as $estudante) {
                if (isset($estudante['id']) && $estudante['id'] == $estudante_id_selecionado) {
                    $estudantes_selecionados_obj[] = $estudante;
                    break;
                }
            }
        }
    }
    return $estudantes_selecionados_obj;
}