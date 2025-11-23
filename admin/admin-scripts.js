/**
 * Scripts admin pour la gestion des questions
 */
(function($) {
	'use strict';
	
	let questionIndex = 0;
	
	// Initialiser l'index
	function initQuestionIndex() {
		const $items = $('#vq-questions-list .vq-question-item');
		if ($items.length > 0) {
			const lastIndex = parseInt($items.last().data('index')) || -1;
			questionIndex = lastIndex + 1;
		}
	}
	
	// Obtenir le type de quiz
	function getQuizType() {
		return $('#vq-questions-container').data('quiz-type') || 'trivia';
	}
	
	// Mettre à jour le type de quiz quand il change
	$('#vq_quiz_type').on('change', function() {
		const newType = $(this).val();
		$('#vq-questions-container').attr('data-quiz-type', newType);
		// Recharger la page pour mettre à jour l'interface
		// Ou mettre à jour dynamiquement les champs
		updateQuestionsUI();
	});
	
	// Mettre à jour l'UI des questions selon le type
	function updateQuestionsUI() {
		const quizType = getQuizType();
		$('.vq-question-item').each(function() {
			if (quizType === 'trivia') {
				$(this).find('.vq-answer-score').closest('p').hide();
				$(this).find('.vq-is-correct').closest('p').show();
			} else {
				$(this).find('.vq-is-correct').closest('p').hide();
				$(this).find('.vq-answer-score').closest('p').show();
			}
		});
	}
	
	// Ajouter une question
	$('#vq-add-question').on('click', function() {
		const quizType = getQuizType();
		const $newQuestion = createQuestionHTML(questionIndex, quizType);
		$('#vq-questions-list').append($newQuestion);
		questionIndex++;
		updateQuestionsData();
	});
	
	// Supprimer une question
	$(document).on('click', '.vq-remove-question', function() {
		if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
			$(this).closest('.vq-question-item').remove();
			renumberQuestions();
			updateQuestionsData();
		}
	});
	
	// Créer le HTML d'une question
	function createQuestionHTML(index, quizType) {
		let answersHTML = '';
		for (let i = 0; i < 4; i++) {
			answersHTML += `
				<div class="vq-answer-item">
					<p>
						<label>
							<strong>Réponse ${i + 1} :</strong>
							<input type="text" class="vq-answer-text widefat" placeholder="Entrez la réponse..." />
						</label>
					</p>
					${quizType === 'trivia' 
						? `<p>
							<label>
								<input type="radio" name="vq_correct_${index}" class="vq-is-correct" value="${i}" />
								Réponse correcte
							</label>
						</p>`
						: `<p>
							<label>
								<strong>Score :</strong>
								<input type="number" class="vq-answer-score small-text" value="0" min="0" step="1" />
							</label>
						</p>`
					}
				</div>
			`;
		}
		
		return $(`
			<div class="vq-question-item" data-index="${index}">
				<div class="vq-question-header">
					<h3>Question ${index + 1}</h3>
					<button type="button" class="vq-remove-question button button-link-delete">
						Supprimer
					</button>
				</div>
				<div class="vq-question-content">
					<p>
						<label>
							<strong>Question :</strong>
							<input type="text" class="vq-question-text widefat" placeholder="Entrez votre question..." />
						</label>
					</p>
					<div class="vq-answers">
						${answersHTML}
					</div>
				</div>
			</div>
		`);
	}
	
	// Renuméroter les questions
	function renumberQuestions() {
		$('#vq-questions-list .vq-question-item').each(function(index) {
			$(this).attr('data-index', index);
			$(this).find('h3').text(`Question ${index + 1}`);
			// Renuméroter les radios
			$(this).find('.vq-is-correct').each(function(i) {
				$(this).attr('name', `vq_correct_${index}`);
				$(this).attr('value', i);
			});
		});
		questionIndex = $('#vq-questions-list .vq-question-item').length;
	}
	
	// Mettre à jour les données dans le champ hidden
	function updateQuestionsData() {
		const questions = [];
		
		$('#vq-questions-list .vq-question-item').each(function() {
			const questionText = $(this).find('.vq-question-text').val();
			const answers = [];
			
			$(this).find('.vq-answer-item').each(function(answerIndex) {
				const answerText = $(this).find('.vq-answer-text').val();
				const quizType = getQuizType();
				
				const answer = {
					text: answerText,
					score: 0,
					is_correct: '0'
				};
				
				if (quizType === 'trivia') {
					const isCorrect = $(this).find('.vq-is-correct:checked').val() === String(answerIndex);
					answer.is_correct = isCorrect ? '1' : '0';
				} else {
					answer.score = parseInt($(this).find('.vq-answer-score').val()) || 0;
				}
				
				answers.push(answer);
			});
			
			if (questionText) {
				questions.push({
					question_text: questionText,
					answers: answers
				});
			}
		});
		
		$('#vq-questions-data').val(JSON.stringify(questions));
	}
	
	// Écouter les changements dans les champs
	$(document).on('input change', '.vq-question-text, .vq-answer-text, .vq-answer-score, .vq-is-correct', function() {
		updateQuestionsData();
	});
	
	// Initialiser au chargement
	$(document).ready(function() {
		initQuestionIndex();
		updateQuestionsUI();
		updateQuestionsData();
	});
	
})(jQuery);

