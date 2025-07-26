document.addEventListener('DOMContentLoaded', function() {
    
    //atualização: 17/07/2025, 18:46.Por Alberto Fraga

    // --- Referências aos elementos HTML ---

    // Turma e Estudantes
    const turmaRadios = document.querySelectorAll('input[name="turma"]');
    const estudantesContainer = document.getElementById('estudantes-list-container');
    const estudanteCheckboxes = document.querySelectorAll('.estudante-checkbox');
    const selecionarTodosEstudantesCheckbox = document.getElementById('selecionarTodosEstudantes');
    const selecionarTodosEstudantesLabel = document.getElementById('selecionarTodosEstudantesLabel');

    // Disciplina e Assuntos
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
    const selecionarTodosSubtiposRespostaAbertaCheckbox = document.getElementById('selecionarTodosSubtiposRespostaAberta');
    const subtiposRespostaAbertaCheckboxes = document.querySelectorAll('input[name="subtiposRespostaAberta[]"]');
    const selecionarTodosSubtiposRespostaAbertaLabel = document.getElementById('selecionarTodosSubtiposRespostaAbertaLabel');


    // --- Funções Auxiliares para Exibir/Ocultar e Resetar ---

    /**
     * Oculta um container e seu respectivo checkbox "Selecionar Todos" e desmarca todos os checkboxes internos.
     * @param {HTMLElement} container O container a ser ocultado.
     * @param {HTMLElement} selectAllLabel A label do checkbox "Selecionar Todos".
     * @param {NodeList} checkboxes A lista de checkboxes dentro do container.
     * @param {HTMLElement} [selectAllCheckbox=null] O checkbox "Selecionar Todos" (opcional).
     */
    function hideAndResetGroup(container, selectAllLabel, checkboxes, selectAllCheckbox = null) {
        if (container) container.style.display = 'none';
        if (selectAllLabel) selectAllLabel.style.display = 'none';
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        checkboxes.forEach(cb => {
            cb.checked = false;
            const label = cb.closest('label');
            if (label) {
                label.style.display = 'none'; // Garante que a label pai é ocultada
            }
        });
    }

    /**
     * Exibe um container e seu respectivo checkbox "Selecionar Todos".
     * @param {HTMLElement} container O container a ser exibido.
     * @param {HTMLElement} selectAllLabel A label do checkbox "Selecionar Todos".
     * @param {HTMLElement} [parentContainer=null] Um container pai para gerenciar visibilidade (ex: form-group).
     */
    function showGroup(container, selectAllLabel, parentContainer = null) {
        if (parentContainer) parentContainer.style.display = 'block'; // Garante que o form-group pai está visível
        if (container) container.style.display = 'block';
        if (selectAllLabel) selectAllLabel.style.display = 'block';
    }


    // --- 1. Inicialização: Ocultar todos os grupos dinâmicos ao carregar a página ---
    hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);
    hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
    hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
    hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);


    // --- 2. Lógica para Estudantes (depende da Turma) ---
    turmaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedTurmaId = this.value;
            console.log('Turma selecionada:', selectedTurmaId);

            hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);

            let hasStudentsForTurma = false;
            estudanteCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label'); // Garante que a label é referenciada
                if (label && checkbox.dataset.turmaId === selectedTurmaId) {
                    label.style.display = 'block';
                    hasStudentsForTurma = true;
                    console.log('Exibindo estudante:', checkbox.value, label.textContent.trim());
                }
            });

            if (hasStudentsForTurma) {
                showGroup(estudantesContainer, selecionarTodosEstudantesLabel);
                console.log('Grupo de estudantes exibido.');
            } else {
                console.log('Nenhum estudante para a turma selecionada.');
            }

            // Ao mudar a turma, resetar e ocultar Assuntos e Formatos de Perguntas também,
            // pois uma nova turma implica em potencialmente novas disciplinas/assuntos.
            hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
        });
    });

    // Lógica para "Selecionar Todos" estudantes
    if (selecionarTodosEstudantesCheckbox) {
        selecionarTodosEstudantesCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const selectedTurmaId = document.querySelector('input[name="turma"]:checked')?.value;
            console.log('Selecionar Todos Estudantes clicado. Marcado:', isChecked, 'Turma selecionada:', selectedTurmaId);

            if (selectedTurmaId) {
                estudanteCheckboxes.forEach(checkbox => {
                    const label = checkbox.closest('label');
                    // Só afeta os checkboxes visíveis da turma selecionada
                    if (label && checkbox.dataset.turmaId === selectedTurmaId && label.style.display !== 'none') {
                        checkbox.checked = isChecked;
                        console.log('Estudante', checkbox.value, 'marcado:', isChecked);
                    }
                });
            }
        });
    }


    // --- 3. Lógica para Assuntos (depende da Disciplina) ---
    const disciplinaRadios = document.querySelectorAll('input[name="disciplina"]');

    disciplinaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedDisciplinaId = this.value;
            console.log('Disciplina selecionada:', selectedDisciplinaId);
            
            hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);

            let hasAssuntosForDisciplina = false;
            if (selectedDisciplinaId) {
                assuntoCheckboxes.forEach(checkbox => {
                    const label = checkbox.closest('label'); // Garante que a label é referenciada
                    if (label && label.dataset.disciplinaId === selectedDisciplinaId) {
                        label.style.display = 'block'; // **CORRIGIDO/REFORÇADO: Garante que a label é exibida**
                        hasAssuntosForDisciplina = true;
                        console.log('Exibindo assunto:', checkbox.value, label.textContent.trim());
                    }
                });
            }

            if (hasAssuntosForDisciplina) {
                showGroup(assuntosContainer, selecionarTodosAssuntosLabel);
                console.log('Grupo de assuntos exibido.');
            } else {
                console.log('Nenhum assunto para a disciplina selecionada.');
            }
        });
    });
    
    // Lógica para "Selecionar Todos" assuntos
    if (selecionarTodosAssuntosCheckbox) {
        selecionarTodosAssuntosCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const selectedDisciplinaId = document.querySelector('input[name="disciplina"]:checked')?.value;
            console.log('Selecionar Todos Assuntos clicado. Marcado:', isChecked, 'Disciplina selecionada:', selectedDisciplinaId);

            if (selectedDisciplinaId) {
                assuntoCheckboxes.forEach(checkbox => {
                    const label = checkbox.closest('label'); // Garante que a label é referenciada
                    // Só afeta os checkboxes visíveis e que pertencem à disciplina selecionada
                    if (label && checkbox.dataset.disciplinaId === selectedDisciplinaId && label.style.display !== 'none') {
                        checkbox.checked = isChecked;
                        console.log('Assunto', checkbox.value, 'marcado:', isChecked);
                    }
                });
            }
            updateFormatosPerguntasVisibility();
        });
    }

    // --- 4. Lógica para Formatos de Perguntas (depende de pelo menos um Assunto selecionado) ---

    assuntoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateFormatosPerguntasVisibility);
    });

    function updateFormatosPerguntasVisibility() {
        const selectedDisciplinaId = document.querySelector('input[name="disciplina"]:checked')?.value;
        console.log('Verificando visibilidade de formatos. Disciplina:', selectedDisciplinaId);
        
        let anyAssuntoSelected = false;
        
        if (selectedDisciplinaId) {
            assuntoCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label'); // Garante que a label é referenciada
                // Verifica se o checkbox pertence à disciplina selecionada E está marcado E está visível
                if (label && checkbox.dataset.disciplinaId === selectedDisciplinaId && checkbox.checked && label.style.display !== 'none') {
                    anyAssuntoSelected = true;
                    console.log('Assunto selecionado visível para formatos:', checkbox.value);
                }
            });
        }

        if (anyAssuntoSelected) {
            showGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel);
            console.log('Grupo de formatos de perguntas exibido.');
        } else {
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
            console.log('Grupo de formatos de perguntas ocultado.');
        }
    }

    // Lógica para "Selecionar Todos" formatos de perguntas
    if (selecionarTodosFormatosPerguntasCheckbox) {
        selecionarTodosFormatosPerguntasCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            console.log('Selecionar Todos Formatos clicado. Marcado:', isChecked);
            formatoPerguntaCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                // Só afeta os checkboxes visíveis do grupo de formatos de perguntas
                if (label && label.style.display !== 'none') {
                    checkbox.checked = isChecked;
                    console.log('Formato', checkbox.value, 'marcado:', isChecked);
                }
            });
            updateSubtiposRespostaAbertaVisibility();
        });
    }

    // --- 5. Lógica para Subtipos de Resposta Aberta (depende de 'Resposta Aberta' selecionado em Formatos) ---

    formatoPerguntaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubtiposRespostaAbertaVisibility);
    });

    function updateSubtiposRespostaAbertaVisibility() {
        let respostaAbertaSelected = false;
        console.log('Verificando visibilidade de subtipos de RA.');
        formatoPerguntaCheckboxes.forEach(checkbox => {
            const label = checkbox.closest('label');
            if (label && checkbox.value.toLowerCase().includes('resposta aberta') && checkbox.checked && label.style.display !== 'none') {
                respostaAbertaSelected = true;
                console.log('Formato "Resposta Aberta" selecionado e visível.');
            }
        });

        if (respostaAbertaSelected) {
            showGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel);
            console.log('Grupo de subtipos RA exibido.');
        } else {
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
            console.log('Grupo de subtipos RA ocultado.');
        }
    }


    // --- Lógica de "Selecionar Todos" genérica para subtiposRespostaAberta ---
    if (selecionarTodosSubtiposRespostaAbertaCheckbox) {
        selecionarTodosSubtiposRespostaAbertaCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            console.log('Selecionar Todos Subtipos RA clicado. Marcado:', isChecked);
            subtiposRespostaAbertaCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label && label.style.display !== 'none') {
                    checkbox.checked = isChecked;
                    console.log('Subtipo RA', checkbox.value, 'marcado:', isChecked);
                }
            });
        });
    }


    // --- Chamadas iniciais para garantir o estado correto ao carregar ---
    console.log('Iniciando checagens de estado inicial...');
    
    const initialSelectedTurma = document.querySelector('input[name="turma"]:checked');
    if (initialSelectedTurma) {
        console.log('Turma inicial selecionada, disparando evento de mudança.');
        initialSelectedTurma.dispatchEvent(new Event('change'));
    } else {
        console.log('Nenhuma turma selecionada inicialmente, ocultando estudantes.');
        hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);
    }

    const initialSelectedDisciplinaRadio = document.querySelector('input[name="disciplina"]:checked');
    if (initialSelectedDisciplinaRadio) {
        console.log('Disciplina inicial selecionada, disparando evento de mudança.');
        initialSelectedDisciplinaRadio.dispatchEvent(new Event('change'));
    } else {
        console.log('Nenhuma disciplina selecionada inicialmente, ocultando assuntos, formatos e subtipos RA.');
        hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
        hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
        hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
    }

    // Chamadas finais para garantir a visibilidade correta baseada em qualquer estado pré-selecionado
    updateFormatosPerguntasVisibility();
    updateSubtiposRespostaAbertaVisibility();
    console.log('Inicialização completa.');

    //atualização: 17/07/2025, 18:46.Por Alberto Fraga
});