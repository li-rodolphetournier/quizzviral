<?php
/**
 * Fonctions helper
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Générer une session unique pour l'utilisateur
 *
 * @return string Session ID
 */
function vq_generate_session() {
	if ( ! isset( $_COOKIE['vq_session'] ) ) {
		$session = wp_generate_password( 32, false );
		setcookie( 'vq_session', $session, time() + ( 86400 * 30 ), '/' );
		return $session;
	}
	return sanitize_text_field( $_COOKIE['vq_session'] );
}

/**
 * Sanitizer pour les questions
 *
 * @param array $questions Questions brutes
 * @return array Questions sanitizées
 */
function vq_sanitize_questions( $questions ) {
	if ( ! is_array( $questions ) ) {
		return array();
	}
	
	$sanitized = array();
	
	foreach ( $questions as $question ) {
		$sanitized_question = array(
			'question_text' => sanitize_text_field( $question['question_text'] ?? '' ),
			'image'         => isset( $question['image'] ) ? esc_url_raw( $question['image'] ) : '',
			'image_id'      => isset( $question['image_id'] ) ? absint( $question['image_id'] ) : 0,
			'answers'       => array(),
		);
		
		if ( isset( $question['answers'] ) && is_array( $question['answers'] ) ) {
			foreach ( $question['answers'] as $answer ) {
				$sanitized_question['answers'][] = array(
					'text'       => sanitize_text_field( $answer['text'] ?? '' ),
					'score'      => isset( $answer['score'] ) ? absint( $answer['score'] ) : 0,
					'is_correct' => isset( $answer['is_correct'] ) ? sanitize_text_field( $answer['is_correct'] ) : '0',
					'house'      => isset( $answer['house'] ) ? strtolower( sanitize_text_field( $answer['house'] ) ) : '',
					'image'      => isset( $answer['image'] ) ? esc_url_raw( $answer['image'] ) : '',
					'image_id'   => isset( $answer['image_id'] ) ? absint( $answer['image_id'] ) : 0,
				);
			}
		}
		
		$sanitized[] = $sanitized_question;
	}
	
	return $sanitized;
}

