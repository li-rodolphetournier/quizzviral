<?php
/**
 * Script d'aide à l'installation - ViralQuiz
 * 
 * Ce script crée les dossiers manquants et vérifie la structure.
 * Uploadez ce fichier dans le dossier du plugin et exécutez-le une fois.
 * 
 * URL: https://votresite.com/wp-content/plugins/viral-quiz-6/install-helper.php
 */

// Sécurité basique - permet l'accès direct pour diagnostic
$plugin_dir = dirname( __FILE__ );

// Liste des dossiers nécessaires
$required_dirs = array(
	'includes',
	'admin',
	'public',
	'assets/css',
	'assets/js',
);

// Liste des fichiers critiques
$required_files = array(
	'includes/class-cpt.php',
	'includes/class-db.php',
	'includes/class-rest-api.php',
	'includes/helpers.php',
	'admin/class-admin-menu.php',
	'admin/class-admin-metabox.php',
	'public/class-shortcode.php',
	'public/class-frontend-render.php',
);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Assistant d'installation - ViralQuiz</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
		.container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 900px; }
		h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
		.section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
		.success { color: #28a745; font-weight: bold; }
		.error { color: #dc3545; font-weight: bold; }
		.warning { color: #ffc107; font-weight: bold; }
		.code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
		.file-list { margin: 10px 0; }
		.file-item { padding: 5px; margin: 2px 0; }
		.file-ok { color: #28a745; }
		.file-missing { color: #dc3545; }
		.button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; }
		.button:hover { background: #005a87; }
	</style>
</head>
<body>
	<div class="container">
		<h1>🔧 Assistant d'installation - ViralQuiz</h1>
		<p><strong>Dossier du plugin:</strong> <code><?php echo esc_html( $plugin_dir ); ?></code></p>
		
		<?php
		// Vérifier et créer les dossiers
		$dirs_created = 0;
		$dirs_existing = 0;
		?>
		<div class="section">
			<h2>1. Vérification des dossiers</h2>
			<?php
			foreach ( $required_dirs as $dir ) {
				$dir_path = $plugin_dir . '/' . $dir;
				if ( ! is_dir( $dir_path ) ) {
					if ( @mkdir( $dir_path, 0755, true ) ) {
						echo '<p class="success">✓ Dossier créé: ' . esc_html( $dir ) . '</p>';
						$dirs_created++;
					} else {
						echo '<p class="error">✗ Impossible de créer: ' . esc_html( $dir ) . '</p>';
					}
				} else {
					echo '<p class="file-ok">✓ Dossier existe: ' . esc_html( $dir ) . '</p>';
					$dirs_existing++;
				}
			}
			?>
		</div>
		
		<div class="section">
			<h2>2. Vérification des fichiers</h2>
			<?php
			$files_ok = 0;
			$files_missing = 0;
			$missing_list = array();
			
			foreach ( $required_files as $file ) {
				$file_path = $plugin_dir . '/' . $file;
				$exists = file_exists( $file_path );
				
				if ( $exists ) {
					echo '<p class="file-ok">✓ ' . esc_html( $file ) . '</p>';
					$files_ok++;
				} else {
					echo '<p class="file-missing">✗ MANQUANT: ' . esc_html( $file ) . '</p>';
					echo '<div class="code">Chemin attendu: ' . esc_html( $file_path ) . '</div>';
					$files_missing++;
					$missing_list[] = $file;
				}
			}
			?>
		</div>
		
		<div class="section">
			<h2>3. Résumé</h2>
			<p><strong>Dossiers:</strong> <?php echo $dirs_existing; ?> existants, <?php echo $dirs_created; ?> créés</p>
			<p><strong>Fichiers:</strong> <?php echo $files_ok; ?> présents, <?php echo $files_missing; ?> manquants</p>
			
			<?php if ( $files_missing > 0 ) : ?>
				<div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-top: 15px; border-left: 4px solid #ffc107;">
					<h3 class="warning">⚠️ Fichiers manquants détectés</h3>
					<p>Les fichiers suivants doivent être uploadés manuellement :</p>
					<ul>
						<?php foreach ( $missing_list as $file ) : ?>
							<li><code><?php echo esc_html( $file ); ?></code></li>
						<?php endforeach; ?>
					</ul>
					
					<h4>Instructions d'upload manuel :</h4>
					<ol>
						<li>Téléchargez le ZIP <code>viral-quiz.zip</code></li>
						<li>Extrayez-le sur votre ordinateur</li>
						<li>Via FTP/cPanel, uploadez les fichiers manquants dans les dossiers correspondants :
							<div class="code">
								<?php foreach ( $missing_list as $file ) : ?>
									<?php
									$parts = explode( '/', $file );
									$filename = array_pop( $parts );
									$folder = implode( '/', $parts );
									?>
									<?php echo esc_html( $file ); ?> → <?php echo esc_html( $plugin_dir . '/' . $folder . '/' ); ?><br>
								<?php endforeach; ?>
							</div>
						</li>
						<li>Vérifiez les permissions (644 pour fichiers, 755 pour dossiers)</li>
						<li>Rechargez cette page pour vérifier</li>
					</ol>
				</div>
			<?php else : ?>
				<div style="background: #d4edda; padding: 15px; border-radius: 4px; margin-top: 15px; border-left: 4px solid #28a745;">
					<h3 class="success">✅ Tous les fichiers sont présents !</h3>
					<p>Le plugin devrait maintenant fonctionner correctement.</p>
					<p><a href="<?php echo admin_url( 'plugins.php' ); ?>" class="button">Aller aux plugins WordPress</a></p>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="section">
			<h2>4. Structure complète attendue</h2>
			<div class="code">
wp-content/plugins/viral-quiz-6/
├── viral-quiz.php
├── check-files.php
├── install-helper.php (ce fichier)
├── readme.txt
├── admin/
│   ├── class-admin-menu.php
│   ├── class-admin-metabox.php
│   ├── admin-scripts.js
│   └── admin-styles.css
├── includes/
│   ├── class-cpt.php
│   ├── class-db.php          ← CRITIQUE
│   ├── class-rest-api.php
│   └── helpers.php
└── public/
    ├── class-shortcode.php
    ├── class-frontend-render.php
    ├── frontend.js
    └── frontend.css
			</div>
		</div>
		
		<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
			<p><strong>Note de sécurité:</strong> Supprimez ce fichier (install-helper.php) après installation réussie.</p>
		</div>
	</div>
</body>
</html>

