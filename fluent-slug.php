<?php
/**
 * Plugin Name:       FluentSlug
 * Plugin URI:        https://mthokozisi.link
 * Description:       Custom URL slugs for FluentForms conversational forms.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Mthokozisi Dhlamini
 * Author URI:        https://mthokozisi.link
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fluent-slug
 */

defined( 'ABSPATH' ) || exit;

define( 'FLUENTSLUG_VERSION',  '1.0.0' );
define( 'FLUENTSLUG_PATH',     plugin_dir_path( __FILE__ ) );
define( 'FLUENTSLUG_URL',      plugin_dir_url( __FILE__ ) );
define( 'FLUENTSLUG_META_KEY', '_fluent_slug' );
define( 'FLUENTSLUG_DEP_FILE', 'fluentform/fluentform.php' );

register_activation_hook( __FILE__, 'fluentslug_activate' );
register_deactivation_hook( __FILE__, 'fluentslug_deactivate' );

add_action( 'plugins_loaded', 'fluentslug_bootstrap' );

function fluentslug_activate(): void {
	fluentslug_bootstrap();
	flush_rewrite_rules();
}

function fluentslug_deactivate(): void {
	flush_rewrite_rules();
}

function fluentslug_bootstrap(): void {
	if ( ! fluentslug_dependency_met() ) {
		add_action( 'admin_notices', 'fluentslug_missing_dep_notice' );
		return;
	}

	require_once FLUENTSLUG_PATH . 'includes/class-fluent-slug-core.php';
	require_once FLUENTSLUG_PATH . 'includes/class-fluent-slug-admin.php';

	( new FluentSlug\Core() )->register();

	if ( is_admin() ) {
		( new FluentSlug\Admin() )->register();
	}
}

function fluentslug_dependency_met(): bool {
	return defined( 'FLUENTFORM_VERSION' )
		|| function_exists( 'wpFluentForm' )
		|| is_plugin_active( FLUENTSLUG_DEP_FILE );
}

function fluentslug_missing_dep_notice(): void {
	?>
	<div class="notice notice-error">
		<p>
			<strong>FluentSlug</strong> requires
			<a href="https://wordpress.org/plugins/fluentform/" target="_blank">FluentForms</a>
			to be installed and active.
		</p>
	</div>
	<?php
}
