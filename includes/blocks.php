<?php
/**
 * Gutenberg blocks.
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register blocks and editor assets.
 */
function awesome_events_register_blocks() {
	wp_register_script(
		'awesome-events-events-loop-editor',
		AWESOME_EVENTS_URL . 'blocks/events-loop/editor.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-i18n',
		),
		AWESOME_EVENTS_VERSION,
		true
	);

	register_block_type(
		AWESOME_EVENTS_PATH . 'blocks/events-loop/block.json',
		array(
			'editor_script'   => 'awesome-events-events-loop-editor',
			'render_callback' => 'awesome_events_render_events_loop_block',
		)
	);
}
add_action( 'init', 'awesome_events_register_blocks', 20 );

/**
 * Editor data for Events Loop block (taxonomy dropdowns).
 */
function awesome_events_events_loop_editor_data() {
	$cities = awesome_events_get_filter_terms( 'event_city' );
	$types  = awesome_events_get_filter_terms( 'event_type' );

	wp_add_inline_script(
		'awesome-events-events-loop-editor',
		'window.awesomeEventsBlockData = ' . wp_json_encode(
			array(
				'cities' => array_map(
					static function ( $term ) {
						return array(
							'slug' => $term->slug,
							'name' => $term->name,
						);
					},
					$cities
				),
				'types'  => array_map(
					static function ( $term ) {
						return array(
							'slug' => $term->slug,
							'name' => $term->name,
						);
					},
					$types
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'awesome_events_events_loop_editor_data' );

/**
 * Enqueue event styles when the Events Loop block is present.
 */
function awesome_events_enqueue_block_assets() {
	if ( is_admin() || has_block( 'awesome-events/events-loop' ) ) {
		wp_enqueue_style(
			'awesome-events',
			AWESOME_EVENTS_URL . 'assets/css/events.css',
			array(),
			AWESOME_EVENTS_VERSION
		);
	}
}
add_action( 'enqueue_block_assets', 'awesome_events_enqueue_block_assets' );

/**
 * Query upcoming published events.
 *
 * @param array<string, mixed> $args {
 *     @type int    $posts_per_page Max events (default 6).
 *     @type string $event_city     City slug filter.
 *     @type string $event_type     Event type slug filter.
 * }
 * @return WP_Post[]
 */
function awesome_events_get_upcoming_events( $args = array() ) {
	$defaults = array(
		'posts_per_page' => 6,
		'event_city'     => '',
		'event_type'     => '',
	);

	$args  = wp_parse_args( $args, $defaults );
	$count = max( 1, min( 24, (int) $args['posts_per_page'] ) );
	$today = awesome_events_today();

	$query_args = array(
		'post_type'              => 'event',
		'post_status'            => 'publish',
		'posts_per_page'         => $count,
		'orderby'                => 'meta_value',
		'meta_key'               => '_event_start_date',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'meta_query'             => array(
			array(
				'key'     => '_event_start_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	);

	$tax_query = array();
	if ( ! empty( $args['event_city'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'event_city',
			'field'    => 'slug',
			'terms'    => sanitize_title( $args['event_city'] ),
		);
	}
	if ( ! empty( $args['event_type'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'event_type',
			'field'    => 'slug',
			'terms'    => sanitize_title( $args['event_type'] ),
		);
	}
	if ( $tax_query ) {
		$query_args['tax_query'] = $tax_query;
	}

	return get_posts( $query_args );
}

/**
 * Render Events Loop block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function awesome_events_render_events_loop_block( $attributes ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'count'            => 6,
			'heading'          => '',
			'eventCity'        => '',
			'eventType'        => '',
			'showArchiveLink'  => true,
			'archiveLinkLabel' => '',
		)
	);

	ob_start();
	include AWESOME_EVENTS_PATH . 'blocks/events-loop/render.php';
	return (string) ob_get_clean();
}
