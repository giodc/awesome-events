<?php
/**
 * Plugin Name: Awesome Events
 * Description: Events custom post type for Awesome Theme (or any other theme) — REST/MCP-ready meta, Schema.org JSON-LD, and archive filtering.
 * Version:     1.0.0
 * Author:      Giovanni De Carlo
 * Text Domain: awesome-events
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

define( 'AWESOME_EVENTS_VERSION', '1.0.0' );
define( 'AWESOME_EVENTS_PLUGIN_FILE', __FILE__ );
define( 'AWESOME_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AWESOME_EVENTS_URL', plugin_dir_url( __FILE__ ) );

require_once AWESOME_EVENTS_PATH . 'includes/template-functions.php';

/**
 * Bootstrap hooks.
 */
function awesome_events_init() {
	awesome_events_register_taxonomies();
	awesome_events_register_post_type();
	awesome_events_register_meta();
}
add_action( 'init', 'awesome_events_init' );

/**
 * Register the event CPT.
 */
function awesome_events_register_post_type() {
	$labels = array(
		'name'                  => _x( 'Events', 'post type general name', 'awesome-events' ),
		'singular_name'         => _x( 'Event', 'post type singular name', 'awesome-events' ),
		'menu_name'             => _x( 'Events', 'admin menu', 'awesome-events' ),
		'name_admin_bar'        => _x( 'Event', 'add new on admin bar', 'awesome-events' ),
		'add_new'               => _x( 'Add New', 'event', 'awesome-events' ),
		'add_new_item'          => __( 'Add New Event', 'awesome-events' ),
		'new_item'              => __( 'New Event', 'awesome-events' ),
		'edit_item'             => __( 'Edit Event', 'awesome-events' ),
		'view_item'             => __( 'View Event', 'awesome-events' ),
		'all_items'             => __( 'All Events', 'awesome-events' ),
		'search_items'          => __( 'Search Events', 'awesome-events' ),
		'parent_item_colon'     => __( 'Parent Events:', 'awesome-events' ),
		'not_found'             => __( 'No events found.', 'awesome-events' ),
		'not_found_in_trash'    => __( 'No events found in Trash.', 'awesome-events' ),
		'archives'              => __( 'Event Archives', 'awesome-events' ),
		'insert_into_item'      => __( 'Insert into event', 'awesome-events' ),
		'uploaded_to_this_item' => __( 'Uploaded to this event', 'awesome-events' ),
		'filter_items_list'     => __( 'Filter events list', 'awesome-events' ),
		'items_list_navigation' => __( 'Events list navigation', 'awesome-events' ),
		'items_list'            => __( 'Events list', 'awesome-events' ),
	);

	register_post_type(
		'event',
		array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'rest_base'           => 'events',
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'taxonomies'          => array( 'event_type', 'event_city' ),
			'rewrite'             => array( 'slug' => 'events' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'exclude_from_search' => false,
		)
	);
}

/**
 * Label set for a hierarchical event taxonomy.
 *
 * @param string $plural   Plural label.
 * @param string $singular Singular label.
 * @return array<string, string>
 */
function awesome_events_taxonomy_labels( $plural, $singular ) {
	return array(
		'name'              => $plural,
		'singular_name'     => $singular,
		'search_items'      => sprintf(
			/* translators: %s: taxonomy plural label */
			__( 'Search %s', 'awesome-events' ),
			$plural
		),
		'all_items'         => sprintf(
			/* translators: %s: taxonomy plural label */
			__( 'All %s', 'awesome-events' ),
			$plural
		),
		'parent_item'       => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'Parent %s', 'awesome-events' ),
			$singular
		),
		'parent_item_colon' => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'Parent %s:', 'awesome-events' ),
			$singular
		),
		'edit_item'         => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'Edit %s', 'awesome-events' ),
			$singular
		),
		'update_item'       => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'Update %s', 'awesome-events' ),
			$singular
		),
		'add_new_item'      => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'Add New %s', 'awesome-events' ),
			$singular
		),
		'new_item_name'     => sprintf(
			/* translators: %s: taxonomy singular label */
			__( 'New %s Name', 'awesome-events' ),
			$singular
		),
		'menu_name'         => $plural,
	);
}

