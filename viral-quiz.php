<?php
/**
 * Plugin Name: ViralQuiz
 * Plugin URI: https://example.com/viral-quiz
 * Description: Plugin WordPress pour créer et afficher des quiz viraux (Trivia & Personnalité)
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: viral-quiz
 * Domain Path: /languages
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Version du plugin
 */
define( 'VQ_VERSION', '1.0.0' );
define( 'VQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Code exécuté lors de l'activation du plugin
 */
function vq_activate() {
	$plugin_dir = untrailingslashit( VQ_PLUGIN_DIR );
	$db_file = $plugin_dir . '/includes/class-db.php';
	
	// Vérifier que le fichier existe avant de le charger
	if ( ! file_exists( $db_file ) ) {
		wp_die( 
			sprintf( 
				'<h1>%s</h1><p>%s</p><p><strong>%s</strong></p>',
				__( 'Erreur d\'activation du plugin ViralQuiz', 'viral-quiz' ),
				__( 'Le fichier class-db.php est manquant. Veuillez vérifier que tous les fichiers du plugin ont été correctement uploadés.', 'viral-quiz' ),
				esc_html( 'Fichier attendu: ' . $db_file )
			)
		);
	}
	
	require_once $db_file;
	
	// Vérifier que la classe existe
	if ( ! class_exists( 'VQ_DB' ) ) {
		wp_die( 
			sprintf( 
				'<h1>%s</h1><p>%s</p>',
				__( 'Erreur d\'activation du plugin ViralQuiz', 'viral-quiz' ),
				__( 'La classe VQ_DB est introuvable après le chargement du fichier.', 'viral-quiz' )
			)
		);
	}
	
	VQ_DB::create_table();
	
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vq_activate' );

/**
 * Code exécuté lors de la désactivation du plugin
 */
function vq_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vq_deactivate' );

/**
 * Chargement des classes du plugin
 */
// Normaliser le chemin du plugin
$plugin_dir = untrailingslashit( VQ_PLUGIN_DIR );

/**
 * Fonction helper pour charger un fichier de manière sécurisée
 *
 * @param string $file_path Chemin du fichier
 * @param string $class_name Nom de la classe attendue (optionnel)
 * @return bool True si chargé avec succès
 */
function vq_load_file( $file_path, $class_name = '' ) {
	if ( ! file_exists( $file_path ) ) {
		$error_msg = sprintf( 
			'ViralQuiz: Fichier manquant - %s (VQ_PLUGIN_DIR: %s)', 
			$file_path, 
			VQ_PLUGIN_DIR 
		);
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $error_msg );
		}
		
		// Afficher une notice admin si possible
		if ( is_admin() && ! did_action( 'admin_notices' ) ) {
			add_action( 'admin_notices', function() use ( $file_path, $error_msg ) {
				echo '<div class="notice notice-error"><p><strong>ViralQuiz Error:</strong> ';
				echo esc_html( $error_msg );
				echo '</p><p>Veuillez vérifier que tous les fichiers du plugin ont été correctement uploadés.</p></div>';
			} );
		}
		
		return false;
	}
	
	require_once $file_path;
	
	// Vérifier que la classe existe si spécifiée
	if ( ! empty( $class_name ) && ! class_exists( $class_name ) ) {
		$error_msg = sprintf( 
			'ViralQuiz: Classe manquante après chargement - %s (Fichier: %s)', 
			$class_name, 
			$file_path 
		);
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $error_msg );
		}
		
		return false;
	}
	
	return true;
}

// Charger les fichiers includes
vq_load_file( $plugin_dir . '/includes/class-cpt.php', 'VQ_CPT' );
vq_load_file( $plugin_dir . '/includes/class-db.php', 'VQ_DB' );
vq_load_file( $plugin_dir . '/includes/class-rest-api.php', 'VQ_REST_API' );
vq_load_file( $plugin_dir . '/includes/class-json-importer.php', 'VQ_JSON_Importer' );
vq_load_file( $plugin_dir . '/includes/helpers.php' );

// Charger les fichiers admin
vq_load_file( $plugin_dir . '/admin/class-admin-menu.php', 'VQ_Admin_Menu' );
vq_load_file( $plugin_dir . '/admin/class-admin-metabox.php', 'VQ_Admin_Metabox' );

// Charger les fichiers public
vq_load_file( $plugin_dir . '/public/class-shortcode.php', 'VQ_Shortcode' );
vq_load_file( $plugin_dir . '/public/class-frontend-render.php', 'VQ_Frontend_Render' );

/**
 * Classe principale du plugin
 */
class ViralQuiz {
	
	/**
	 * Instance unique du plugin
	 */
	private static $instance = null;
	
	/**
	 * Obtenir l'instance unique
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructeur privé
	 */
	private function __construct() {
		$this->init_hooks();
	}
	
	/**
	 * Initialiser les hooks
	 */
	private function init_hooks() {
		// Initialisation du CPT
		add_action( 'init', array( 'VQ_CPT', 'register_post_type' ) );
		
		// Initialisation de l'admin
		if ( is_admin() ) {
			if ( class_exists( 'VQ_Admin_Menu' ) ) {
				new VQ_Admin_Menu();
			}
			if ( class_exists( 'VQ_Admin_Metabox' ) ) {
				new VQ_Admin_Metabox();
			}
		}
		
		// Initialisation du shortcode
		if ( class_exists( 'VQ_Shortcode' ) ) {
			add_action( 'init', array( 'VQ_Shortcode', 'register' ) );
		}
		
		// Enregistrement des scripts et styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		
		// Initialisation de l'API REST
		if ( class_exists( 'VQ_REST_API' ) ) {
			add_action( 'rest_api_init', array( 'VQ_REST_API', 'register_routes' ) );
		}
	}
	
	/**
	 * Enregistrer les scripts et styles frontend
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'vq-frontend-css',
			VQ_PLUGIN_URL . 'public/frontend.css',
			array(),
			VQ_VERSION
		);
		
		wp_enqueue_script(
			'vq-frontend-js',
			VQ_PLUGIN_URL . 'public/frontend.js',
			array( 'jquery' ),
			VQ_VERSION,
			true
		);
		
		wp_localize_script( 'vq-frontend-js', 'vqData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'restUrl' => rest_url( 'viral-quiz/v1/' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}
	
	/**
	 * Enregistrer les scripts et styles admin
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post_type;
		
		if ( 'viral_quiz' !== $post_type ) {
			return;
		}
		
		wp_enqueue_style(
			'vq-admin-css',
			VQ_PLUGIN_URL . 'admin/admin-styles.css',
			array(),
			VQ_VERSION
		);
		
		// Enqueue WordPress media uploader
		wp_enqueue_media();
		
		wp_enqueue_script(
			'vq-admin-js',
			VQ_PLUGIN_URL . 'admin/admin-scripts.js',
			array( 'jquery' ),
			VQ_VERSION,
			true
		);
	}
}

// Démarrer le plugin
ViralQuiz::get_instance();

