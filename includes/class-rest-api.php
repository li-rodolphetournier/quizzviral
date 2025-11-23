<?php
/**
 * Classe pour gérer l'API REST
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_REST_API
 */
class VQ_REST_API {
	
	/**
	 * Namespace de l'API
	 */
	const NAMESPACE = 'viral-quiz/v1';
	
	/**
	 * Enregistrer les routes REST
	 */
	public static function register_routes() {
		// GET /quiz/{id}
		register_rest_route(
			self::NAMESPACE,
			'/quiz/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_quiz' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);
		
		// POST /quiz/{id}/submit
		register_rest_route(
			self::NAMESPACE,
			'/quiz/(?P<id>\d+)/submit',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'submit_quiz' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'      => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'answers' => array(
						'required' => true,
						'type'     => 'array',
					),
					'session' => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);
	}
	
	/**
	 * Obtenir un quiz
	 *
	 * @param WP_REST_Request $request Requête REST
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_quiz( $request ) {
		$quiz_id = absint( $request['id'] );
		
		$quiz = get_post( $quiz_id );
		
		if ( ! $quiz || 'viral_quiz' !== $quiz->post_type ) {
			return new WP_Error(
				'quiz_not_found',
				__( 'Quiz introuvable', 'viral-quiz' ),
				array( 'status' => 404 )
			);
		}
		
		$questions = get_post_meta( $quiz_id, '_vq_questions', true );
		$quiz_type = get_post_meta( $quiz_id, '_vq_quiz_type', true );
		
		if ( ! $questions ) {
			$questions = array();
		}
		
		return new WP_REST_Response(
			array(
				'id'        => $quiz_id,
				'title'     => $quiz->post_title,
				'content'   => $quiz->post_content,
				'type'      => $quiz_type ? $quiz_type : 'trivia',
				'questions' => $questions,
			),
			200
		);
	}
	
	/**
	 * Soumettre un quiz
	 *
	 * @param WP_REST_Request $request Requête REST
	 * @return WP_REST_Response|WP_Error
	 */
	public static function submit_quiz( $request ) {
		$quiz_id = absint( $request['id'] );
		$answers = $request['answers'];
		$session = $request->get_param( 'session' );
		
		if ( ! $session ) {
			$session = wp_generate_password( 32, false );
		}
		
		// Vérifier que le quiz existe
		$quiz = get_post( $quiz_id );
		if ( ! $quiz || 'viral_quiz' !== $quiz->post_type ) {
			return new WP_Error(
				'quiz_not_found',
				__( 'Quiz introuvable', 'viral-quiz' ),
				array( 'status' => 404 )
			);
		}
		
		// Récupérer les questions
		$questions = get_post_meta( $quiz_id, '_vq_questions', true );
		$quiz_type = get_post_meta( $quiz_id, '_vq_quiz_type', true );
		
		if ( ! $questions || ! is_array( $questions ) ) {
			return new WP_Error(
				'invalid_quiz',
				__( 'Quiz invalide', 'viral-quiz' ),
				array( 'status' => 400 )
			);
		}
		
		// Calculer le résultat
		$result = self::calculate_result( $questions, $answers, $quiz_type );
		
		// Enregistrer en base
		$score = null;
		if ( 'personality' === $quiz_type ) {
			$score = isset( $result['score'] ) ? $result['score'] : null;
		}
		
		VQ_DB::insert_result(
			$quiz_id,
			$session,
			$answers,
			$result['final_result'],
			$score
		);
		
		return new WP_REST_Response(
			array(
				'success'      => true,
				'result'       => $result['final_result'],
				'score'        => $score,
				'session'      => $session,
			),
			200
		);
	}
	
	/**
	 * Calculer le résultat du quiz
	 *
	 * @param array  $questions Questions du quiz
	 * @param array  $answers   Réponses de l'utilisateur
	 * @param string $quiz_type Type de quiz
	 * @return array Résultat
	 */
	private static function calculate_result( $questions, $answers, $quiz_type ) {
		if ( 'personality' === $quiz_type ) {
			// Quiz personnalité : calculer les scores par maison (pour quiz Poudlard)
			$houses_scores = array(
				'gryffondor' => 0,
				'serdaigle'  => 0,
				'serpentard' => 0,
				'poufsouffle' => 0,
			);
			
			$total_score = 0;
			$has_house_data = false;
			
			foreach ( $answers as $question_index => $answer_index ) {
				if ( isset( $questions[ $question_index ] ) && isset( $questions[ $question_index ]['answers'][ $answer_index ] ) ) {
					$answer = $questions[ $question_index ]['answers'][ $answer_index ];
					$score = isset( $answer['score'] ) ? intval( $answer['score'] ) : 0;
					$total_score += $score;
					
					// Si la réponse a un attribut "house", l'utiliser
					if ( isset( $answer['house'] ) && ! empty( $answer['house'] ) ) {
						$house = strtolower( sanitize_text_field( $answer['house'] ) );
						if ( isset( $houses_scores[ $house ] ) ) {
							$houses_scores[ $house ] += $score;
							$has_house_data = true;
						}
					}
				}
			}
			
			// Si on a des données de maison, déterminer la maison gagnante
			if ( $has_house_data ) {
				$winning_house = array_search( max( $houses_scores ), $houses_scores );
				$house_info = self::get_house_info( $winning_house );
				
				$result_text = sprintf( 
					'<div class="vq-house-result"><h3 class="vq-house-title">Vous appartenez à la maison %s !</h3><p class="vq-house-description">%s</p><p class="vq-house-score"><strong>Votre score :</strong> %d points</p></div>',
					esc_html( $house_info['name'] ),
					esc_html( $house_info['description'] ),
					$houses_scores[ $winning_house ]
				);
				
				return array(
					'final_result' => $result_text,
					'score'        => $houses_scores[ $winning_house ],
					'house'        => $winning_house,
					'houses_scores' => $houses_scores,
				);
			} else {
				// Pas de données de maison, afficher le score total classique
				$result_text = sprintf( __( 'Votre score est de %d points', 'viral-quiz' ), $total_score );
				
				return array(
					'final_result' => $result_text,
					'score'        => $total_score,
				);
			}
		} else {
			// Quiz trivia : compter les bonnes réponses
			$correct = 0;
			$total   = count( $questions );
			
			foreach ( $answers as $question_index => $answer_index ) {
				if ( isset( $questions[ $question_index ] ) && isset( $questions[ $question_index ]['answers'][ $answer_index ] ) ) {
					$is_correct = isset( $questions[ $question_index ]['answers'][ $answer_index ]['is_correct'] ) 
						&& $questions[ $question_index ]['answers'][ $answer_index ]['is_correct'] === '1';
					
					if ( $is_correct ) {
						$correct++;
					}
				}
			}
			
			$result_text = sprintf( __( 'Vous avez obtenu %d/%d bonnes réponses', 'viral-quiz' ), $correct, $total );
			
			return array(
				'final_result' => $result_text,
				'score'        => $correct,
			);
		}
	}
	
	/**
	 * Obtenir les informations d'une maison de Poudlard
	 *
	 * @param string $house_key Clé de la maison
	 * @return array Informations de la maison
	 */
	private static function get_house_info( $house_key ) {
		$houses = array(
			'gryffondor' => array(
				'name' => 'Gryffondor 🦁',
				'description' => 'La maison Gryffondor valorise le courage, la bravoure et la détermination. Les membres de cette maison sont connus pour leur audace et leur sens de la justice.',
			),
			'serdaigle' => array(
				'name' => 'Serdaigle 🦅',
				'description' => 'La maison Serdaigle valorise la sagesse, l\'intellect, la curiosité et l\'apprentissage. Les membres de cette maison sont réputés pour leur intelligence et leur créativité.',
			),
			'serpentard' => array(
				'name' => 'Serpentard 🐍',
				'description' => 'La maison Serpentard valorise l\'ambition, la ruse, la détermination et la fierté. Les membres de cette maison sont connus pour leur ambition et leur détermination à réussir.',
			),
			'poufsouffle' => array(
				'name' => 'Poufsouffle 🦡',
				'description' => 'La maison Poufsouffle valorise la loyauté, la patience, la modestie et la justice. Les membres de cette maison sont réputés pour leur gentillesse et leur sens de l\'équité.',
			),
		);
		
		return isset( $houses[ $house_key ] ) ? $houses[ $house_key ] : array( 'name' => 'Inconnue', 'description' => '' );
	}
}

