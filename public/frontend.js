/**
 * Script frontend pour ViralQuiz
 */
(function($) {
	'use strict';
	
	// Classe principale du quiz
	class ViralQuiz {
		constructor($container) {
			this.$container = $container;
			this.quizId = $container.data('quiz-id');
			this.quizType = $container.data('quiz-type') || 'trivia';
			this.currentQuestion = 0;
			this.answers = {};
			this.totalQuestions = $container.find('.vq-question-wrapper').length;
			this.questionsData = [];
			
			this.init();
		}
		
		init() {
			this.loadQuizData();
			this.bindEvents();
			this.updateProgress();
		}
		
		// Charger les données du quiz via l'API REST
		loadQuizData() {
			const self = this;
			
			$.ajax({
				url: vqData.restUrl + 'quiz/' + this.quizId,
				method: 'GET',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', vqData.nonce);
				},
				success: function(response) {
					if (response.questions && Array.isArray(response.questions)) {
						self.questionsData = response.questions;
						self.quizType = response.type || self.quizType;
					}
				},
				error: function() {
					console.error('Erreur lors du chargement du quiz');
				}
			});
		}
		
		// Lier les événements
		bindEvents() {
			const self = this;
			
			// Bouton suivant
			this.$container.on('click', '.vq-btn-next', function() {
				self.handleNext();
			});
			
			// Sélection d'une réponse
			this.$container.on('change', '.vq-answer-input', function() {
				self.handleAnswerSelect($(this));
			});
			
			// Recommencer
			this.$container.on('click', '.vq-restart-quiz', function() {
				self.restart();
			});
		}
		
		// Gérer la sélection d'une réponse
		handleAnswerSelect($input) {
			const questionIndex = parseInt($input.closest('.vq-question-wrapper').data('question-index'));
			const answerIndex = parseInt($input.val());
			
			this.answers[questionIndex] = answerIndex;
			
			// Activer le bouton suivant
			this.$container.find('.vq-btn-next').prop('disabled', false);
		}
		
		// Gérer le passage à la question suivante
		handleNext() {
			// Vérifier qu'une réponse a été sélectionnée
			const $currentWrapper = this.$container.find('.vq-question-wrapper.vq-active');
			const $selected = $currentWrapper.find('.vq-answer-input:checked');
			
			if ($selected.length === 0) {
				alert('Veuillez sélectionner une réponse avant de continuer.');
				return;
			}
			
			// Passer à la question suivante ou afficher le résultat
			if (this.currentQuestion < this.totalQuestions - 1) {
				this.nextQuestion();
			} else {
				this.showResult();
			}
		}
		
		// Question suivante
		nextQuestion() {
			const $currentWrapper = this.$container.find('.vq-question-wrapper.vq-active');
			$currentWrapper.removeClass('vq-active');
			
			this.currentQuestion++;
			
			const $nextWrapper = this.$container.find('.vq-question-wrapper').eq(this.currentQuestion);
			$nextWrapper.addClass('vq-active');
			
			// Désactiver le bouton suivant jusqu'à ce qu'une réponse soit sélectionnée
			this.$container.find('.vq-btn-next').prop('disabled', true);
			
			// Vérifier si une réponse a déjà été sélectionnée pour cette question
			if (this.answers[this.currentQuestion] !== undefined) {
				const $wrapper = this.$container.find('.vq-question-wrapper').eq(this.currentQuestion);
				$wrapper.find('.vq-answer-input').eq(this.answers[this.currentQuestion]).prop('checked', true);
				this.$container.find('.vq-btn-next').prop('disabled', false);
			}
			
			this.updateProgress();
			
			// Changer le texte du bouton si c'est la dernière question
			if (this.currentQuestion === this.totalQuestions - 1) {
				this.$container.find('.vq-btn-next').text('Voir le résultat');
			} else {
				this.$container.find('.vq-btn-next').text('Question suivante');
			}
		}
		
		// Afficher le résultat
		showResult() {
			const self = this;
			
			// Calculer le score si nécessaire
			let score = null;
			if (this.quizType === 'personality') {
				score = this.calculatePersonalityScore();
			}
			
			// Envoyer les réponses à l'API
			$.ajax({
				url: vqData.restUrl + 'quiz/' + this.quizId + '/submit',
				method: 'POST',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', vqData.nonce);
				},
				data: {
					answers: self.answers,
					session: self.getSession()
				},
				success: function(response) {
					self.displayResult(response.result, response.score);
				},
				error: function() {
					console.error('Erreur lors de la soumission du quiz');
					// Afficher quand même un résultat basique
					if (self.quizType === 'trivia') {
						self.displayResult('Merci d\'avoir participé au quiz !', null);
					} else {
						self.displayResult('Merci d\'avoir participé au quiz !', score);
					}
				}
			});
		}
		
		// Calculer le score pour un quiz personnalité
		calculatePersonalityScore() {
			if (this.quizType !== 'personality' || !this.questionsData.length) {
				return null;
			}
			
			let totalScore = 0;
			
			for (let questionIndex in this.answers) {
				const answerIndex = this.answers[questionIndex];
				if (this.questionsData[questionIndex] && 
					this.questionsData[questionIndex].answers && 
					this.questionsData[questionIndex].answers[answerIndex]) {
					const score = parseInt(this.questionsData[questionIndex].answers[answerIndex].score) || 0;
					totalScore += score;
				}
			}
			
			return totalScore;
		}
		
		// Afficher le résultat
		displayResult(resultText, score) {
			this.$container.find('.vq-questions-wrapper').hide();
			this.$container.find('.vq-navigation').hide();
			this.$container.find('.vq-progress-container').hide();
			
			const $resultWrapper = this.$container.find('.vq-result-wrapper');
			const $resultText = $resultWrapper.find('.vq-result-text');
			
			// Si resultText contient déjà du HTML (pour les résultats de maison), l'utiliser directement
			// Sinon, créer un format simple
			if (resultText && resultText.indexOf('<') !== -1) {
				// C'est du HTML, l'utiliser tel quel
				$resultText.html(resultText);
			} else {
				// Format simple
				let resultHTML = '<p>' + resultText + '</p>';
				if (score !== null && score !== undefined) {
					resultHTML += '<p class="vq-score">Score: ' + score + '</p>';
				}
				$resultText.html(resultHTML);
			}
			
			$resultWrapper.show();
			
			// Scroll vers le résultat
			$('html, body').animate({
				scrollTop: $resultWrapper.offset().top - 100
			}, 500);
		}
		
		// Recommencer le quiz
		restart() {
			this.currentQuestion = 0;
			this.answers = {};
			
			this.$container.find('.vq-question-wrapper').removeClass('vq-active').first().addClass('vq-active');
			this.$container.find('.vq-answer-input').prop('checked', false);
			this.$container.find('.vq-questions-wrapper').show();
			this.$container.find('.vq-navigation').show();
			this.$container.find('.vq-progress-container').show();
			this.$container.find('.vq-result-wrapper').hide();
			this.$container.find('.vq-btn-next').prop('disabled', true).text('Question suivante');
			
			this.updateProgress();
		}
		
		// Mettre à jour la barre de progression
		updateProgress() {
			const progress = ((this.currentQuestion + 1) / this.totalQuestions) * 100;
			this.$container.find('.vq-progress-fill').css('width', progress + '%');
			this.$container.find('.vq-current-question').text(this.currentQuestion + 1);
		}
		
		// Obtenir ou créer une session
		getSession() {
			let session = localStorage.getItem('vq_session_' + this.quizId);
			if (!session) {
				session = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
				localStorage.setItem('vq_session_' + this.quizId, session);
			}
			return session;
		}
	}
	
	// Initialiser les quiz au chargement de la page
	$(document).ready(function() {
		$('.vq-container').each(function() {
			new ViralQuiz($(this));
		});
	});
	
})(jQuery);

