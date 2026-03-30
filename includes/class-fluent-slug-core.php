<?php

namespace FluentSlug;

defined( 'ABSPATH' ) || exit;

class Core {

	public function register(): void {
		add_filter( 'query_vars',         [ $this, 'add_query_vars' ] );
		add_action( 'init',               [ $this, 'add_rewrite_rules' ] );
		add_action( 'template_redirect',  [ $this, 'maybe_render_form' ] );
	}

	public function add_query_vars( array $vars ): array {
		$vars[] = 'fluent_slug';
		return $vars;
	}

	public function add_rewrite_rules(): void {
		add_rewrite_rule(
			'^([a-z0-9][a-z0-9\-]*[a-z0-9])/?$',
			'index.php?fluent_slug=$matches[1]',
			'top'
		);
	}

	public function maybe_render_form(): void {
		$slug = get_query_var( 'fluent_slug' );

		if ( empty( $slug ) ) {
			return;
		}

		$form_id = $this->get_form_id_by_slug( $slug );

		if ( ! $form_id ) {
			return;
		}

		nocache_headers();
		add_filter( 'show_admin_bar', '__return_false' );

		$template = locate_template( 'fluent-slug/conversational-form.php' );

		if ( ! $template ) {
			$template = FLUENTSLUG_PATH . 'templates/conversational-form.php';
		}

		load_template( $template, true, [ 'form_id' => $form_id ] );
		exit;
	}

	public function get_form_id_by_slug( string $slug ): int|false {
		global $wpdb;

		$table = $wpdb->prefix . 'fluentform_form_meta';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT form_id FROM {$table} WHERE meta_key = %s AND value = %s LIMIT 1",
				FLUENTSLUG_META_KEY,
				$slug
			)
		);

		return $result ? (int) $result : false;
	}

	public static function flush(): void {
		flush_rewrite_rules( false );
	}
}
