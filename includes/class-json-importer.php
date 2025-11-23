<?php
/**
 * Classe pour importer des quiz depuis un fichier JSON
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_JSON_Importer
 */
class VQ_JSON_Importer {
	
	/**
	 * Importer un quiz depuis un fichier JSON
	 *
	 * @param string $json_file_path Chemin vers le fichier JSON
	 * @return int|WP_Error ID du quiz créé ou WP_Error en cas d'erreur
	 */
	public static function import_from_file( $json_file_path ) {
		if ( ! file_exists( $json_file_path ) ) {
			return new WP_Error( 'file_not_found', 'Le fichier JSON est introuvable.' );
		}
		
		$json_content = file_get_contents( $json_file_path );
		$quiz_data = json_decode( $json_content, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', 'Le fichier JSON est invalide : ' . json_last_error_msg() );
		}
		
		return self::import_from_array( $quiz_data );
	}
	
	/**
	 * Importer un quiz depuis un tableau de données
	 *
	 * @param array $quiz_data Données du quiz
	 * @return int|WP_Error ID du quiz créé ou WP_Error en cas d'erreur
	 */
	public static function import_from_array( $quiz_data ) {
		// Vérifier les données requises
		if ( ! isset( $quiz_data['title'] ) || empty( $quiz_data['title'] ) ) {
			return new WP_Error( 'missing_title', 'Le titre du quiz est requis.' );
		}
		
		if ( ! isset( $quiz_data['questions'] ) || ! is_array( $quiz_data['questions'] ) || empty( $quiz_data['questions'] ) ) {
			return new WP_Error( 'missing_questions', 'Le quiz doit contenir au moins une question.' );
		}
		
		// Créer le post du quiz
		$post_data = array(
			'post_title'   => sanitize_text_field( $quiz_data['title'] ),
			'post_content' => isset( $quiz_data['description'] ) ? wp_kses_post( $quiz_data['description'] ) : '',
			'post_status'  => 'draft', // Créer en brouillon
			'post_type'    => 'viral_quiz',
		);
		
		$quiz_id = wp_insert_post( $post_data );
		
		if ( is_wp_error( $quiz_id ) ) {
			return $quiz_id;
		}
		
		// Définir le type de quiz
		$quiz_type = isset( $quiz_data['type'] ) && in_array( $quiz_data['type'], array( 'trivia', 'personality' ), true )
			? $quiz_data['type']
			: 'trivia';
		
		update_post_meta( $quiz_id, '_vq_quiz_type', $quiz_type );
		
		// Traiter les questions
		$formatted_questions = array();
		
		foreach ( $quiz_data['questions'] as $question_data ) {
			if ( ! isset( $question_data['question_text'] ) || empty( $question_data['question_text'] ) ) {
				continue;
			}
			
			if ( ! isset( $question_data['answers'] ) || ! is_array( $question_data['answers'] ) || empty( $question_data['answers'] ) ) {
				continue;
			}
			
			$formatted_question = array(
				'question_text' => sanitize_text_field( $question_data['question_text'] ),
				'answers'       => array(),
			);
			
			// Traiter les réponses
			foreach ( $question_data['answers'] as $answer_data ) {
				if ( ! isset( $answer_data['text'] ) || empty( $answer_data['text'] ) ) {
					continue;
				}
				
				$formatted_answer = array(
					'text'       => sanitize_text_field( $answer_data['text'] ),
					'score'      => isset( $answer_data['score'] ) ? absint( $answer_data['score'] ) : 0,
					'is_correct' => isset( $answer_data['is_correct'] ) && $answer_data['is_correct'] ? '1' : '0',
					'house'      => isset( $answer_data['house'] ) ? strtolower( sanitize_text_field( $answer_data['house'] ) ) : '',
				);
				
				$formatted_question['answers'][] = $formatted_answer;
			}
			
			// S'assurer qu'il y a au moins 4 réponses (remplir avec des réponses vides si nécessaire)
			while ( count( $formatted_question['answers'] ) < 4 ) {
				$formatted_question['answers'][] = array(
					'text'       => '',
					'score'      => 0,
					'is_correct' => '0',
				);
			}
			
			$formatted_questions[] = $formatted_question;
		}
		
		// Sauvegarder les questions
		if ( ! empty( $formatted_questions ) ) {
			update_post_meta( $quiz_id, '_vq_questions', $formatted_questions );
		}
		
		// Image à la une (optionnel)
		if ( isset( $quiz_data['featured_image_url'] ) && ! empty( $quiz_data['featured_image_url'] ) ) {
			self::set_featured_image_from_url( $quiz_id, $quiz_data['featured_image_url'] );
		}
		
		return $quiz_id;
	}
	
	/**
	 * Définir l'image à la une depuis une URL
	 *
	 * @param int    $post_id ID du post
	 * @param string $image_url URL de l'image
	 * @return int|false ID de l'attachment ou false
	 */
	private static function set_featured_image_from_url( $post_id, $image_url ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		$tmp = download_url( $image_url );
		
		if ( is_wp_error( $tmp ) ) {
			return false;
		}
		
		$file_array = array(
			'name'     => basename( $image_url ),
			'tmp_name' => $tmp,
		);
		
		$id = media_handle_sideload( $file_array, $post_id );
		
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return false;
		}
		
		set_post_thumbnail( $post_id, $id );
		
		return $id;
	}
}

