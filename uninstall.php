<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ViralQuiz
 */

// Si ce fichier n'est pas appelé par WordPress, abandonner.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Supprimer les options du plugin (si nécessaire)
// delete_option( 'vq_option_name' );

// Supprimer les métadonnées des posts (si nécessaire)
// global $wpdb;
// $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_vq_%'" );

// Note: La table wp_vq_results est conservée pour préserver les données
// Si vous voulez la supprimer, décommentez les lignes suivantes :
/*
global $wpdb;
$table_name = $wpdb->prefix . 'vq_results';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
*/

