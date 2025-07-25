document.addEventListener('DOMContentLoaded', function() {
    // --- Referências aos elementos HTML ---\

    // Turma e Estudantes
    const turmaRadios = document.querySelectorAll('input[name="turma"]');
    const estudantesContainer = document.getElementById('estudantes-list-container');
    const estudanteCheckboxes = document.querySelectorAll('.estudante-checkbox');
    const selecionarTodosEstudantesCheckbox = document.getElementById('selecionarTodosEstudantes');
    const selecionarTodosEstudantesLabel = document.getElementById('selecionarTodosEstudantesLabel');

    // Disciplina e Assuntos
    // Modificado: Agora referencia diretamente os rádios da disciplina pelo name, não por um ID de SELECT
    const disciplinaRadios = document.querySelectorAll('input[name="disciplina"]');
    const assuntosContainer = document.getElementById('assuntos-list-container');
    const assuntoCheckboxes = document.querySelectorAll('.assunto-checkbox');
    const selecionarTodosAssuntosCheckbox = document.getElementById('selecionarTodosAssuntos');
    const selecionarTodosAssuntosLabel = document.getElementById('selecionarTodosAssuntosLabel');

    // Formatos de Perguntas
    const formatosPerguntasContainer = document.getElementById('formatosPerguntas-list-container');
    const formatoPerguntaCheckboxes = document.querySelectorAll('.formato-pergunta-checkbox');
    const selecionarTodosFormatosPerguntasCheckbox = document.getElementById('selecionarTodosFormatosPerguntas');
    const selecionarTodosFormatosPerguntasLabel = document.getElementById('selecionarTodosFormatosPerguntasLabel');

    // Subtipos de Resposta Aberta
    const subtiposRespostaAbertaContainer = document.getElementById('subtiposRespostaAberta-list-container');
    const subtiposRespostaAbertaCheckboxes = document.querySelectorAll('input[name="subtiposRespostaAberta[]"]');
    const selecionarTodosSubtiposRespostaAbertaCheckbox = document.getElementById('selecionarTodosSubtiposRespostaAberta');
    const selecionarTodosSubtiposRespostaAbertaLabel = document.getElementById('selecionarTodosSubtiposRespostaAbertaLabel');


    // --- Funções Auxiliares ---\

    function hideAndResetGroup(container, selectAllLabel, checkboxes, selectAllCheckbox = null) {
        if (container) container.style.display = 'none';
        if (selectAllLabel) selectAllLabel.style.display = 'none';
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        checkboxes.forEach(cb => {
            cb.checked = false;
            const label = cb.closest('label');
            if (label) {
                label.style.display = 'none';
            }
        });
        console.log('Grupo ocultado e resetado.');
    }

    function showGroup(container, selectAllLabel) {
        if (container) container.style.display = 'block';
        if (selectAllLabel) selectAllLabel.style.display = 'block';
    }


    // --- Lógica para Formatos de Perguntas e Subtipos de Resposta Aberta ---\

    // A visibilidade dos formatos de perguntas depende dos assuntos selecionados
    function updateFormatosPerguntasVisibility() {
        // Pega os assuntos visíveis e marcados
        const assuntosSelecionadosVisiveis = Array.from(assuntoCheckboxes).filter(checkbox => {
            const label = checkbox.closest('label');
            return checkbox.checked && label && label.style.display !== 'none';
        }).map(checkbox => checkbox.value);

        console.log('Assuntos selecionados visíveis para formato:', assuntosSelecionadosVisiveis);

        // Se 'FOR', 'WHILE', 'DO-WHILE' estiverem selecionados, exibir formatos de laço
        const possuiAssuntosDeLaco = assuntosSelecionadosVisiveis.some(assunto =>
            ['4', '5', '6'].includes(assunto) // Corresponde aos valores 'FOR', 'WHILE', 'DO-WHILE' no seu PHP/JSON
        );
        console.log('Possui assuntos de laço:', possuiAssuntosDeLaco);


        // Verifica se 'Resposta Aberta' está entre os formatos selecionados
        const respostaAbertaCheckbox = Array.from(formatoPerguntaCheckboxes).find(cb => cb.value === 'Resposta Aberta');
        const isRespostaAbertaSelected = respostaAbertaCheckbox && respostaAbertaCheckbox.checked;
        console.log('Formato "Resposta Aberta" selecionado:', isRespostaAbertaSelected);

        // Oculta e reseta todos os formatos e subtipos por padrão
        hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
        hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);


        let hasVisibleFormatos = false;
        formatoPerguntaCheckboxes.forEach(checkbox => {
            const label = checkbox.closest('label');
            // 'Laco de Repetição' só aparece se houver assunto de laço selecionado
            if (checkbox.value === 'Laco de Repeticao') {
                if (possuiAssuntosDeLaco) {
                    label.style.display = 'block';
                    hasVisibleFormatos = true;
                } else {
                    label.style.display = 'none';
                    checkbox.checked = false; // Garante que desmarca se assunto de laço for deselecionado
                }
            } else {
                // Outros formatos (Múltipla Escolha, Resposta Aberta) sempre visíveis
                label.style.display = 'block';
                hasVisibleFormatos = true;
            }
            console.log(`Formato ${checkbox.value} display: ${label.style.display}`);
        });

        if (hasVisibleFormatos) {
            showGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel);
            console.log('Grupo de formatos de perguntas exibido.');
        } else {
            console.log('Nenhum formato de pergunta a ser exibido. Grupo ocultado.');
        }

        // Atualiza a visibilidade dos subtipos de RA APÓS a atualização dos formatos
        updateSubtiposRespostaAbertaVisibility();
    }

    // A visibilidade dos subtipos de RA depende se 'Resposta Aberta' está selecionado
    function updateSubtiposRespostaAbertaVisibility() {
        const respostaAbertaCheckbox = Array.from(formatoPerguntaCheckboxes).find(cb => cb.value === 'Resposta Aberta');
        const isRespostaAbertaSelectedAndVisible = respostaAbertaCheckbox && respostaAbertaCheckbox.checked && respostaAbertaCheckbox.closest('label').style.display !== 'none';

        console.log('Função updateSubtiposRespostaAbertaVisibility acionada.');
        console.log('Resposta Aberta selecionada e visível:', isRespostaAbertaSelectedAndVisible);

        if (isRespostaAbertaSelectedAndVisible) {
            subtiposRespostaAbertaCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label) {
                    label.style.display = 'block';
                    console.log(`Subtipo RA ${checkbox.value} display: block`);
                }
            });
            showGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel);
            console.log('Grupo de subtipos de resposta aberta exibido.');
        } else {
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
            console.log('Grupo de subtipos de resposta aberta ocultado.');
        }
    }


    // --- Event Listeners ---\

    // Event listener para seleção de Turma (radio_group)
    turmaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedTurmaId = this.value;
            console.log('Turma selecionada:', selectedTurmaId);

            // Oculta e reseta todos os estudantes
            hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);

            let hasEstudantesForTurma = false;
            estudanteCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label && label.dataset.turmaId === selectedTurmaId) {
                    label.style.display = 'block';
                    hasEstudantesForTurma = true;
                    console.log('Exibindo estudante:', checkbox.value, label.textContent.trim());
                }
            });

            if (hasEstudantesForTurma) {
                showGroup(estudantesContainer, selecionarTodosEstudantesLabel);
                console.log('Grupo de estudantes exibido.');
            } else {
                console.log('Nenhum estudante encontrado para a turma selecionada. Grupo de estudantes ocultado.');
            }
        });
    });

    // Event listener para seleção de Disciplina (radio_group)
    // REMOVIDO o if para SELECT, agora focado apenas em radio_group
    disciplinaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedDisciplinaId = this.value;
            console.log('Disciplina selecionada (rádio):', selectedDisciplinaId); // Log ajustado para indicar que é rádio

            // Oculta e reseta todos os grupos dependentes (Assuntos, Formatos, Subtipos RA)
            hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);

            let hasAssuntosForDisciplina = false;
            assuntoCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                // Adicionei console.log para depuração aqui
                console.log('Checking assunto visibility for:', checkbox.value, 'data-disciplina-id:', checkbox.dataset.disciplinaId, 'is visible:', label && label.style.display !== 'none', 'matches selected disciplina:', checkbox.dataset.disciplinaId === selectedDisciplinaId);

                if (label && checkbox.dataset.disciplinaId === selectedDisciplinaId) {
                    label.style.display = 'block';
                    hasAssuntosForDisciplina = true;
                    console.log('Exibindo assunto:', checkbox.value, label.textContent.trim());
                }
            });

            if (hasAssuntosForDisciplina) {
                showGroup(assuntosContainer, selecionarTodosAssuntosLabel);
                console.log('Grupo de assuntos exibido.');
            } else {
                console.log('Nenhum assunto encontrado para a disciplina selecionada. Grupo de assuntos ocultado.');
            }

            // Garante que a visibilidade dos formatos de perguntas e subtipos RA seja atualizada
            updateFormatosPerguntasVisibility();
            updateSubtiposRespostaAbertaVisibility();
        });
    });


    // Event listener para "Selecionar Todos os Estudantes"
    if (selecionarTodosEstudantesCheckbox) {
        selecionarTodosEstudantesCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const selectedTurmaId = document.querySelector('input[name="turma"]:checked')?.value;
            console.log('Selecionar Todos Estudantes clicado. Marcado:', isChecked, 'Turma selecionada:', selectedTurmaId);

            estudanteCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                // Apenas marca/desmarca se a label estiver visível E pertencer à turma selecionada
                console.log('Checking estudante checkbox:', checkbox.value, 'for turma:', checkbox.dataset.turmaId, 'is visible:', label && label.style.display !== 'none', 'matches selected turma:', checkbox.dataset.turmaId === selectedTurmaId);
                if (label && label.style.display !== 'none' && checkbox.dataset.turmaId === selectedTurmaId) {
                    checkbox.checked = isChecked;
                }
            });
        });
    }

    // Event listener para "Selecionar Todos os Assuntos"
    if (selecionarTodosAssuntosCheckbox) {
        selecionarTodosAssuntosCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            // Obtém o valor do rádio de disciplina selecionado para filtrar os assuntos
            const selectedDisciplinaId = document.querySelector('input[name="disciplina"]:checked')?.value;
            console.log('Selecionar Todos Assuntos clicado. Marcado:', isChecked, 'Disciplina selecionada:', selectedDisciplinaId);

            assuntoCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                // Apenas marca/desmarca se a label estiver visível E pertencer à disciplina selecionada
                console.log('Checking assunto checkbox:', checkbox.value, 'for discipline:', checkbox.dataset.disciplinaId, 'is visible:', label && label.style.display !== 'none', 'matches selected disciplina:', checkbox.dataset.disciplinaId === selectedDisciplinaId);
                if (label && label.style.display !== 'none' && checkbox.dataset.disciplinaId === selectedDisciplinaId) {
                    checkbox.checked = isChecked;
                }
            });
        });
    }

    // Event listener para "Selecionar Todos Formatos de Perguntas"
    if (selecionarTodosFormatosPerguntasCheckbox) {
        selecionarTodosFormatosPerguntasCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            console.log('Selecionar Todos Formatos clicado. Marcado:', isChecked);

            formatoPerguntaCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label && label.style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
            updateSubtiposRespostaAbertaVisibility(); // Atualiza a visibilidade dos subtipos RA após a seleção de formatos
        });
    }

    // Event listener para "Selecionar Todos Subtipos de Resposta Aberta"
    if (selecionarTodosSubtiposRespostaAbertaCheckbox) {
        selecionarTodosSubtiposRespostaAbertaCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            console.log('Selecionar Todos Subtipos RA clicado. Marcado:', isChecked);

            subtiposRespostaAbertaCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label && label.style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
        });
    }

    // Event listeners para mudanças nos checkboxes de assuntos e formatos para atualizar visibilidade
    assuntoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateFormatosPerguntasVisibility);
    });

    formatoPerguntaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubtiposRespostaAbertaVisibility);
    });


    // --- Inicialização ao carregar a página ---\

    // Se houver turma pré-selecionada (ex: via histórico do navegador), dispara o evento de mudança
    const initialSelectedTurmaRadio = document.querySelector('input[name="turma"]:checked');
    if (initialSelectedTurmaRadio) {
        initialSelectedTurmaRadio.dispatchEvent(new Event('change')); // Simula um "change" para ativar a lógica de exibição
    } else {
        // Se nenhuma turma estiver selecionada inicialmente, garante que os estudantes estejam ocultos
        hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);
    }

    // Se houver disciplina pré-selecionada (rádio), dispara o evento de mudança
    const initialSelectedDisciplinaRadio = document.querySelector('input[name="disciplina"]:checked');
    if (initialSelectedDisciplinaRadio) {
        initialSelectedDisciplinaRadio.dispatchEvent(new Event('change')); // Simula um "change" para ativar a lógica de exibição
    } else {
        // Se nenhuma disciplina estiver selecionada inicialmente, garante que assuntos e dependências estejam ocultos
        hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
        hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
        hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
    }

    // Garante que a visibilidade dos formatos de perguntas e subtipos RA seja atualizada
    // caso assuntos já estejam selecionados (ex: via histórico do navegador)
    updateFormatosPerguntasVisibility();
    updateSubtiposRespostaAbertaVisibility();
});