/**
 * Shared args for event taxonomies (admin + block editor).
 *
 * @return array<string, mixed>
 */
function awesome_events_taxonomy_args() {
	return array(
		'public'            => true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'show_admin_column' => false,
		'show_tagcloud'     => false,
		'hierarchical'      => true,
		'meta_box_cb'       => 'post_categories_meta_box',
		'capabilities'      => array(
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_posts',
		),
	);
}

/**
 * Register event taxonomies (must run before the event post type).
 */
function awesome_events_register_taxonomies() {
	$shared = awesome_events_taxonomy_args();

	register_taxonomy(
		'event_type',
		array( 'event' ),
		array_merge(
			$shared,
			array(
				'labels'    => awesome_events_taxonomy_labels(
					__( 'Event Types', 'awesome-events' ),
					__( 'Event Type', 'awesome-events' )
				),
				'rewrite'   => array( 'slug' => 'event-type' ),
				'rest_base' => 'event_type',
			)
		)
	);

	register_taxonomy(
		'event_city',
		array( 'event' ),
		array_merge(
			$shared,
			array(
				'labels'    => awesome_events_taxonomy_labels(
					__( 'Cities', 'awesome-events' ),
					__( 'City', 'awesome-events' )
				),
				'rewrite'   => array( 'slug' => 'event-city' ),
				'rest_base' => 'event_city',
			)
		)
	);
}

/**
 * Ensure taxonomy panels appear on the event edit screen (classic + block editor fallback).
 */
function awesome_events_taxonomy_meta_boxes() {
	$boxes = array(
		'event_type' => __( 'Event Type', 'awesome-events' ),
		'event_city' => __( 'City', 'awesome-events' ),
	);

	foreach ( $boxes as $taxonomy => $title ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		add_meta_box(
			$taxonomy . 'div',
			$title,
			'post_categories_meta_box',
			'event',
			'side',
			'default',
			array( 'taxonomy' => $taxonomy )
		);
	}
}
add_action( 'add_meta_boxes_event', 'awesome_events_taxonomy_meta_boxes' );

/**
 * Meta auth callback for REST/MCP edits.
 *
 * @return bool
 */
function awesome_events_meta_auth_callback() {
	return current_user_can( 'edit_posts' );
}

/**
 * Sanitize Y-m-d date meta.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function awesome_events_sanitize_date( $value ) {
	$value = sanitize_text_field( (string) $value );
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
		return $value;
	}
	return '';
}

/**
 * Whether a string is a valid http(s) URL for Official Website.
 *
 * @param string $url URL to check.
 * @return bool
 */
function awesome_events_is_valid_url( $url ) {
	$url = esc_url_raw( $url );
	if ( '' === $url ) {
		return false;
	}

	$parts = wp_parse_url( $url );
	if ( empty( $parts['scheme'] ) || ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
		return false;
	}
	if ( empty( $parts['host'] ) ) {
		return false;
	}

	return (bool) wp_http_validate_url( $url );
}

/**
 * Sanitize Official Website meta; invalid non-empty values become empty.
 *
 * @param mixed $value Raw value.
 * @return string Valid URL or empty string.
 */
function awesome_events_sanitize_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	if ( ! preg_match( '#^https?://#i', $value ) ) {
		$value = 'https://' . $value;
	}

	$url = esc_url_raw( $value );
	if ( ! awesome_events_is_valid_url( $url ) ) {
		return '';
	}

	return $url;
}

/**
 * Register all event meta for REST (meta object) with Schema.org hints.
 */
