<?php

namespace FluentSlug;

defined( 'ABSPATH' ) || exit;

class Admin {

	private const RESERVED_SLUGS = [
		'wp-admin', 'wp-content', 'wp-includes', 'wp-login', 'wp-cron',
		'wp-json', 'feed', 'sitemap', 'sitemap_index', 'page', 'category',
		'tag', 'author', 'search', 'attachment', 'embed', 'robots',
		'favicon', 'wp-signup', 'wp-activate',
	];

	public function register(): void {
		add_action( 'admin_menu',               [ $this, 'add_submenu_page' ] );
		add_action( 'admin_notices',            [ $this, 'maybe_show_notice' ] );
		add_action( 'admin_enqueue_scripts',    [ $this, 'enqueue_styles' ] );
		add_action( 'admin_post_fluent_slug_save',   [ $this, 'handle_save' ] );
		add_action( 'admin_post_fluent_slug_delete', [ $this, 'handle_delete' ] );
	}

	public function add_submenu_page(): void {
		add_submenu_page(
			'fluent_forms',
			__( 'Custom Slugs', 'fluent-slug' ),
			__( 'Custom Slugs', 'fluent-slug' ),
			'manage_options',
			'fluent-slug-manager',
			[ $this, 'render_manager_page' ]
		);
	}

	public function enqueue_styles( string $hook ): void {
		if ( 'fluentform_page_fluent-slug-manager' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'fluent-slug-admin',
			FLUENTSLUG_URL . 'admin/css/admin.css',
			[],
			FLUENTSLUG_VERSION
		);
	}

	public function render_manager_page(): void {
		$forms = $this->get_conversational_forms();
		?>
		<div class="wrap fluent-slug-wrap">
			<h1><?php esc_html_e( 'FluentSlug — Custom Slugs', 'fluent-slug' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Assign a custom URL slug to each conversational form. Visitors to that URL will see the form full-page.', 'fluent-slug' ); ?>
			</p>

			<?php if ( empty( $forms ) ) : ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'No forms found. Make sure FluentForms has at least one form.', 'fluent-slug' ); ?></p>
				</div>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped fluent-slug-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Form', 'fluent-slug' ); ?></th>
							<th><?php esc_html_e( 'ID', 'fluent-slug' ); ?></th>
							<th><?php esc_html_e( 'Type', 'fluent-slug' ); ?></th>
							<th><?php esc_html_e( 'Custom Slug', 'fluent-slug' ); ?></th>
							<th><?php esc_html_e( 'Full URL', 'fluent-slug' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'fluent-slug' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $forms as $form ) :
							$current_slug = $this->get_slug_for_form( (int) $form->id );
						?>
						<tr>
							<td><strong><?php echo esc_html( $form->title ); ?></strong></td>
							<td><?php echo esc_html( $form->id ); ?></td>
							<td><code><?php echo esc_html( $form->type ?: '—' ); ?></code></td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fluent-slug-form">
									<?php wp_nonce_field( 'fluent_slug_save_' . $form->id, '_wpnonce' ); ?>
									<input type="hidden" name="action" value="fluent_slug_save">
									<input type="hidden" name="form_id" value="<?php echo esc_attr( $form->id ); ?>">
									<input
										type="text"
										name="fluent_slug"
										value="<?php echo esc_attr( $current_slug ); ?>"
										placeholder="<?php esc_attr_e( 'e.g. contact-us', 'fluent-slug' ); ?>"
										class="regular-text"
										pattern="[a-z0-9][a-z0-9\-]*[a-z0-9]"
										title="<?php esc_attr_e( 'Lowercase letters, numbers, and hyphens only. Must start and end with a letter or number.', 'fluent-slug' ); ?>"
									>
									<button type="submit" class="button button-primary">
										<?php esc_html_e( 'Save', 'fluent-slug' ); ?>
									</button>
								</form>
							</td>
							<td>
								<?php if ( $current_slug ) : ?>
									<a href="<?php echo esc_url( home_url( '/' . $current_slug . '/' ) ); ?>" target="_blank">
										<?php echo esc_html( home_url( '/' . $current_slug . '/' ) ); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e( 'Not set', 'fluent-slug' ); ?></em>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $current_slug ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
										<?php wp_nonce_field( 'fluent_slug_delete_' . $form->id, '_wpnonce' ); ?>
										<input type="hidden" name="action" value="fluent_slug_delete">
										<input type="hidden" name="form_id" value="<?php echo esc_attr( $form->id ); ?>">
										<button
											type="submit"
											class="button button-link-delete"
											onclick="return confirm('<?php esc_attr_e( 'Remove this custom slug?', 'fluent-slug' ); ?>')"
										>
											<?php esc_html_e( 'Remove', 'fluent-slug' ); ?>
										</button>
									</form>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<div class="fluent-slug-help">
				<h3><?php esc_html_e( 'How it works', 'fluent-slug' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Enter a slug using lowercase letters, numbers, and hyphens only.', 'fluent-slug' ); ?></li>
					<li><?php esc_html_e( 'Each slug must be unique — two forms cannot share the same slug.', 'fluent-slug' ); ?></li>
					<li><?php esc_html_e( 'After saving, visit the full URL to see your conversational form.', 'fluent-slug' ); ?></li>
					<li><?php esc_html_e( 'If your slug does not work, go to Settings → Permalinks and click Save Changes to flush rewrite rules.', 'fluent-slug' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}

	public function handle_save(): void {
		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_die( esc_html__( 'Invalid form ID.', 'fluent-slug' ) );
		}

		check_admin_referer( 'fluent_slug_save_' . $form_id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'fluent-slug' ) );
		}

