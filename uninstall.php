<?php
/**
 * Runs only when the plugin is deleted via the WordPress admin.
 * Removes all FluentSlug meta rows from fluentform_form_meta.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$wpdb->delete(
	$wpdb->prefix . 'fluentform_form_meta',
	[ 'meta_key' => '_fluent_slug' ],
	[ '%s' ]
);
