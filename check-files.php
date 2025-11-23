<?php
/**
 * Script de vérification des fichiers du plugin ViralQuiz
 * 
 * Uploadez ce fichier dans le dossier du plugin et accédez-y via navigateur
 * pour vérifier que tous les fichiers sont présents.
 * 
 * Exemple: https://votresite.com/wp-content/plugins/viral-quiz-3/check-files.php
 */

// Sécurité basique
if ( ! defined( 'ABSPATH' ) ) {
	// Si appelé directement, permettre l'accès pour diagnostic
	// En production, vous pouvez supprimer ce fichier après vérification
}

$plugin_dir = dirname( __FILE__ );
$required_files = array(
	'viral-quiz.php',
	'includes/class-cpt.php',
	'includes/class-db.php',
	'includes/class-rest-api.php',
	'includes/helpers.php',
	'admin/class-admin-menu.php',
	'admin/class-admin-metabox.php',
	'public/class-shortcode.php',
	'public/class-frontend-render.php',
	'admin/admin-scripts.js',
	'admin/admin-styles.css',
	'public/frontend.js',
	'public/frontend.css',
);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Vérification des fichiers - ViralQuiz</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
		.container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; }
		h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
		.file-list { margin: 20px 0; }
		.file-item { padding: 10px; margin: 5px 0; border-radius: 4px; }
		.file-ok { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
		.file-missing { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
		.path { font-family: monospace; font-size: 12px; color: #666; margin-top: 5px; }
		.summary { margin-top: 30px; padding: 15px; border-radius: 4px; font-weight: bold; }
		.summary.ok { background: #d4edda; color: #155724; }
		.summary.error { background: #f8d7da; color: #721c24; }
	</style>
</head>
<body>
	<div class="container">
		<h1>🔍 Vérification des fichiers - ViralQuiz</h1>
		<p><strong>Dossier du plugin:</strong> <code><?php echo esc_html( $plugin_dir ); ?></code></p>
		
		<div class="file-list">
			<?php
			$all_ok = true;
			$missing_files = array();
			
			foreach ( $required_files as $file ) {
				$file_path = $plugin_dir . '/' . $file;
				$exists = file_exists( $file_path );
				
				if ( ! $exists ) {
					$all_ok = false;
					$missing_files[] = $file;
				}
				?>
				<div class="file-item <?php echo $exists ? 'file-ok' : 'file-missing'; ?>">
					<?php echo $exists ? '✓' : '✗'; ?> 
					<strong><?php echo esc_html( $file ); ?></strong>
					<div class="path"><?php echo esc_html( $file_path ); ?></div>
				</div>
				<?php
			}
			?>
		</div>
		
		<div class="summary <?php echo $all_ok ? 'ok' : 'error'; ?>">
			<?php if ( $all_ok ) : ?>
				✅ Tous les fichiers sont présents ! Le plugin devrait fonctionner correctement.
			<?php else : ?>
				❌ <strong><?php echo count( $missing_files ); ?> fichier(s) manquant(s):</strong><br>
				<?php foreach ( $missing_files as $file ) : ?>
					- <?php echo esc_html( $file ); ?><br>
				<?php endforeach; ?>
				<br>
				<strong>Solution:</strong> Vérifiez que le ZIP a été correctement extrait et que tous les fichiers ont été uploadés dans le dossier du plugin.
			<?php endif; ?>
		</div>
		
		<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
			<p><strong>Note:</strong> Vous pouvez supprimer ce fichier (check-files.php) après vérification pour des raisons de sécurité.</p>
		</div>
	</div>
</body>
</html>

