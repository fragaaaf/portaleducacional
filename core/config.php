<?php
// core/config.php


// Configurações gerais da aplicação
return [
    'app_name' => 'Gerador de Provas Inteligente',
    'default_school_name' => 'Escola Técnica Estadual de Palmares',
    'default_course_name' => 'TDS',
    // Caminhos para os arquivos JSON de dados
    'data_files' => [
        'config' => __DIR__ . '/../data/config.json', // Caminho relativo à raiz do projeto
        'turmas' => __DIR__ . '/../data/turmas.json',
        'disciplina_assuntos' => __DIR__ . '/../data/disciplina_assuntos.json',
        'formato_perguntas' => __DIR__ . '/../data/formato_perguntas.json'
    ],
    // Outras configurações futuras...
];