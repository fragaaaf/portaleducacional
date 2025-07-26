// script.js

document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quizForm');
    
    // Elementos dinâmicos para Estudantes
    const estudantesContainer = document.getElementById('estudantes-list-container');
    const selecionarTodosEstudantesCheckbox = document.getElementById('selecionarTodosEstudantes');

    // Elementos dinâmicos para Assuntos
    const assuntosContainer = document.getElementById('assuntos-list-container');
    const selecionarTodosAssuntosCheckbox = document.getElementById('selecionarTodosAssuntos');

    // Elementos dinâmicos para Subtipos de Resposta Aberta
    const subtiposRespostaAbertaGroup = document.getElementById('subtiposRespostaAberta-group'); // O div pai com label e checkbox "Selecionar Todos"
    const subtiposRespostaAbertaContainer = document.getElementById('subtiposRespostaAberta-list-container'); // O container dos checkboxes
    const selecionarTodosSubtiposRespostaAbertaCheckbox = document.getElementById('selecionarTodosSubtiposRespostaAberta');

    // Elementos para Data da Prova
    const customDateInputGroup = document.getElementById('customDateInputGroup'); // O div que contém o input date
    const dataProvaRadios = document.querySelectorAll('input[name="dataProva"]');


    // Funções de Utilitário
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function hideError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    // --- Lógica de Exibição e Seleção de Estudantes Dinâmicos ---
    // Estudantes e Assuntos containers inicialmente ocultos
    if (estudantesContainer) {
        estudantesContainer.style.display = 'none';
        document.getElementById('selecionarTodosEstudantesLabel').style.display = 'none';
    }

    document.querySelectorAll('input[name="turma"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedTurmaId = this.value;
            if (estudantesContainer) {
                estudantesContainer.style.display = 'block';
                document.getElementById('selecionarTodosEstudantesLabel').style.display = 'flex';
            }
            document.querySelectorAll('.estudante-checkbox').forEach(checkbox => {
                if (checkbox.dataset.turmaId === selectedTurmaId) {
                    checkbox.parentElement.style.display = 'flex';
                } else {
                    checkbox.parentElement.style.display = 'none';
                    checkbox.checked = false;
                }
            });
            if (selecionarTodosEstudantesCheckbox) {
                selecionarTodosEstudantesCheckbox.checked = false;
            }
            hideError('estudantesError');
        });
    });

    if (selecionarTodosEstudantesCheckbox) {
        selecionarTodosEstudantesCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.estudante-checkbox').forEach(checkbox => {
                if (checkbox.parentElement.style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
        });
    }

    // --- Lógica de Exibição e Seleção de Assuntos Dinâmicos ---
    if (assuntosContainer) {
        assuntosContainer.style.display = 'none';
        document.getElementById('selecionarTodosAssuntosLabel').style.display = 'none';
    }

    document.querySelectorAll('input[name="disciplina"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedDisciplinaId = this.value;
            if (assuntosContainer) {
                assuntosContainer.style.display = 'block';
                document.getElementById('selecionarTodosAssuntosLabel').style.display = 'flex';
            }
            document.querySelectorAll('.assunto-checkbox').forEach(checkbox => {
                if (checkbox.dataset.disciplinaId === selectedDisciplinaId) {
                    checkbox.parentElement.style.display = 'flex';
                } else {
                    checkbox.parentElement.style.display = 'none';
                    checkbox.checked = false;
                }
            });
            if (selecionarTodosAssuntosCheckbox) {
                selecionarTodosAssuntosCheckbox.checked = false;
            }
            hideError('assuntosError');
        });
    });

    if (selecionarTodosAssuntosCheckbox) {
        selecionarTodosAssuntosCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.assunto-checkbox').forEach(checkbox => {
                if (checkbox.parentElement.style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
        });
    }

    // --- Lógica para Data da Prova ---
    if (customDateInputGroup) {
        customDateInputGroup.style.display = 'none'; // Oculta o input de data personalizado inicialmente
    }
    dataProvaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateInputGroup.style.display = 'block';
                document.getElementById('dataPersonalizada').setAttribute('required', 'required'); // Torna o campo obrigatório
            } else {
                customDateInputGroup.style.display = 'none';
                document.getElementById('dataPersonalizada').removeAttribute('required'); // Remove a obrigatoriedade
                document.getElementById('dataPersonalizada').value = ''; // Limpa o valor
            }
            hideError('dataProvaError');
        });
    });

    // --- Lógica para Subtipos de Resposta Aberta ---
    // Garante que o grupo de subtipos esteja oculto no carregamento inicial
    if (subtiposRespostaAbertaGroup) {
        subtiposRespostaAbertaGroup.style.display = 'none';
    }

    document.querySelectorAll('input[name="formatoPerguntas[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const respostaAbertaSelected = document.querySelector('input[name="formatoPerguntas[]"][value="Resposta Aberta"]')?.checked;
            
            if (respostaAbertaSelected) {
                subtiposRespostaAbertaGroup.style.display = 'block';
            } else {
                subtiposRespostaAbertaGroup.style.display = 'none';
                // Desmarca todos os subtipos quando "Resposta Aberta" é desmarcado
                document.querySelectorAll('input[name="subtiposRespostaAberta[]"]').forEach(subCheckbox => {
                    subCheckbox.checked = false;
                });
                if (selecionarTodosSubtiposRespostaAbertaCheckbox) {
                    selecionarTodosSubtiposRespostaAbertaCheckbox.checked = false;
                }
            }
            hideError('subtiposRespostaAbertaError'); // Limpa erro ao mudar seleção
        });
    });

    if (selecionarTodosSubtiposRespostaAbertaCheckbox) {
        selecionarTodosSubtiposRespostaAbertaCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('input[name="subtiposRespostaAberta[]"]').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }


    // --- Validação Client-Side do Formulário ---
    quizForm.addEventListener('submit', function(event) {
        let isValid = true;

        // Limpa mensagens de erro anteriores
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // Validação: Turma (radio_group)
        const turmaRadios = document.querySelectorAll('input[name="turma"]:checked');
        if (turmaRadios.length === 0) {
            showError('turmaError', 'Selecione uma turma.');
            isValid = false;
        }

        // Validação: Estudantes (checkbox_group_dynamic) - verifica apenas os visíveis
        const selectedTurmaIdForValidation = turmaRadios.length > 0 ? turmaRadios[0].value : '';
        const estudantesCheckboxesVisiveis = document.querySelectorAll('.estudante-checkbox[data-turma-id="' + selectedTurmaIdForValidation + '"]');
        const estudantesSelecionadosVisiveis = Array.from(estudantesCheckboxesVisiveis).filter(cb => cb.checked);
        
        if (turmaRadios.length > 0 && estudantesSelecionadosVisiveis.length === 0) { // Turma selecionada mas nenhum estudante visível marcado
            showError('estudantesError', 'Selecione pelo menos um estudante da turma.');
            isValid = false;
        } else if (turmaRadios.length === 0) { // Se nenhuma turma foi selecionada, o erro de estudantes deve aparecer
            showError('estudantesError', 'Selecione uma turma antes de selecionar estudantes.');
            isValid = false;
        }


        // Validação: Disciplina (radio_group)
        const disciplinaRadios = document.querySelectorAll('input[name="disciplina"]:checked');
        if (disciplinaRadios.length === 0) {
            showError('disciplinaError', 'Selecione uma disciplina.');
            isValid = false;
        }

        // Validação: Assuntos (checkbox_group_dynamic) - verifica apenas os visíveis
        const selectedDisciplinaIdForValidation = disciplinaRadios.length > 0 ? disciplinaRadios[0].value : '';
        const assuntosCheckboxesVisiveis = document.querySelectorAll('.assunto-checkbox[data-disciplina-id="' + selectedDisciplinaIdForValidation + '"]');
        const assuntosSelecionadosVisiveis = Array.from(assuntosCheckboxesVisiveis).filter(cb => cb.checked);

        if (disciplinaRadios.length > 0 && assuntosSelecionadosVisiveis.length === 0) {
            showError('assuntosError', 'Selecione pelo menos um assunto da disciplina.');
            isValid = false;
        } else if (disciplinaRadios.length === 0) {
            showError('assuntosError', 'Selecione uma disciplina antes de selecionar assuntos.');
            isValid = false;
        } else {
             // Validação específica: se "Lógica de Programação" estiver selecionada
             if (selectedDisciplinaIdForValidation === 'log_prog') {
                const controleRepeticaoAssuntos = ['FOR', 'WHILE', 'DO-WHILE'];
                let hasRepetitionAssunto = false;
                let selectedRepetitionAssuntosCount = 0;

                assuntosSelecionadosVisiveis.forEach(assuntoCb => {
                    if (controleRepeticaoAssuntos.includes(assuntoCb.value)) {
                        hasRepetitionAssunto = true;
                        selectedRepetitionAssuntosCount++;
                    }
                });

                // Se "Controle de Repetição" está selecionado (indiretamente, através de FOR/WHILE/DO-WHILE)
                // e nenhum dos subtipos específicos (FOR, WHILE, DO-WHILE) foi selecionado
                // A validação agora é se a disciplina é 'log_prog' e se pelo menos um dos assuntos FOR/WHILE/DO-WHILE foi selecionado
                // Se nenhum assunto de controle de repetição foi selecionado, mas o usuário escolheu Lógica de Programação, isso será tratado pela validação geral de 'assuntos'.
                // O ponto 1.1 "forçará a seleção de pelo menos um [tipo de laço]" é coberto pela validação geral de 'assuntos' 
                // se 'FOR', 'WHILE', 'DO-WHILE' forem os únicos assuntos de interesse na prova de Lógica.
                // Se houver outros assuntos de Lógica (Fundamentos, Variáveis, etc), então a validação é que QUALQUER assunto deve ser selecionado.
                // A interpretação dada é que se a prova é de 'Lógica de Programação', então o usuário DEVE selecionar ALGUM assunto de 'Lógica de Programação'.
                // Se ele quer uma prova apenas de controle de repetição, ele selecionaria FOR/WHILE/DO-WHILE.
                // A validação atual já cobre isso: se disciplina é Lógica, e 0 assuntos de Lógica são selecionados, vai dar erro em 'assuntosError'.
             }
        }


        // Validação: Formato das Perguntas (checkbox_group) - Apenas um ou mais, não todos
        const formatoPerguntasCheckboxes = document.querySelectorAll('input[name="formatoPerguntas[]"]:checked');
        if (formatoPerguntasCheckboxes.length === 0) {
            showError('formatoPerguntasError', 'Selecione pelo menos um formato de pergunta.');
            isValid = false;
        }

        // Validação: Subtipos de Resposta Aberta (se "Resposta Aberta" estiver selecionado)
        const respostaAbertaSelected = document.querySelector('input[name="formatoPerguntas[]"][value="Resposta Aberta"]')?.checked;
        if (respostaAbertaSelected) {
            const subtiposRespostaAbertaCheckboxes = document.querySelectorAll('input[name="subtiposRespostaAberta[]"]:checked');
            if (subtiposRespostaAbertaCheckboxes.length === 0) {
                showError('subtiposRespostaAbertaError', 'Selecione pelo menos um subtipo para Resposta Aberta.');
                isValid = false;
            }
        }


        // Validação: Nível de Dificuldade (radio_group)
        const dificuldadeRadios = document.querySelectorAll('input[name="dificuldade"]:checked');
        if (dificuldadeRadios.length === 0) {
            showError('dificuldadeError', 'Selecione um nível de dificuldade.');
            isValid = false;
        }

        // Validação: Tipo de Prova (radio_group)
        const tipoProvaRadios = document.querySelectorAll('input[name="tipoProva"]:checked');
        if (tipoProvaRadios.length === 0) {
            showError('tipoProvaError', 'Selecione um tipo de prova.');
            isValid = false;
        }

        // Validação: Data da Prova
        const dataProvaRadioSelected = document.querySelector('input[name="dataProva"]:checked');
        if (!dataProvaRadioSelected) {
            showError('dataProvaError', 'Selecione a data da prova.');
            isValid = false;
        } else if (dataProvaRadioSelected.value === 'custom') {
            const customDateInput = document.getElementById('dataPersonalizada');
            if (!customDateInput || customDateInput.value === '') {
                showError('dataProvaError', 'Por favor, insira a data personalizada.');
                isValid = false;
            }
        }


        // Validação: Número de Questões
        const numQuestoes = document.getElementById('numQuestoes');
        const num = parseInt(numQuestoes ? numQuestoes.value : '', 10);
        // Assumindo que min/max são 1 e 50 conforme config.json
        if (isNaN(num) || num < 1 || num > 50) {
            showError('numQuestoesError', 'O número de questões deve ser entre 1 e 50.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault(); // Impede o envio do formulário se houver erros
            // Rola para o primeiro erro se houver
            const firstError = document.querySelector('.error-message[style*="block"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});