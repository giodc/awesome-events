<?php
/**
 * Event archive filters (upcoming/past, city, type).
 *
 * Override: your-theme/awesome-events/event-filters.php
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

$filters = awesome_events_get_archive_filters();
$archive = get_post_type_archive_link( 'event' );
if ( ! $archive ) {
	$archive = home_url( '/events/' );
}

$upcoming_url = awesome_events_archive_url(
	array(
		'past'       => null,
		'event_city' => $filters['event_city'] ? $filters['event_city'] : null,
		'event_type' => $filters['event_type'] ? $filters['event_type'] : null,
	)
);
$past_url     = awesome_events_archive_url(
	array(
		'past'       => '1',
		'event_city' => $filters['event_city'] ? $filters['event_city'] : null,
		'event_type' => $filters['event_type'] ? $filters['event_type'] : null,
	)
);

$cities = awesome_events_get_filter_terms( 'event_city', $filters['event_city'] );
$types  = awesome_events_get_filter_terms( 'event_type', $filters['event_type'] );
?>
<form class="awesome-events-filters" method="get" action="<?php echo esc_url( $archive ); ?>">
	<div class="awesome-events-filters__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Event timeframe', 'awesome-events' ); ?>">
		<a
			class="awesome-events-filters__tab<?php echo ! $filters['past'] ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $upcoming_url ); ?>"
			<?php echo ! $filters['past'] ? ' aria-current="page"' : ''; ?>
		>
			<?php esc_html_e( 'Upcoming', 'awesome-events' ); ?>
		</a>
		<a
			class="awesome-events-filters__tab<?php echo $filters['past'] ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $past_url ); ?>"
			<?php echo $filters['past'] ? ' aria-current="page"' : ''; ?>
		>
			<?php esc_html_e( 'Past events', 'awesome-events' ); ?>
		</a>
	</div>

	<?php if ( $filters['past'] ) : ?>
		<input type="hidden" name="past" value="1">
	<?php endif; ?>

	<?php
	$cal_month = awesome_events_get_calendar_month();
	if ( ! empty( $cal_month['key'] ) ) :
		?>
		<input type="hidden" name="cal_month" value="<?php echo esc_attr( $cal_month['key'] ); ?>">
	<?php endif; ?>

	<div class="awesome-events-filters__field">
		<label for="awesome-events-filter-city"><?php esc_html_e( 'City', 'awesome-events' ); ?></label>
		<select name="event_city" id="awesome-events-filter-city">
			<option value=""><?php esc_html_e( 'All cities', 'awesome-events' ); ?></option>
			<?php foreach ( $cities as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['event_city'], $term->slug ); ?>>
						<?php echo esc_html( $term->name ); ?>
					</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="awesome-events-filters__field">
		<label for="awesome-events-filter-type"><?php esc_html_e( 'Event type', 'awesome-events' ); ?></label>
		<select name="event_type" id="awesome-events-filter-type">
			<option value=""><?php esc_html_e( 'All types', 'awesome-events' ); ?></option>
			<?php foreach ( $types as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['event_type'], $term->slug ); ?>>
						<?php echo esc_html( $term->name ); ?>
					</option>
			<?php endforeach; ?>
		</select>
	</div>

	<button type="submit" class="awesome-events-filters__submit">
		<?php esc_html_e( 'Apply filters', 'awesome-events' ); ?>
	</button>
</form>
