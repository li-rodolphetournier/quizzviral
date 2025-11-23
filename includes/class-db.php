<?php
/**
 * Classe pour gérer la base de données
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_DB
 */
class VQ_DB {
	
	/**
	 * Créer la table pour stocker les résultats
	 */
	public static function create_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'vq_results';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			quiz_id bigint(20) NOT NULL,
			user_session varchar(64) NOT NULL,
			answers_json text NOT NULL,
			final_result varchar(255) DEFAULT NULL,
			score int(11) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY quiz_id (quiz_id),
			KEY user_session (user_session)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	
	/**
	 * Insérer un résultat
	 *
	 * @param int    $quiz_id      ID du quiz
	 * @param string $user_session Session utilisateur
	 * @param array  $answers      Réponses
	 * @param string $final_result Résultat final
	 * @param int    $score        Score
	 * @return int|false ID de l'insertion ou false en cas d'erreur
	 */
	public static function insert_result( $quiz_id, $user_session, $answers, $final_result = null, $score = null ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'vq_results';
		
		$result = $wpdb->insert(
			$table_name,
			array(
				'quiz_id'      => absint( $quiz_id ),
				'user_session' => sanitize_text_field( $user_session ),
				'answers_json' => wp_json_encode( $answers ),
				'final_result' => sanitize_text_field( $final_result ),
				'score'        => $score !== null ? absint( $score ) : null,
			),
			array( '%d', '%s', '%s', '%s', '%d' )
		);
		
		if ( $result ) {
			return $wpdb->insert_id;
		}
		
		return false;
	}
	
	/**
	 * Obtenir les résultats d'un quiz
	 *
	 * @param int $quiz_id ID du quiz
	 * @return array Résultats
	 */
	public static function get_quiz_results( $quiz_id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'vq_results';
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE quiz_id = %d ORDER BY created_at DESC",
				absint( $quiz_id )
			),
			ARRAY_A
		);
		
		return $results;
	}
}

