<?php
/**
 * Classe pour gérer le rendu frontend
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_Frontend_Render
 */
class VQ_Frontend_Render {
	
	/**
	 * Rendre un quiz
	 *
	 * @param int $quiz_id ID du quiz
	 * @return string HTML du quiz
	 */
	public static function render_quiz( $quiz_id ) {
		$quiz = get_post( $quiz_id );
		
		if ( ! $quiz || 'viral_quiz' !== $quiz->post_type ) {
			return '<p>' . esc_html__( 'Quiz introuvable', 'viral-quiz' ) . '</p>';
		}
		
		$questions = get_post_meta( $quiz_id, '_vq_questions', true );
		$quiz_type = get_post_meta( $quiz_id, '_vq_quiz_type', true );
		
		if ( ! $questions || ! is_array( $questions ) || empty( $questions ) ) {
			return '<p>' . esc_html__( 'Ce quiz ne contient aucune question', 'viral-quiz' ) . '</p>';
		}
		
		if ( ! $quiz_type ) {
			$quiz_type = 'trivia';
		}
		
		ob_start();
		?>
		<div class="vq-container" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>" data-quiz-type="<?php echo esc_attr( $quiz_type ); ?>">
			<div class="vq-quiz-header">
				<h2 class="vq-quiz-title"><?php echo esc_html( $quiz->post_title ); ?></h2>
				<?php if ( ! empty( $quiz->post_content ) ) : ?>
					<div class="vq-quiz-description">
						<?php echo wp_kses_post( wpautop( $quiz->post_content ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			
			<div class="vq-progress-container">
				<div class="vq-progress-bar">
					<div class="vq-progress-fill" style="width: 0%;"></div>
				</div>
				<span class="vq-progress-text">
					<span class="vq-current-question">1</span> / <span class="vq-total-questions"><?php echo esc_html( count( $questions ) ); ?></span>
				</span>
			</div>
			
			<div class="vq-questions-wrapper">
				<?php foreach ( $questions as $index => $question ) : ?>
					<div class="vq-question-wrapper <?php echo 0 === $index ? 'vq-active' : ''; ?>" data-question-index="<?php echo esc_attr( $index ); ?>">
						<div class="vq-question">
							<?php if ( ! empty( $question['image'] ) ) : ?>
								<div class="vq-question-image">
									<img src="<?php echo esc_url( $question['image'] ); ?>" alt="<?php echo esc_attr( $question['question_text'] ?? '' ); ?>" />
								</div>
							<?php endif; ?>
							
							<h3 class="vq-question-title">
								<?php echo esc_html( $question['question_text'] ?? '' ); ?>
							</h3>
							
							<div class="vq-answers-list">
								<?php if ( isset( $question['answers'] ) && is_array( $question['answers'] ) ) : ?>
									<?php foreach ( $question['answers'] as $answer_index => $answer ) : ?>
										<?php if ( ! empty( $answer['text'] ) ) : ?>
											<label class="vq-answer-option">
												<input type="radio" 
													name="vq_answer_<?php echo esc_attr( $index ); ?>" 
													value="<?php echo esc_attr( $answer_index ); ?>" 
													class="vq-answer-input" />
												<?php if ( ! empty( $answer['image'] ) ) : ?>
													<div class="vq-answer-image">
														<img src="<?php echo esc_url( $answer['image'] ); ?>" alt="<?php echo esc_attr( $answer['text'] ); ?>" />
													</div>
												<?php endif; ?>
												<span class="vq-answer-text"><?php echo esc_html( $answer['text'] ); ?></span>
											</label>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<div class="vq-result-wrapper" style="display: none;">
				<div class="vq-result-content">
					<h3 class="vq-result-title"><?php esc_html_e( 'Résultat', 'viral-quiz' ); ?></h3>
					<div class="vq-result-text"></div>
					<button type="button" class="vq-restart-quiz button">
						<?php esc_html_e( 'Recommencer', 'viral-quiz' ); ?>
					</button>
				</div>
			</div>
			
			<div class="vq-navigation">
				<button type="button" class="vq-btn vq-btn-next" disabled>
					<?php esc_html_e( 'Question suivante', 'viral-quiz' ); ?>
				</button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