function awesome_events_register_meta() {
	$auth = 'awesome_events_meta_auth_callback';

	register_post_meta(
		'event',
		'_event_start_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'format'      => 'date',
					'description' => 'Event startDate (Schema.org).',
				),
			),
			'sanitize_callback' => 'awesome_events_sanitize_date',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_end_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'format'      => 'date',
					'description' => 'Event endDate (Schema.org). Empty uses start date.',
				),
			),
			'sanitize_callback' => 'awesome_events_sanitize_date',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_location_name',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'description' => 'Event location.name (Schema.org).',
				),
			),
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_location_address',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'description' => 'Event location.address (Schema.org).',
				),
			),
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_time',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'description' => 'Human-readable event time, e.g. "From 18:00 to midnight".',
				),
			),
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_tickets',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'description' => 'Event offers.price / tickets info (Schema.org).',
				),
			),
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);

	register_post_meta(
		'event',
		'_event_url',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => 'string',
					'format'      => 'uri',
					'description' => 'Event url — official website (Schema.org).',
				),
			),
			'sanitize_callback' => 'awesome_events_sanitize_url',
			'auth_callback'     => $auth,
		)
	);
}

/**
 * Default taxonomy terms (name => slug).
 *
 * @return array<string, array<string, string>>
 */
function awesome_events_default_terms() {
	return array(
		'event_type' => array(
			'Festival'       => 'festival',
			'Concert'        => 'concert',
			'Food & Wine'    => 'food-wine',
			'Exhibition'     => 'exhibition',
			'Sports'         => 'sports',
			'Religious'      => 'religious',
			'Market'         => 'market',
			'Other'          => 'other',
		),
		'event_city' => array(
			'Genoa'                      => 'genoa',
			'Cinque Terre'               => 'cinque-terre',
			'Portofino'                  => 'portofino',
			'Sanremo'                    => 'sanremo',
			'La Spezia'                  => 'la-spezia',
			'Savona'                     => 'savona',
			'Imperia'                    => 'imperia',
			'Rapallo'                    => 'rapallo',
			'Santa Margherita Ligure'    => 'santa-margherita-ligure',
			'Other'                      => 'other',
		),
	);
}

/**
 * Insert default taxonomy terms (idempotent).
 */
function awesome_events_insert_default_terms() {
	foreach ( awesome_events_default_terms() as $taxonomy => $terms ) {
		foreach ( $terms as $name => $slug ) {
			if ( term_exists( $slug, $taxonomy ) ) {
				continue;
			}
			wp_insert_term(
				$name,
				$taxonomy,
				array( 'slug' => $slug )
			);
		}
	}
}

/**
 * Plugin activation: terms + rewrite flush.
 */
function awesome_events_activate() {
	awesome_events_init();
	awesome_events_insert_default_terms();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'awesome_events_activate' );

/**
 * Plugin deactivation: flush rewrites.
 */
function awesome_events_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'awesome_events_deactivate' );

/**
 * Today's date in site timezone (Y-m-d).
 *
 * @return string
 */
function awesome_events_today() {
	return wp_date( 'Y-m-d' );
}

/**
 * Get event meta with defaults.
 *
 * @param int $post_id Post ID.
 * @return array<string, string>
 */
function awesome_events_get_meta( $post_id ) {
	$start = (string) get_post_meta( $post_id, '_event_start_date', true );
	$end   = (string) get_post_meta( $post_id, '_event_end_date', true );
	if ( '' === $end && '' !== $start ) {
		$end = $start;
	}

	return array(
		'start_date' => $start,
		'end_date'   => $end,
		'location'   => (string) get_post_meta( $post_id, '_event_location_name', true ),
		'address'    => (string) get_post_meta( $post_id, '_event_location_address', true ),
		'time'       => (string) get_post_meta( $post_id, '_event_time', true ),
		'tickets'    => (string) get_post_meta( $post_id, '_event_tickets', true ),
		'url'        => (string) get_post_meta( $post_id, '_event_url', true ),
	);
}

/**
 * Whether an event start date is in the past.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function awesome_events_is_past( $post_id ) {
	$start = (string) get_post_meta( $post_id, '_event_start_date', true );
	if ( '' === $start ) {
		return false;
	}
	return $start < awesome_events_today();
}

/**
 * Archive query: upcoming (default) or past (?past=1), ordered by start date.
 *
 * @param WP_Query $query Main query.
 */
