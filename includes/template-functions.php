<?php
/**
 * Template loader, helpers, and frontend assets.
 *
 * Override any template by copying from:
 *   wp-content/plugins/awesome-events/templates/
 * to:
 *   wp-content/themes/your-theme/awesome-events/
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locate a template: child theme → parent theme → plugin.
 *
 * @param string $template_name Template file name, e.g. archive-event.php.
 * @return string Absolute path or empty string.
 */
function awesome_events_locate_template( $template_name ) {
	$template_name = ltrim( $template_name, '/' );

	$candidates = array(
		'awesome-events/' . $template_name,
		$template_name,
	);

	$theme_path = locate_template( $candidates );
	if ( $theme_path ) {
		return (string) apply_filters( 'awesome_events_locate_template', $theme_path, $template_name, 'theme' );
	}

	$plugin_path = AWESOME_EVENTS_PATH . 'templates/' . $template_name;
	if ( is_readable( $plugin_path ) ) {
		return (string) apply_filters( 'awesome_events_locate_template', $plugin_path, $template_name, 'plugin' );
	}

	return (string) apply_filters( 'awesome_events_locate_template', '', $template_name, '' );
}

/**
 * Include a template with optional arguments.
 *
 * @param string               $template_name Template file name.
 * @param array<string, mixed> $args          Variables exposed in template scope.
 * @param bool                 $return        Return buffered output instead of echoing.
 * @return string
 */
