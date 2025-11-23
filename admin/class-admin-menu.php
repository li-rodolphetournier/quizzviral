<?php
/**
 * Classe pour gérer le menu admin
 *
 * @package ViralQuiz
 */

// Si ce fichier est appelé directement, abandonner.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe VQ_Admin_Menu
 */
class VQ_Admin_Menu {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_import_submenu' ) );
		add_action( 'admin_init', array( $this, 'handle_import' ) );
	}
	
	/**
	 * Ajouter le sous-menu d'import
	 */
	public function add_import_submenu() {
		add_submenu_page(
			'edit.php?post_type=viral_quiz',
			__( 'Importer un quiz', 'viral-quiz' ),
			__( 'Importer JSON', 'viral-quiz' ),
			'manage_options',
			'vq-import-json',
			array( $this, 'render_import_page' )
		);
	}
	
	/**
	 * Gérer l'import
	 */
	public function handle_import() {
		if ( ! isset( $_POST['vq_import_json'] ) || ! wp_verify_nonce( $_POST['vq_import_nonce'], 'vq_import_action' ) ) {
			return;
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Vous n\'avez pas les permissions nécessaires.', 'viral-quiz' ) );
		}
		
		// Vérifier si un fichier a été uploadé
		if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK ) {
			$file_path = $_FILES['json_file']['tmp_name'];
			$result = VQ_JSON_Importer::import_from_file( $file_path );
			
			if ( is_wp_error( $result ) ) {
				add_action( 'admin_notices', function() use ( $result ) {
					echo '<div class="notice notice-error"><p>';
					echo esc_html( $result->get_error_message() );
					echo '</p></div>';
				} );
			} else {
				$edit_link = admin_url( 'post.php?action=edit&post=' . $result );
				add_action( 'admin_notices', function() use ( $edit_link ) {
					echo '<div class="notice notice-success"><p>';
					echo __( 'Quiz importé avec succès !', 'viral-quiz' ) . ' ';
					echo '<a href="' . esc_url( $edit_link ) . '">' . __( 'Modifier le quiz', 'viral-quiz' ) . '</a>';
					echo '</p></div>';
				} );
			}
		} else {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-error"><p>';
				echo __( 'Erreur lors de l\'upload du fichier.', 'viral-quiz' );
				echo '</p></div>';
			} );
		}
	}
	
	/**
	 * Rendre la page d'import
	 */
	public function render_import_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Importer un quiz depuis JSON', 'viral-quiz' ); ?></h1>
			
			<div class="card" style="max-width: 800px;">
				<h2><?php esc_html_e( 'Uploader un fichier JSON', 'viral-quiz' ); ?></h2>
				
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'vq_import_action', 'vq_import_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="json_file"><?php esc_html_e( 'Fichier JSON', 'viral-quiz' ); ?></label>
							</th>
							<td>
								<input type="file" name="json_file" id="json_file" accept=".json" required />
								<p class="description">
									<?php esc_html_e( 'Sélectionnez un fichier JSON contenant les données du quiz.', 'viral-quiz' ); ?>
								</p>
							</td>
						</tr>
					</table>
					
					<?php submit_button( __( 'Importer le quiz', 'viral-quiz' ), 'primary', 'vq_import_json' ); ?>
				</form>
			</div>
			
			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2><?php esc_html_e( 'Format JSON attendu', 'viral-quiz' ); ?></h2>
				<pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;"><code>{
  "title": "Titre du quiz",
  "description": "Description du quiz",
  "type": "personality",
  "questions": [
    {
      "question_text": "Votre question ?",
      "answers": [
        {
          "text": "Réponse 1",
          "score": 5,
          "is_correct": false
        },
        {
          "text": "Réponse 2",
          "score": 3,
          "is_correct": false
        }
      ]
    }
  ]
}</code></pre>
			</div>
		</div>
		<?php
	}
}

