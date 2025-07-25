document.addEventListener('DOMContentLoaded', function() {
    // --- Referências aos elementos HTML ---

    // Turma e Estudantes
    const turmaRadios = document.querySelectorAll('input[name="turma"]');
    const estudantesContainer = document.getElementById('estudantes-list-container');
    const estudanteCheckboxes = document.querySelectorAll('.estudante-checkbox');
    const selecionarTodosEstudantesCheckbox = document.getElementById('selecionarTodosEstudantes');
    const selecionarTodosEstudantesLabel = document.getElementById('selecionarTodosEstudantesLabel');

    // Disciplina e Assuntos
    const disciplinaSelect = document.getElementById('disciplina');
    const assuntosContainer = document.getElementById('assuntos-list-container');
    const assuntoCheckboxes = document.querySelectorAll('.assunto-checkbox');
    const selecionarTodosAssuntosCheckbox = document.getElementById('selecionarTodosAssuntos');
    const selecionarTodosAssuntosLabel = document.getElementById('selecionarTodosAssuntosLabel');

    // Formatos de Perguntas
    const formatosPerguntasContainer = document.getElementById('formatosPerguntas-list-container');
    const formatoPerguntaCheckboxes = document.querySelectorAll('.formato-pergunta-checkbox');
    const selecionarTodosFormatosPerguntasCheckbox = document.getElementById('selecionarTodosFormatosPerguntas');
    const selecionarTodosFormatosPerguntasLabel = document.getElementById('selecionarTodosFormatosPerguntasLabel');

    // Subtipos de Resposta Aberta (se for dinâmico e depender de outro campo, adicionar lógica)
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
            if (cb.closest('label')) { // Oculta a label pai de cada checkbox
                cb.closest('label').style.display = 'none';
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
    // Para subtiposRespostaAberta, se ele não depende de um campo acima (e sim de um formato de pergunta, por exemplo)
    // Se for sempre visível ou depender apenas de si mesmo para selecionar todos, não precisa de hideAndResetGroup completo,
    // apenas showGroup quando necessário. Por enquanto, vamos assumir que ele pode ser escondido se for parte da dinâmica.
    hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);


    // --- 2. Lógica para Estudantes (depende da Turma) ---
    turmaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedTurmaId = this.value;

            // Ocultar e resetar todos os estudantes e "Selecionar Todos"
            hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);

            // Exibir apenas os estudantes da turma selecionada
            let hasStudentsForTurma = false;
            estudanteCheckboxes.forEach(checkbox => {
                if (checkbox.dataset.turmaId === selectedTurmaId) {
                    checkbox.closest('label').style.display = 'block';
                    hasStudentsForTurma = true;
                }
            });

            // Se houver estudantes para a turma selecionada, mostrar o grupo e seu "Selecionar Todos"
            if (hasStudentsForTurma) {
                showGroup(estudantesContainer, selecionarTodosEstudantesLabel);
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

            if (selectedTurmaId) {
                estudanteCheckboxes.forEach(checkbox => {
                    // Só afeta os checkboxes visíveis da turma selecionada
                    if (checkbox.dataset.turmaId === selectedTurmaId && checkbox.closest('label').style.display !== 'none') {
                        checkbox.checked = isChecked;
                    }
                });
            }
        });
    }


    // --- 3. Lógica para Assuntos (depende da Disciplina) ---
    // --- 3. Lógica para Assuntos (depende da Disciplina) ---
const disciplinaRadios = document.querySelectorAll('input[name="disciplina"]'); // Adicione esta linha

disciplinaRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        const selectedDisciplinaId = this.value;
        
        // Ocultar e resetar todos os assuntos e "Selecionar Todos"
        hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);

        // Exibir apenas os assuntos da disciplina selecionada
        let hasAssuntosForDisciplina = false;
        if (selectedDisciplinaId) {
            assuntoCheckboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (label && label.dataset.disciplinaId === selectedDisciplinaId) {
                    label.style.display = 'block';
                    hasAssuntosForDisciplina = true;
                }
            });
        }

        // Se houver assuntos, mostrar o container e "Selecionar Todos"
        if (hasAssuntosForDisciplina) {
            showGroup(assuntosContainer, selecionarTodosAssuntosLabel);
        }
    });
});
    
    /*
    if (disciplinaSelect) {
        
        disciplinaSelect.addEventListener('change', function() {
            const selectedDisciplinaId = this.value;

            // Ocultar e resetar todos os assuntos e "Selecionar Todos"
            hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
            // Ao mudar a disciplina, resetar e ocultar Formatos de Perguntas e Subtipos RA
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);


            // Exibir apenas os assuntos da disciplina selecionada
            let hasAssuntosForDisciplina = false;
            if (selectedDisciplinaId) { // Só mostra se uma disciplina foi selecionada
                assuntoCheckboxes.forEach(checkbox => {
                    if (checkbox.dataset.disciplinaId === selectedDisciplinaId) {
                        checkbox.closest('label').style.display = 'block';
                        hasAssuntosForDisciplina = true;
                    }
                });
            }

            // Se houver assuntos para a disciplina selecionada, mostrar o grupo e seu "Selecionar Todos"
            if (hasAssuntosForDisciplina) {
                showGroup(assuntosContainer, selecionarTodosAssuntosLabel);
            }
        });
    }
    */
    // Lógica para "Selecionar Todos" assuntos
    if (selecionarTodosAssuntosCheckbox) {
        selecionarTodosAssuntosCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            //correção: 14072025 - linha abaixo 1 substituida por abxaixo 2
            //const selectedDisciplinaId = disciplinaSelect.value; // Pega a disciplina atualmente selecionada
            const selectedDisciplinaId = document.querySelector('input[name="disciplina"]:checked')?.value;
            if (selectedDisciplinaId) {
                assuntoCheckboxes.forEach(checkbox => {
                    // Só afeta os checkboxes visíveis e que pertencem à disciplina selecionada
                    if (checkbox.dataset.disciplinaId === selectedDisciplinaId && checkbox.closest('label').style.display !== 'none') {
                        checkbox.checked = isChecked;
                    }
                });
            }
            // Chama a função para verificar a visibilidade dos formatos de perguntas após mudança nos assuntos
            updateFormatosPerguntasVisibility();
        });
    }

    // --- 4. Lógica para Formatos de Perguntas (depende de pelo menos um Assunto selecionado) ---

    // Adiciona um listener a CADA checkbox de assunto
    assuntoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateFormatosPerguntasVisibility);
    });

    function updateFormatosPerguntasVisibility() {
        const selectedDisciplinaId = disciplinaSelect ? disciplinaSelect.value : null;
        let anyAssuntoSelected = false;
        // correção: incluiu o trecho abaixo
        // Dispara o evento 'change' da disciplina se já estiver selecionada
        const initialSelectedDisciplina = document.querySelector('input[name="disciplina"]:checked');
        if (initialSelectedDisciplina) {
            initialSelectedDisciplina.dispatchEvent(new Event('change'));
        }
        // trecho acima incluso.
        if (selectedDisciplinaId) {
            // Verifica se pelo menos um assunto da disciplina selecionada está marcado
            assuntoCheckboxes.forEach(checkbox => {
                if (checkbox.dataset.disciplinaId === selectedDisciplinaId && checkbox.checked) {
                    anyAssuntoSelected = true;
                }
            });
        }

        if (anyAssuntoSelected) {
            showGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel);
            // Se "Resposta Aberta" for um formato, e seus subtipos dependerem dele,
            // você precisaria de uma lógica para mostrar o subtiposRespostaAbertaContainer aqui.
            // Por enquanto, assumimos que é uma dependência direta do formato "Resposta Aberta".
            // Para isso, teríamos que escutar 'change' em formatoPerguntaCheckboxes
            // e verificar se o checkbox de "Resposta Aberta" está marcado.
        } else {
            hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
        }
    }

    // Lógica para "Selecionar Todos" formatos de perguntas
    if (selecionarTodosFormatosPerguntasCheckbox) {
        selecionarTodosFormatosPerguntasCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            formatoPerguntaCheckboxes.forEach(checkbox => {
                // Só afeta os checkboxes visíveis do grupo de formatos de perguntas
                if (checkbox.closest('label').style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
            // Chama a função para verificar a visibilidade dos subtipos de Resposta Aberta
            updateSubtiposRespostaAbertaVisibility();
        });
    }

    // --- 5. Lógica para Subtipos de Resposta Aberta (depende de 'Resposta Aberta' selecionado em Formatos) ---

    // Adiciona um listener a CADA checkbox de formato de pergunta
    formatoPerguntaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubtiposRespostaAbertaVisibility);
    });

    function updateSubtiposRespostaAbertaVisibility() {
        // Verifica se o checkbox de "Resposta Aberta" (assumindo que o value seja 'respostaAberta') está selecionado
        let respostaAbertaSelected = false;
        formatoPerguntaCheckboxes.forEach(checkbox => {
            /*
            if (checkbox.value === 'respostaAberta' && checkbox.checked) {
                respostaAbertaSelected = true;
            }
            */
           // Modifique a condição para:
            if (checkbox.value.toLowerCase().includes('resposta aberta') && checkbox.checked) {
                respostaAbertaSelected = true;
            }
        });

        if (respostaAbertaSelected) {
            showGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel);
        } else {
            hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
        }
    }


    // --- Lógica de "Selecionar Todos" genérica para subtiposRespostaAberta (se for sempre visível ou já manipulado) ---
    if (selecionarTodosSubtiposRespostaAbertaCheckbox) {
        selecionarTodosSubtiposRespostaAbertaCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            subtiposRespostaAbertaCheckboxes.forEach(checkbox => {
                if (checkbox.closest('label').style.display !== 'none') { // Apenas os visíveis
                    checkbox.checked = isChecked;
                }
            });
        });
    }


    // --- Chamadas iniciais para garantir o estado correto ao carregar ---
    // Isto é útil se você tiver um estado inicial pré-selecionado no PHP
    // Se não houver, as funções hideAndResetGroup no início já cuidam disso.
    // Se houver rádio de turma pré-selecionado:
    const initialSelectedTurma = document.querySelector('input[name="turma"]:checked');
    if (initialSelectedTurma) {
        initialSelectedTurma.dispatchEvent(new Event('change')); // Simula um "change" para ativar a lógica de exibição
    } else {
        // Se nenhuma turma estiver selecionada inicialmente, garante que os estudantes estejam ocultos
        hideAndResetGroup(estudantesContainer, selecionarTodosEstudantesLabel, estudanteCheckboxes, selecionarTodosEstudantesCheckbox);
    }

    // Se houver disciplina pré-selecionada:
    const initialSelectedDisciplina = disciplinaSelect ? disciplinaSelect.value : null;
    if (initialSelectedDisciplina) {
        disciplinaSelect.dispatchEvent(new Event('change')); // Simula um "change" para ativar a lógica de exibição
    } else {
        hideAndResetGroup(assuntosContainer, selecionarTodosAssuntosLabel, assuntoCheckboxes, selecionarTodosAssuntosCheckbox);
        hideAndResetGroup(formatosPerguntasContainer, selecionarTodosFormatosPerguntasLabel, formatoPerguntaCheckboxes, selecionarTodosFormatosPerguntasCheckbox);
        hideAndResetGroup(subtiposRespostaAbertaContainer, selecionarTodosSubtiposRespostaAbertaLabel, subtiposRespostaAbertaCheckboxes, selecionarTodosSubtiposRespostaAbertaCheckbox);
    }

    // Garante que a visibilidade dos formatos de perguntas e subtipos RA seja atualizada
    // caso assuntos já estejam selecionados (ex: via histórico do navegador)
    updateFormatosPerguntasVisibility();
    updateSubtiposRespostaAbertaVisibility();

    // Dispara eventos iniciais se houver seleções prévias
        const initialTurma = document.querySelector('input[name="turma"]:checked');
        if (initialTurma) initialTurma.dispatchEvent(new Event('change'));

        const initialDisciplina = document.querySelector('input[name="disciplina"]:checked');
        if (initialDisciplina) initialDisciplina.dispatchEvent(new Event('change'));
});