function awesome_events_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'event' ) ) {
		return;
	}

	$today = awesome_events_today();
	$past  = isset( $_GET['past'] ) && '1' === (string) wp_unslash( $_GET['past'] );

	$query->set( 'meta_key', '_event_start_date' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', $past ? 'DESC' : 'ASC' );
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_event_start_date',
				'value'   => $today,
				'compare' => $past ? '<' : '>=',
				'type'    => 'DATE',
			),
		)
	);

	$filters  = awesome_events_get_archive_filters();
	$tax_query = array();

	if ( $filters['event_city'] ) {
		$tax_query[] = array(
			'taxonomy' => 'event_city',
			'field'    => 'slug',
			'terms'    => $filters['event_city'],
		);
	}

	if ( $filters['event_type'] ) {
		$tax_query[] = array(
			'taxonomy' => 'event_type',
			'field'    => 'slug',
			'terms'    => $filters['event_type'],
		);
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'awesome_events_archive_query' );

/**
 * Output Event JSON-LD on single event pages.
 */
function awesome_events_json_ld() {
	if ( ! is_singular( 'event' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$meta = awesome_events_get_meta( $post_id );
	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Event',
		'name'        => get_the_title( $post_id ),
		'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
		'url'         => $meta['url'] ? $meta['url'] : get_permalink( $post_id ),
	);

	if ( $meta['start_date'] ) {
		$data['startDate'] = $meta['start_date'];
	}
	if ( $meta['end_date'] ) {
		$data['endDate'] = $meta['end_date'];
	}

	$location = array(
		'@type' => 'Place',
		'name'  => $meta['location'],
	);
	if ( $meta['address'] ) {
		$location['address'] = $meta['address'];
	}
	if ( ! empty( $location['name'] ) || ! empty( $location['address'] ) ) {
		$data['location'] = $location;
	}

	if ( $meta['tickets'] ) {
		$data['offers'] = array(
			'@type' => 'Offer',
			'price' => $meta['tickets'],
		);
	}

	$image = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( $image ) {
		$data['image'] = $image;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_head', 'awesome_events_json_ld' );

/* --- Admin: meta box --- */

/**
 * Register event details meta box.
 */
function awesome_events_admin_meta_box() {
	add_meta_box(
		'awesome-events-details',
		__( 'Event Details', 'awesome-events' ),
		'awesome_events_render_meta_box',
		'event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'awesome_events_admin_meta_box' );

/**
 * Render event meta box fields.
 *
 * @param WP_Post $post Current post.
 */
function awesome_events_render_meta_box( $post ) {
	wp_nonce_field( 'awesome_events_save_meta', 'awesome_events_meta_nonce' );
	$meta = awesome_events_get_meta( $post->ID );
	$fields = array(
		'_event_start_date'        => array( 'label' => __( 'Start Date', 'awesome-events' ), 'type' => 'date', 'required' => true ),
		'_event_end_date'          => array( 'label' => __( 'End Date', 'awesome-events' ), 'type' => 'date', 'required' => false ),
		'_event_location_name'     => array( 'label' => __( 'Venue / Location', 'awesome-events' ), 'type' => 'text', 'required' => true ),
		'_event_location_address'  => array( 'label' => __( 'Address', 'awesome-events' ), 'type' => 'text', 'required' => false ),
		'_event_time'              => array( 'label' => __( 'Time', 'awesome-events' ), 'type' => 'text', 'required' => false ),
		'_event_tickets'           => array( 'label' => __( 'Tickets / Entry', 'awesome-events' ), 'type' => 'text', 'required' => false ),
		'_event_url'               => array( 'label' => __( 'Official Website', 'awesome-events' ), 'type' => 'url', 'required' => false ),
	);

	echo '<table class="form-table awesome-events-meta"><tbody>';
	foreach ( $fields as $key => $field ) {
		$value = (string) get_post_meta( $post->ID, $key, true );
		if ( '_event_end_date' === $key && '' === $value ) {
			$value = '';
		}

		$extra_attrs = $field['required'] ? ' required' : '';
		$field_hint  = '';

		if ( '_event_url' === $key ) {
			$extra_attrs .= ' placeholder="https://example.com" inputmode="url"';
			$field_hint   = '<p class="description">' . esc_html__( 'Must be a valid http or https URL (e.g. https://example.com).', 'awesome-events' ) . '</p>';
		}

		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s%3$s</label></th><td><input class="widefat" type="%4$s" id="%1$s" name="%1$s" value="%5$s"%6$s>%7$s</td></tr>',
			esc_attr( $key ),
			esc_html( $field['label'] ),
			$field['required'] ? ' <span class="required">*</span>' : '',
			esc_attr( $field['type'] ),
			esc_attr( $value ),
			$extra_attrs,
			$field_hint
		);
	}
	echo '</tbody></table>';
}

/**
 * Save event meta from admin.
 *
 * @param int $post_id Post ID.
 */
function awesome_events_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['awesome_events_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['awesome_events_meta_nonce'] ) ), 'awesome_events_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || 'event' !== get_post_type( $post_id ) ) {
		return;
	}

	$map = array(
		'_event_start_date'       => 'awesome_events_sanitize_date',
		'_event_end_date'         => 'awesome_events_sanitize_date',
		'_event_location_name'    => 'sanitize_text_field',
		'_event_location_address' => 'sanitize_text_field',
		'_event_time'             => 'sanitize_text_field',
		'_event_tickets'          => 'sanitize_text_field',
		'_event_url'              => 'awesome_events_sanitize_url',
	);

	foreach ( $map as $key => $callback ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = trim( (string) wp_unslash( $_POST[ $key ] ) );

		if ( '_event_url' === $key && '' !== $raw && '' === awesome_events_sanitize_url( $raw ) ) {
			set_transient( 'awesome_events_invalid_url_' . get_current_user_id(), $post_id, 60 );
			continue;
		}

		$value = call_user_func( $callback, $raw );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_event', 'awesome_events_save_meta_box' );

/**
 * Admin notice when Official Website is not a valid URL.
 */
function awesome_events_invalid_url_admin_notice() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$post_id = get_transient( 'awesome_events_invalid_url_' . $user_id );
	if ( ! $post_id ) {
		return;
	}

	delete_transient( 'awesome_events_invalid_url_' . $user_id );

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'event' !== $screen->post_type || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
		return;
	}

	if ( (int) $post_id !== (int) ( $_GET['post'] ?? 0 ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
		esc_html__( 'Official Website must be a valid URL (http or https). The previous value was kept.', 'awesome-events' )
	);
}
add_action( 'admin_notices', 'awesome_events_invalid_url_admin_notice' );

/**
 * Reject invalid Official Website on REST create/update.
 *
 * @param WP_Post         $prepared_post Prepared post.
 * @param WP_REST_Request $request       Request.
 * @return WP_Post|WP_Error
 */
function awesome_events_rest_validate_url( $prepared_post, $request ) {
	$meta = $request->get_param( 'meta' );
	if ( ! is_array( $meta ) || ! array_key_exists( '_event_url', $meta ) ) {
		return $prepared_post;
	}

	$raw = trim( (string) $meta['_event_url'] );
	if ( '' === $raw ) {
		return $prepared_post;
	}

	if ( '' === awesome_events_sanitize_url( $raw ) ) {
		return new WP_Error(
			'rest_invalid_event_url',
			__( 'Official Website must be a valid http or https URL.', 'awesome-events' ),
			array( 'status' => 400 )
		);
	}

	return $prepared_post;
}
add_filter( 'rest_pre_insert_event', 'awesome_events_rest_validate_url', 10, 2 );
add_filter( 'rest_pre_update_event', 'awesome_events_rest_validate_url', 10, 2 );

/* --- Admin: list columns & filters --- */

/**
 * Admin list columns.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function awesome_events_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['event_start_date'] = __( 'Start Date', 'awesome-events' );
			$new['event_end_date']   = __( 'End Date', 'awesome-events' );
			$new['event_city']       = __( 'City', 'awesome-events' );
			$new['event_type']       = __( 'Event Type', 'awesome-events' );
		}
	}
	return $new;
}
add_filter( 'manage_event_posts_columns', 'awesome_events_columns' );

/**
 * Render custom admin column cells.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function awesome_events_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'event_start_date':
			echo esc_html( (string) get_post_meta( $post_id, '_event_start_date', true ) );
			break;
		case 'event_end_date':
			$end = (string) get_post_meta( $post_id, '_event_end_date', true );
			echo esc_html( $end ? $end : '—' );
			break;
		case 'event_city':
			echo esc_html( awesome_events_term_list( $post_id, 'event_city' ) );
			break;
		case 'event_type':
			echo esc_html( awesome_events_term_list( $post_id, 'event_type' ) );
			break;
	}
}
add_action( 'manage_event_posts_custom_column', 'awesome_events_column_content', 10, 2 );

/**
 * Comma-separated term names for a post.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return string
 */
function awesome_events_term_list( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	return implode( ', ', wp_list_pluck( $terms, 'name' ) );
}

/**
 * Sortable admin columns.
 *
 * @param array<string, string> $columns Sortable columns.
 * @return array<string, string>
 */
function awesome_events_sortable_columns( $columns ) {
	$columns['event_start_date'] = 'event_start_date';
	$columns['event_end_date']   = 'event_end_date';
	return $columns;
}
add_filter( 'manage_edit-event_sortable_columns', 'awesome_events_sortable_columns' );

/**
 * Order admin list by meta when sorting by date columns.
 *
 * @param WP_Query $query Admin query.
 */
function awesome_events_admin_sort( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$orderby = $query->get( 'orderby' );
	if ( 'event_start_date' === $orderby ) {
		$query->set( 'meta_key', '_event_start_date' );
		$query->set( 'orderby', 'meta_value' );
	}
	if ( 'event_end_date' === $orderby ) {
		$query->set( 'meta_key', '_event_end_date' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'awesome_events_admin_sort' );

/**
 * Taxonomy dropdown filters on Events list screen.
 *
 * @param string $post_type Current post type.
 */
function awesome_events_admin_filters( $post_type ) {
	if ( 'event' !== $post_type ) {
		return;
	}

	$taxonomies = array(
		'event_city' => __( 'All Cities', 'awesome-events' ),
		'event_type' => __( 'All Event Types', 'awesome-events' ),
	);

	foreach ( $taxonomies as $taxonomy => $label ) {
		$selected = isset( $_GET[ $taxonomy ] ) ? sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
		$terms    = awesome_events_get_filter_terms( $taxonomy, $selected );

		printf( '<label class="screen-reader-text" for="filter-%1$s">%2$s</label>', esc_attr( $taxonomy ), esc_html( $label ) );
		printf( '<select name="%1$s" id="filter-%1$s">', esc_attr( $taxonomy ) );
		printf( '<option value="">%s</option>', esc_html( $label ) );
		foreach ( $terms as $term ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $term->slug ),
				selected( $selected, $term->slug, false ),
				esc_html( $term->name )
			);
		}
		echo '</select>';
	}
}
add_action( 'restrict_manage_posts', 'awesome_events_admin_filters' );

/**
 * Apply admin taxonomy filters.
 *
 * @param WP_Query $query Admin query.
 */
function awesome_events_admin_filter_query( $query ) {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
		return;
	}
	if ( 'event' !== $query->get( 'post_type' ) ) {
		return;
	}

	$tax_query = array();
	foreach ( array( 'event_city', 'event_type' ) as $taxonomy ) {
		if ( empty( $_GET[ $taxonomy ] ) ) {
			continue;
		}
		$slug = sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) );
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $slug,
		);
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_filter( 'parse_query', 'awesome_events_admin_filter_query' );
