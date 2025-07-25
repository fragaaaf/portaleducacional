<?php
$enunciados = [
    '1_1' => [
        // Enunciados existentes (7)
       /* "Cada VEÍCULO possui exatamente um MOTOR específico, e cada motor é instalado em apenas um veículo. Atributos: Veículo (placa [PK], modelo); Motor (num_serie [PK], potência)",
        "Cada PACIENTE tem um único PRONTUÁRIO eletrônico, e cada prontuário está vinculado a apenas um paciente. Atributos: Paciente (cpf [PK], nome); Prontuário (id [PK], data_criação)",
        "Cada ALUNO residente tem direito a um único ARMÁRIO, e cada armário é designado para apenas um aluno. Atributos: Aluno (matricula [PK], curso); Armário (numero [PK], localização)",
        "Um FUNCIONÁRIO possui exatamente um CONTRATO ativo, e cada contrato pertence a apenas um funcionário. Atributos:Funcionário (id_func [PK], nome, cargo); Contrato (id_contrato [PK], data_inicio, salario)",
        "Um VEÍCULO possui uma única PLACA de identificação, e cada placa está vinculada a apenas um veículo.Atributos: Veículo (id_veiculo [PK], modelo, ano); Placa (id_placa [PK], numero, estado)",
        "Cada FUNCIONÁRIO possui exatamente um CRACHÁ, e cada crachá pertence a apenas um funcionário. Atributos: Funcionario (id_funcionario [PK], nome); Cracha (id_cracha [PK], cor)",
        "Cada PESSOA possui um único CPF, e cada CPF está vinculado a apenas uma pessoa. Atributos: Pessoa (id_pessoa [PK], nome); CPF (numero_cpf [PK], data_emissao)",
        */
        // Novos enunciados (4)
        "Um CELULAR possui uma única BATERIA original, e cada bateria é fabricada para um modelo específico. Atributos: Celular (id_celular [PK], modelo); Bateria (id_bateria [PK], capacidade)",
        "Um PASSAPORTE pertence a um único CIDADÃO, e cada cidadão possui apenas um passaporte válido. Atributos: Cidadão (id_cidadao [PK], nome); Passaporte (numero [PK], validade)",
        "Um EQUIPAMENTO possui um único MANUAL, e cada manual é específico para um equipamento. Atributos: Equipamento (id_equip [PK], tipo); Manual (id_manual [PK], idioma)",
        "Um CARTÃO DE CRÉDITO está vinculado a uma única CONTA BANCÁRIA, e cada conta tem apenas um cartão principal. Atributos: Conta (id_conta [PK], agencia); Cartão (numero [PK], limite)"
    ],
    '1_N' => [
        // Enunciados existentes (10)
       /* "Uma EDITORA publica vários LIVROS, mas cada livro é publicado por apenas uma editora. Atributos: Editora (cnpj [PK], nome_fantasia); Livro (registro [PK], título)",
        "Um DEPARTAMENTO pode ter vários FUNCIONÁRIOS, mas cada funcionário trabalha em apenas um departamento. Atributos: Departamento (codigo [PK], nome); Funcionário (id_func [PK], nome_completo)",
        "Um USUÁRIO pode fazer várias POSTAGENS, mas cada postagem pertence a apenas um usuário. Atributos: Usuário (id_usario [PK], apelido); Postagem (id [PK], conteudo)",
        "Um PROFESSOR pode orientar vários ALUNOS, mas cada aluno tem apenas um professor orientador. Atributos: Professor (id_prof [PK], nome, departamento);Aluno (id_aluno [PK], nome, periodo)",
        "Um CLIENTE pode realizar vários PEDIDOS, mas cada pedido pertence a apenas um cliente. Atributos: Cliente (id_cliente [PK], nome, telefone); Pedido (id_pedido [PK], data, valor_total)",
        "Uma TURMA pode ter vários ALUNOS, mas cada aluno pertence a apenas uma turma. Atributos: Turma (id_turma [PK], turno); Aluno (ra [PK], nome)",
        "Uma CATEGORIA pode conter vários PRODUTOS, mas cada produto pertence a apenas uma categoria. Atributos: Categoria (id_categoria [PK], nome); Produto (id_produto [PK], descricao)",
        "Um MÉDICO pode atender vários PACIENTES, mas cada paciente possui apenas um médico principal. Atributos: Medico (crm [PK], nome); Paciente (id_paciente [PK], nome)",
        "Um DEPARTAMENTO possui vários FUNCIONÁRIOS, mas cada funcionário trabalha em apenas um departamento. Atributos: Departamento (id_departamento [PK], nome); Funcionario (id_funcionario [PK], nome)",
        "Uma LOJA recebe vários PEDIDOS, mas cada pedido pertence a apenas uma loja. Atributos: Loja (id_loja [PK], nome); Pedido (id_pedido [PK], data)",
        */
        // Novos enunciados (4)
        "Um FABRICANTE produz vários MODELOS de carro, mas cada modelo pertence a um único fabricante. Atributos: Fabricante (id_fab [PK], pais); Modelo (id_modelo [PK], nome)",
        "Um VOO transporta vários PASSAGEIROS, mas cada passageiro está em apenas um voo por vez. Atributos: Voo (id_voo [PK], horario); Passageiro (id_pass [PK], nome)",
        "Um RESTAURANTE faz várias ENTREGAS, mas cada entrega pertence a um único restaurante. Atributos: Restaurante (id_rest [PK], especialidade); Entrega (id_entrega [PK], endereco)",
        "Um TREINADOR orienta vários ATLETAS, mas cada atleta tem apenas um treinador principal. Atributos: Treinador (id_treinador [PK], nome); Atleta (id_atleta [PK], modalidade)"
    ],
    'N_N' => [
        // Enunciados existentes (10)
       /* "Um PARTICIPANTE pode se inscrever em vários EVENTOS, e um evento pode ter vários participantes. Atributos: Participante (RG [PK], nome); Evento (id [PK], tema)",
        "Uma MÚSICA pode estar em vários ÁLBUNS (compilações), e um álbum contém várias músicas. Atributos: Música (registro [PK], nome); Álbum (codigo [PK], titulo)",
        "Um FILME pode pertencer a vários GÊNEROS, e um gênero pode classificar vários filmes. Atributos: Filme (id [PK], titulo_original); Gênero (código [PK], nome do gênero)",
        "Um FUNCIONÁRIO pode trabalhar em vários PROJETOS, e um projeto pode ter vários funcionários alocados. Atributos: Funcionário (id_func [PK], nome, cargo);Projeto (id_projeto [PK], titulo, orcamento)",
        "Um ALUNO pode se matricular em vários CURSOS, e um curso pode ter vários alunos matriculados. Atributos: Aluno (id_aluno [PK], nome, matricula); Curso (id_curso [PK], nome, carga_horaria)",
        "Um ALUNO pode cursar várias DISCIPLINAS, e uma disciplina pode ser cursada por vários alunos. Atributos: Aluno (ra [PK], nome); Disciplina (id_disciplina [PK], nome)",
        "Um CLIENTE pode comprar vários PRODUTOS, e um produto pode ser comprado por vários clientes. Atributos: Cliente (id_cliente [PK], nome); Produto (id_produto [PK], descricao)",
        "Um PROFESSOR pode ministrar vários CURSOS, e um curso pode ser ministrado por vários professores. Atributos: Professor (id_professor [PK], nome); Curso (id_curso [PK], nome)",
        "Um AUTOR pode escrever vários LIVROS, e um livro pode ter mais de um autor. Atributos: Autor (id_autor [PK], nome); Livro (id_livro [PK], titulo)",
        "Um ATOR pode atuar em vários FILMES, e um filme pode ter vários atores. Atributos: Ator (id_ator [PK], nome); Filme (id_filme [PK], titulo)",
        */
        // Novos enunciados (4)
        "Um INVESTIDOR pode financiar vários STARTUPS, e uma startup pode ter vários investidores. Atributos: Investidor (id_invest [PK], nome); Startup (id_startup [PK], segmento)",
        "Um INGREDIENTE pode ser usado em várias RECEITAS, e uma receita usa vários ingredientes. Atributos: Ingrediente (id_ingred [PK], nome); Receita (id_receita [PK], nome_prato)",
        "Um HOSPITAL atende vários PLANOS DE SAÚDE, e um plano cobre vários hospitais. Atributos: Hospital (id_hosp [PK], nome); Plano (id_plano [PK], operadora)",
        "Um LIVRO pode estar em várias BIBLIOTECAS, e uma biblioteca possui vários livros. Atributos: Livro (id_livro [PK], titulo); Biblioteca (id_bib [PK], local)"
    ]
];


?>