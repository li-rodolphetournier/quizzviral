<?php
/**
 * Classe pour gérer la metabox admin
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_Admin_Metabox
 */
class VQ_Admin_Metabox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
	}
	
	/**
	 * Ajouter les metaboxes
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'vq_quiz_settings',
			__( 'Paramètres du Quiz', 'viral-quiz' ),
			array( $this, 'render_quiz_settings' ),
			'viral_quiz',
			'side',
			'default'
		);
		
		add_meta_box(
			'vq_questions',
			__( 'Questions & Réponses', 'viral-quiz' ),
			array( $this, 'render_questions_metabox' ),
			'viral_quiz',
			'normal',
			'high'
		);
	}
	
	/**
	 * Rendre la metabox des paramètres
	 *
	 * @param WP_Post $post Post object
	 */
	public function render_quiz_settings( $post ) {
		wp_nonce_field( 'vq_save_meta', 'vq_meta_nonce' );
		
		$quiz_type = get_post_meta( $post->ID, '_vq_quiz_type', true );
		if ( ! $quiz_type ) {
			$quiz_type = 'trivia';
		}
		
		?>
		<p>
			<label for="vq_quiz_type">
				<strong><?php esc_html_e( 'Type de quiz', 'viral-quiz' ); ?></strong>
			</label>
		</p>
		<p>
			<select name="vq_quiz_type" id="vq_quiz_type" style="width: 100%;">
				<option value="trivia" <?php selected( $quiz_type, 'trivia' ); ?>>
					<?php esc_html_e( 'Trivia (Questions/Réponses)', 'viral-quiz' ); ?>
				</option>
				<option value="personality" <?php selected( $quiz_type, 'personality' ); ?>>
					<?php esc_html_e( 'Personnalité (Scoring)', 'viral-quiz' ); ?>
				</option>
			</select>
		</p>
		<p class="description">
			<?php esc_html_e( 'Trivia : réponses correctes/incorrectes. Personnalité : scoring par points.', 'viral-quiz' ); ?>
		</p>
		<?php
	}
	
	/**
	 * Rendre la metabox des questions
	 *
	 * @param WP_Post $post Post object
	 */
	public function render_questions_metabox( $post ) {
		$questions = get_post_meta( $post->ID, '_vq_questions', true );
		$quiz_type = get_post_meta( $post->ID, '_vq_quiz_type', true );
		
		if ( ! $questions || ! is_array( $questions ) ) {
			$questions = array();
		}
		
		if ( ! $quiz_type ) {
			$quiz_type = 'trivia';
		}
		
		?>
		<div id="vq-questions-container" data-quiz-type="<?php echo esc_attr( $quiz_type ); ?>">
			<div id="vq-questions-list">
				<?php if ( ! empty( $questions ) ) : ?>
					<?php foreach ( $questions as $index => $question ) : ?>
						<?php $this->render_question_item( $index, $question, $quiz_type ); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			
			<button type="button" id="vq-add-question" class="button button-primary">
				<?php esc_html_e( '+ Ajouter une question', 'viral-quiz' ); ?>
			</button>
		</div>
		
		<input type="hidden" id="vq-questions-data" name="vq_questions_data" value="<?php echo esc_attr( wp_json_encode( $questions ) ); ?>" />
		<?php
	}
	
	/**
	 * Rendre un item de question
	 *
	 * @param int    $index     Index de la question
	 * @param array  $question  Données de la question
	 * @param string $quiz_type Type de quiz
	 */
	private function render_question_item( $index, $question, $quiz_type ) {
		$question_text = isset( $question['question_text'] ) ? esc_attr( $question['question_text'] ) : '';
		$answers       = isset( $question['answers'] ) && is_array( $question['answers'] ) ? $question['answers'] : array();
		
		// S'assurer qu'il y a 4 réponses
		while ( count( $answers ) < 4 ) {
			$answers[] = array(
				'text'       => '',
				'score'      => 0,
				'is_correct' => '0',
			);
		}
		
		?>
		<div class="vq-question-item" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="vq-question-header">
				<h3><?php echo esc_html( sprintf( __( 'Question %d', 'viral-quiz' ), $index + 1 ) ); ?></h3>
				<button type="button" class="vq-remove-question button button-link-delete">
					<?php esc_html_e( 'Supprimer', 'viral-quiz' ); ?>
				</button>
			</div>
			
			<div class="vq-question-content">
				<p>
					<label>
						<strong><?php esc_html_e( 'Question :', 'viral-quiz' ); ?></strong>
						<input type="text" class="vq-question-text widefat" 
							value="<?php echo esc_attr( $question_text ); ?>" 
							placeholder="<?php esc_attr_e( 'Entrez votre question...', 'viral-quiz' ); ?>" />
					</label>
				</p>
				
				<div class="vq-answers">
					<?php foreach ( $answers as $answer_index => $answer ) : ?>
						<div class="vq-answer-item">
							<p>
								<label>
									<strong><?php echo esc_html( sprintf( __( 'Réponse %d :', 'viral-quiz' ), $answer_index + 1 ) ); ?></strong>
									<input type="text" class="vq-answer-text widefat" 
										value="<?php echo esc_attr( $answer['text'] ?? '' ); ?>" 
										placeholder="<?php esc_attr_e( 'Entrez la réponse...', 'viral-quiz' ); ?>" />
								</label>
							</p>
							
							<?php if ( 'trivia' === $quiz_type ) : ?>
								<p>
									<label>
										<input type="radio" name="vq_correct_<?php echo esc_attr( $index ); ?>" 
											class="vq-is-correct" 
											value="<?php echo esc_attr( $answer_index ); ?>" 
											<?php checked( isset( $answer['is_correct'] ) && $answer['is_correct'] === '1' ); ?> />
										<?php esc_html_e( 'Réponse correcte', 'viral-quiz' ); ?>
									</label>
								</p>
							<?php else : ?>
								<p>
									<label>
										<strong><?php esc_html_e( 'Score :', 'viral-quiz' ); ?></strong>
										<input type="number" class="vq-answer-score small-text" 
											value="<?php echo esc_attr( $answer['score'] ?? 0 ); ?>" 
											min="0" step="1" />
									</label>
								</p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Sauvegarder les metaboxes
	 *
	 * @param int $post_id ID du post
	 */
	public function save_meta_boxes( $post_id ) {
		// Vérifications de sécurité
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! isset( $_POST['vq_meta_nonce'] ) || ! wp_verify_nonce( $_POST['vq_meta_nonce'], 'vq_save_meta' ) ) {
			return;
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		if ( 'viral_quiz' !== get_post_type( $post_id ) ) {
			return;
		}
		
		// Sauvegarder le type de quiz
		if ( isset( $_POST['vq_quiz_type'] ) ) {
			$quiz_type = sanitize_text_field( $_POST['vq_quiz_type'] );
			if ( in_array( $quiz_type, array( 'trivia', 'personality' ), true ) ) {
				update_post_meta( $post_id, '_vq_quiz_type', $quiz_type );
			}
		}
		
		// Sauvegarder les questions
		if ( isset( $_POST['vq_questions_data'] ) ) {
			$questions_data = json_decode( wp_unslash( $_POST['vq_questions_data'] ), true );
			
			if ( is_array( $questions_data ) ) {
				$sanitized_questions = vq_sanitize_questions( $questions_data );
				update_post_meta( $post_id, '_vq_questions', $sanitized_questions );
			}
		}
	}
}

