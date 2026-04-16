<?php

namespace FluentSlug;

defined( 'ABSPATH' ) || exit;

class Core {

	private int $pending_form_id = 0;

	public function register(): void {
		add_filter( 'query_vars',        [ $this, 'add_query_vars' ] );
		add_action( 'init',              [ $this, 'add_rewrite_rules' ] );

		// Priority 1: resolve slug → form ID and inject the query param FluentForms
		// uses for its own landing page renderer, so it takes over at priority 10.
		add_action( 'template_redirect', [ $this, 'inject_fluent_form_var' ], 1 );

		// Priority 20: fallback if FluentForms didn't handle it (e.g. version differences).
		add_action( 'template_redirect', [ $this, 'maybe_render_form' ], 20 );
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

	/**
	 * Runs at priority 1. Resolves the custom slug and injects the
	 * `fluent-form` GET parameter that FluentForms checks in its own
	 * template_redirect hook (priority 10), causing it to render its
	 * native full-page landing page at our custom URL.
	 */
	public function inject_fluent_form_var(): void {
		$slug = get_query_var( 'fluent_slug' );

		if ( empty( $slug ) ) {
			return;
		}

		$form_id = $this->get_form_id_by_slug( $slug );

		if ( ! $form_id ) {
			return;
		}

		$this->pending_form_id = $form_id;

		// FluentForms reads $_GET['fluent-form'] (the form ID) to render
		// its conversational landing page.
		$_GET['fluent-form']     = $form_id;
		$_REQUEST['fluent-form'] = $form_id;
	}

	/**
	 * Runs at priority 20. By this point FluentForms has had a chance to
	 * render its landing page and call exit(). If we're still executing,
	 * fall back to our own standalone template.
	 */
	public function maybe_render_form(): void {
		if ( ! $this->pending_form_id ) {
			return;
		}

		nocache_headers();
		add_filter( 'show_admin_bar', '__return_false' );

		$template = locate_template( 'fluent-slug/conversational-form.php' );

		if ( ! $template ) {
			$template = FLUENTSLUG_PATH . 'templates/conversational-form.php';
		}

		load_template( $template, true, [ 'form_id' => $this->pending_form_id ] );
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
