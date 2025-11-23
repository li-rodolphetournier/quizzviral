<?php
/**
 * Classe pour gérer le shortcode
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_Shortcode
 */
class VQ_Shortcode {
	
	/**
	 * Enregistrer le shortcode
	 */
	public static function register() {
		add_shortcode( 'viral_quiz', array( __CLASS__, 'render' ) );
	}
	
	/**
	 * Rendre le shortcode
	 *
	 * @param array $atts Attributs du shortcode
	 * @return string HTML du quiz
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'viral_quiz'
		);
		
		$quiz_id = absint( $atts['id'] );
		
		if ( ! $quiz_id ) {
			return '<p>' . esc_html__( 'ID de quiz manquant', 'viral-quiz' ) . '</p>';
		}
		
		$quiz = get_post( $quiz_id );
		
		if ( ! $quiz || 'viral_quiz' !== $quiz->post_type ) {
			return '<p>' . esc_html__( 'Quiz introuvable', 'viral-quiz' ) . '</p>';
		}
		
		// Utiliser la classe de rendu frontend
		return VQ_Frontend_Render::render_quiz( $quiz_id );
	}
}