function awesome_events_get_template( $template_name, $args = array(), $return = false ) {
	$path = awesome_events_locate_template( $template_name );
	if ( '' === $path ) {
		return '';
	}

	if ( ! empty( $args ) && is_array( $args ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- template API.
		extract( $args, EXTR_SKIP );
	}

	if ( $return ) {
		ob_start();
		include $path;
		return (string) ob_get_clean();
	}

	include $path;
	return '';
}

/**
 * Use plugin templates for event archive and single views.
 *
 * @param string $template Current template path.
 * @return string
 */
function awesome_events_template_include( $template ) {
	if ( is_post_type_archive( 'event' ) ) {
		$located = awesome_events_locate_template( 'archive-event.php' );
		if ( $located ) {
			return $located;
		}
	}

	if ( is_singular( 'event' ) ) {
		$located = awesome_events_locate_template( 'single-event.php' );
		if ( $located ) {
			return $located;
		}
	}

	return $template;
}
add_filter( 'template_include', 'awesome_events_template_include', 99 );

/**
 * Enqueue frontend styles on event pages.
 */
function awesome_events_enqueue_styles() {
	if ( ! is_post_type_archive( 'event' ) && ! is_singular( 'event' ) ) {
		return;
	}

	wp_enqueue_style(
		'awesome-events',
		AWESOME_EVENTS_URL . 'assets/css/events.css',
		array(),
		AWESOME_EVENTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'awesome_events_enqueue_styles' );

/**
 * Taxonomy terms assigned to at least one published event (for filter dropdowns).
 *
 * @param string $taxonomy      Taxonomy slug (event_city or event_type).
 * @param string $selected_slug Optional slug to keep visible when active in the URL.
 * @return WP_Term[]
 */
function awesome_events_get_filter_terms( $taxonomy, $selected_slug = '' ) {
	$by_id = array();

	$event_ids = get_posts(
		array(
			'post_type'              => 'event',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $event_ids ) {
		$terms = wp_get_object_terms( $event_ids, $taxonomy );
		if ( ! is_wp_error( $terms ) && $terms ) {
			foreach ( $terms as $term ) {
				$by_id[ $term->term_id ] = $term;
			}
		}
	}

	$terms = array_values( $by_id );
	usort(
		$terms,
		static function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);

	if ( '' !== $selected_slug ) {
		$found = false;
		foreach ( $terms as $term ) {
			if ( $term->slug === $selected_slug ) {
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			$selected = get_term_by( 'slug', $selected_slug, $taxonomy );
			if ( $selected && ! is_wp_error( $selected ) ) {
				$terms[] = $selected;
				usort(
					$terms,
					static function ( $a, $b ) {
						return strcasecmp( $a->name, $b->name );
					}
				);
			}
		}
	}

	return $terms;
}

/**
 * Current archive filter values from the query string.
 *
 * @return array{past: bool, event_city: string, event_type: string}
 */
function awesome_events_get_archive_filters() {
	return array(
		'past'       => isset( $_GET['past'] ) && '1' === (string) wp_unslash( $_GET['past'] ),
		'event_city' => isset( $_GET['event_city'] ) ? sanitize_title( wp_unslash( $_GET['event_city'] ) ) : '',
		'event_type' => isset( $_GET['event_type'] ) ? sanitize_title( wp_unslash( $_GET['event_type'] ) ) : '',
	);
}

/**
 * Build event archive URL with filter query args.
 *
 * @param array<string, string|null> $overrides Query arg overrides; null removes key.
 * @return string
 */
function awesome_events_archive_url( $overrides = array() ) {
	$base = get_post_type_archive_link( 'event' );
	if ( ! $base ) {
		$base = home_url( '/events/' );
	}

	$filters = awesome_events_get_archive_filters();
	$args    = array();

	if ( $filters['past'] ) {
		$args['past'] = '1';
	}
	if ( $filters['event_city'] ) {
		$args['event_city'] = $filters['event_city'];
	}
	if ( $filters['event_type'] ) {
		$args['event_type'] = $filters['event_type'];
	}

	$cal = awesome_events_get_calendar_month();
	if ( ! empty( $cal['key'] ) ) {
		$args['cal_month'] = $cal['key'];
	}

	foreach ( $overrides as $key => $value ) {
		if ( null === $value ) {
			unset( $args[ $key ] );
		} else {
			$args[ $key ] = $value;
		}
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Active calendar month from ?cal_month=YYYY-MM (defaults to current month).
 *
 * @return array{year: int, month: int, key: string}
 */
function awesome_events_get_calendar_month() {
	$raw = '';
	if ( isset( $_GET['cal_month'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_GET['cal_month'] ) );
	}

	if ( preg_match( '/^(\d{4})-(\d{2})$/', $raw, $m ) ) {
		$year  = (int) $m[1];
		$month = (int) $m[2];
		if ( $month >= 1 && $month <= 12 ) {
			return array(
				'year'  => $year,
				'month' => $month,
				'key'   => sprintf( '%04d-%02d', $year, $month ),
			);
		}
	}

	return array(
		'year'  => (int) wp_date( 'Y' ),
		'month' => (int) wp_date( 'n' ),
		'key'   => wp_date( 'Y-m' ),
	);
}

/**
 * Events in a month grouped by day (Y-m-d), respecting city/type filters.
 *
 * @param int $year  Four-digit year.
 * @param int $month Month 1–12.
 * @return array<string, array<int, array{id: int, title: string, url: string}>>
 */
function awesome_events_get_calendar_events_by_day( $year, $month ) {
	$month_start = sprintf( '%04d-%02d-01', $year, $month );
	$month_end   = wp_date( 'Y-m-t', strtotime( $month_start . ' 12:00:00' ) );

	$filters   = awesome_events_get_archive_filters();
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

	$query_args = array(
		'post_type'              => 'event',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'meta_value',
		'meta_key'               => '_event_start_date',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'meta_query'             => array(
			array(
				'key'     => '_event_start_date',
				'value'   => $month_end,
				'compare' => '<=',
				'type'    => 'DATE',
			),
		),
	);

	if ( $tax_query ) {
		$query_args['tax_query'] = $tax_query;
	}

	$posts = get_posts( $query_args );
	$days  = array();
	$tz    = wp_timezone();

	foreach ( $posts as $post ) {
		$meta  = awesome_events_get_meta( $post->ID );
		$start = $meta['start_date'];
		if ( '' === $start ) {
			continue;
		}

		$end = $meta['end_date'] ? $meta['end_date'] : $start;
		$from = max( $start, $month_start );
		$to   = min( $end, $month_end );

		if ( $from > $to ) {
			continue;
		}

		$cursor = new DateTimeImmutable( $from, $tz );
		$last   = new DateTimeImmutable( $to, $tz );

		while ( $cursor <= $last ) {
			$key = $cursor->format( 'Y-m-d' );
			if ( ! isset( $days[ $key ] ) ) {
				$days[ $key ] = array();
			}

			$exists = false;
			foreach ( $days[ $key ] as $item ) {
				if ( $item['id'] === $post->ID ) {
					$exists = true;
					break;
				}
			}

			if ( ! $exists ) {
				$days[ $key ][] = array(
					'id'    => $post->ID,
					'title' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
				);
			}

			$cursor = $cursor->modify( '+1 day' );
		}
	}

	return $days;
}

/**
 * Calendar grid cells (null = padding, string = Y-m-d).
 *
 * @param int $year  Year.
 * @param int $month Month.
 * @return array<int, string|null>
 */
function awesome_events_get_calendar_grid( $year, $month ) {
	$month_start   = sprintf( '%04d-%02d-01', $year, $month );
	$first         = new DateTimeImmutable( $month_start, wp_timezone() );
	$days_in_month = (int) $first->format( 't' );
	$start_wday    = (int) $first->format( 'w' );
	$week_start    = (int) get_option( 'start_of_week', 0 );
	$offset        = ( $start_wday - $week_start + 7 ) % 7;

	$cells = array_fill( 0, $offset, null );

	for ( $day = 1; $day <= $days_in_month; $day++ ) {
		$cells[] = sprintf( '%04d-%02d-%02d', $year, $month, $day );
	}

	return $cells;
}

/**
 * Localized weekday abbreviations for calendar header.
 *
 * @return string[]
 */
function awesome_events_get_calendar_weekdays() {
	$week_start = (int) get_option( 'start_of_week', 0 );
	$labels     = array();

	for ( $i = 0; $i < 7; $i++ ) {
		$wday           = ( $week_start + $i ) % 7;
		$timestamp      = strtotime( "Sunday +{$wday} days" );
		$labels[ $i ] = wp_date( 'D', $timestamp );
	}

	return $labels;
}

/**
 * Format a date for display (site locale).
 *
 * @param string $date Y-m-d date.
 * @return string
 */
function awesome_events_format_date( $date ) {
	if ( '' === $date ) {
		return '';
	}
	$timestamp = strtotime( $date . ' 12:00:00' );
	if ( false === $timestamp ) {
		return $date;
	}
	return wp_date( get_option( 'date_format' ), $timestamp );
}

/**
 * Format start/end date range for display.
 *
 * @param string $start Start date Y-m-d.
 * @param string $end   End date Y-m-d.
 * @return string
 */
function awesome_events_format_date_range( $start, $end ) {
	$start_fmt = awesome_events_format_date( $start );
	if ( '' === $start_fmt ) {
		return '';
	}
	if ( '' === $end || $end === $start ) {
		return $start_fmt;
	}
	return sprintf(
		/* translators: 1: start date, 2: end date */
		__( '%1$s – %2$s', 'awesome-events' ),
		$start_fmt,
		awesome_events_format_date( $end )
	);
}

/**
 * Month section heading from a Y-m-d start date.
 *
 * @param string $start_date Event start date.
 * @return string
 */
function awesome_events_month_heading( $start_date ) {
	if ( '' === $start_date ) {
		return __( 'Undated', 'awesome-events' );
	}
	$timestamp = strtotime( $start_date . ' 12:00:00' );
	if ( false === $timestamp ) {
		return $start_date;
	}
	return wp_date( 'F Y', $timestamp );
}

/**
 * Group posts by Y-m month key from _event_start_date.
 *
 * @param WP_Post[] $posts Post objects.
 * @return array<string, WP_Post[]>
 */
function awesome_events_group_posts_by_month( $posts ) {
	$groups = array();

	foreach ( $posts as $post ) {
		$start = (string) get_post_meta( $post->ID, '_event_start_date', true );
		$key   = $start ? gmdate( 'Y-m', strtotime( $start . ' 12:00:00 UTC' ) ) : '0000-00';
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}
		$groups[ $key ][] = $post;
	}

	return $groups;
}

/**
 * HTML for the back-to-archive link on single events.
 *
 * @return string
 */
function awesome_events_back_link_html() {
	$url = get_post_type_archive_link( 'event' );
	if ( ! $url ) {
		$url = home_url( '/events/' );
	}

	return sprintf(
		'<nav class="awesome-events-back" aria-label="%1$s"><a class="awesome-events-back__link" href="%2$s">%3$s</a></nav>',
		esc_attr__( 'Event navigation', 'awesome-events' ),
		esc_url( $url ),
		esc_html__( '← View all events', 'awesome-events' )
	);
}

/**
 * HTML for the past-event notice (single event).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function awesome_events_past_notice_html( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	if ( ! $post_id || ! awesome_events_is_past( $post_id ) ) {
		return '';
	}

	$archive_url = get_post_type_archive_link( 'event' );
	if ( ! $archive_url ) {
		$archive_url = home_url( '/events/' );
	}

	return sprintf(
		'<div class="awesome-events-past-notice" role="status">%s</div>',
		sprintf(
			/* translators: %s: link to events archive */
			__( 'This event has passed — %s', 'awesome-events' ),
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $archive_url ),
				esc_html__( 'browse upcoming events →', 'awesome-events' )
			)
		)
	);
}

/**
 * Comma-separated linked taxonomy terms.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return string HTML.
 */
function awesome_events_the_terms_list( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	$links = array();
	foreach ( $terms as $term ) {
		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( get_term_link( $term ) ),
			esc_html( $term->name )
		);
	}

	return implode( ', ', $links );
}
