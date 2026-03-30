<?php
/**
 * Full-page template for rendering a FluentForms conversational form.
 *
 * Themes can override this by placing their own copy at:
 *   {theme}/fluent-slug/conversational-form.php
 *
 * @var array $args  Contains 'form_id' (int).
 */

defined( 'ABSPATH' ) || exit;

$form_id = isset( $args['form_id'] ) ? (int) $args['form_id'] : 0;

if ( ! $form_id ) {
	wp_die( esc_html__( 'Form not found.', 'fluent-slug' ) );
}

global $wpdb;

$form = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT title, status FROM {$wpdb->prefix}fluentform_forms WHERE id = %d",
		$form_id
	)
);

if ( ! $form || 'published' !== $form->status ) {
	wp_die( esc_html__( 'This form is not available.', 'fluent-slug' ) );
}

$form_title  = $form->title;
$site_name   = get_bloginfo( 'name' );
$page_title  = $form_title ? "{$form_title} — {$site_name}" : $site_name;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $page_title ); ?></title>
	<?php wp_head(); ?>
	<style>
		html, body {
			margin: 0;
			padding: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
		}
		.fluent-slug-form-wrap {
			width: 100%;
			min-height: 100vh;
		}
	</style>
</head>
<body class="fluent-slug-conversational-page">
	<div class="fluent-slug-form-wrap">
		<?php echo do_shortcode( '[fluentform id="' . $form_id . '" type="conversational"]' ); ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
