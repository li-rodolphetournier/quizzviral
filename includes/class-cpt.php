<?php
/**
 * Classe pour gérer le Custom Post Type
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_CPT
 */
class VQ_CPT {
	
	/**
	 * Enregistrer le Custom Post Type
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Quiz Viraux', 'Post Type General Name', 'viral-quiz' ),
			'singular_name'         => _x( 'Quiz Viral', 'Post Type Singular Name', 'viral-quiz' ),
			'menu_name'             => __( 'Quiz Viraux', 'viral-quiz' ),
			'name_admin_bar'        => __( 'Quiz Viral', 'viral-quiz' ),
			'archives'              => __( 'Archives des Quiz', 'viral-quiz' ),
			'attributes'            => __( 'Attributs du Quiz', 'viral-quiz' ),
			'parent_item_colon'     => __( 'Quiz parent:', 'viral-quiz' ),
			'all_items'             => __( 'Tous les Quiz', 'viral-quiz' ),
			'add_new_item'          => __( 'Ajouter un nouveau Quiz', 'viral-quiz' ),
			'add_new'               => __( 'Ajouter un nouveau', 'viral-quiz' ),
			'new_item'              => __( 'Nouveau Quiz', 'viral-quiz' ),
			'edit_item'             => __( 'Modifier le Quiz', 'viral-quiz' ),
			'update_item'           => __( 'Mettre à jour le Quiz', 'viral-quiz' ),
			'view_item'             => __( 'Voir le Quiz', 'viral-quiz' ),
			'view_items'            => __( 'Voir les Quiz', 'viral-quiz' ),
			'search_items'          => __( 'Rechercher un Quiz', 'viral-quiz' ),
			'not_found'             => __( 'Aucun Quiz trouvé', 'viral-quiz' ),
			'not_found_in_trash'    => __( 'Aucun Quiz trouvé dans la corbeille', 'viral-quiz' ),
			'featured_image'        => __( 'Image à la une', 'viral-quiz' ),
			'set_featured_image'    => __( 'Définir l\'image à la une', 'viral-quiz' ),
			'remove_featured_image' => __( 'Supprimer l\'image à la une', 'viral-quiz' ),
			'use_featured_image'    => __( 'Utiliser comme image à la une', 'viral-quiz' ),
		);
		
		$args = array(
			'label'                 => __( 'Quiz Viral', 'viral-quiz' ),
			'description'           => __( 'Quiz viraux (Trivia & Personnalité)', 'viral-quiz' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-clipboard',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
		);
		
		register_post_type( 'viral_quiz', $args );
	}
}