		$raw  = isset( $_POST['fluent_slug'] ) ? wp_unslash( $_POST['fluent_slug'] ) : '';
		$slug = sanitize_title( $raw );

		$redirect = admin_url( 'admin.php?page=fluent-slug-manager' );

		if ( empty( $slug ) ) {
			$this->set_notice( 'error', __( 'Slug cannot be empty.', 'fluent-slug' ) );
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( in_array( $slug, self::RESERVED_SLUGS, true ) ) {
			$this->set_notice( 'error', sprintf(
				/* translators: %s: the reserved slug */
				__( '"%s" is a reserved WordPress URL and cannot be used as a slug.', 'fluent-slug' ),
				$slug
			) );
			wp_safe_redirect( $redirect );
			exit;
		}

		$collision = $this->slug_in_use_by_other_form( $slug, $form_id );

		if ( $collision ) {
			$this->set_notice( 'error', sprintf(
				/* translators: %s: the conflicting slug */
				__( 'The slug "%s" is already assigned to another form.', 'fluent-slug' ),
				$slug
			) );
			wp_safe_redirect( $redirect );
			exit;
		}

		$this->upsert_slug( $form_id, $slug );
		Core::flush();

		$this->set_notice( 'success', sprintf(
			/* translators: %s: the saved slug */
			__( 'Slug "%s" saved successfully.', 'fluent-slug' ),
			$slug
		) );

		wp_safe_redirect( $redirect );
		exit;
	}

	public function handle_delete(): void {
		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_die( esc_html__( 'Invalid form ID.', 'fluent-slug' ) );
		}

		check_admin_referer( 'fluent_slug_delete_' . $form_id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'fluent-slug' ) );
		}

		$this->delete_slug( $form_id );
		Core::flush();

		$redirect = admin_url( 'admin.php?page=fluent-slug-manager' );
		$this->set_notice( 'success', __( 'Custom slug removed.', 'fluent-slug' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	public function maybe_show_notice(): void {
		$key    = 'fluent_slug_notice_' . get_current_user_id();
		$notice = get_transient( $key );

		if ( ! $notice ) {
			return;
		}

		delete_transient( $key );

		$type    = esc_attr( $notice['type'] ?? 'info' );
		$message = $notice['message'] ?? '';
		?>
		<div class="notice notice-<?php echo $type; ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	private function get_conversational_forms(): array {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT id, title, type FROM {$wpdb->prefix}fluentform_forms
			 ORDER BY created_at DESC"
		) ?: [];
	}

	private function get_slug_for_form( int $form_id ): string {
		global $wpdb;

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT value FROM {$wpdb->prefix}fluentform_form_meta
				 WHERE form_id = %d AND meta_key = %s",
				$form_id,
				FLUENTSLUG_META_KEY
			)
		);

		return $value ?? '';
	}

	private function slug_in_use_by_other_form( string $slug, int $exclude_form_id ): bool {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT form_id FROM {$wpdb->prefix}fluentform_form_meta
				 WHERE meta_key = %s AND value = %s AND form_id != %d
				 LIMIT 1",
				FLUENTSLUG_META_KEY,
				$slug,
				$exclude_form_id
			)
		);

		return (bool) $result;
	}

	private function upsert_slug( int $form_id, string $slug ): void {
		global $wpdb;

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}fluentform_form_meta
				 WHERE form_id = %d AND meta_key = %s",
				$form_id,
				FLUENTSLUG_META_KEY
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$wpdb->prefix . 'fluentform_form_meta',
				[ 'value' => $slug ],
				[ 'form_id' => $form_id, 'meta_key' => FLUENTSLUG_META_KEY ],
				[ '%s' ],
				[ '%d', '%s' ]
			);
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'fluentform_form_meta',
				[
					'form_id'  => $form_id,
					'meta_key' => FLUENTSLUG_META_KEY,
					'value'    => $slug,
				],
				[ '%d', '%s', '%s' ]
			);
		}
	}

	private function delete_slug( int $form_id ): void {
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'fluentform_form_meta',
			[ 'form_id' => $form_id, 'meta_key' => FLUENTSLUG_META_KEY ],
			[ '%d', '%s' ]
		);
	}

	private function set_notice( string $type, string $message ): void {
		$key = 'fluent_slug_notice_' . get_current_user_id();
		set_transient( $key, [ 'type' => $type, 'message' => $message ], 60 );
	}
}
