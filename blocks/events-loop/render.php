<?php
/**
 * Events Loop block — front-end markup.
 *
 * @package AwesomeEvents
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$count   = isset( $attributes['count'] ) ? (int) $attributes['count'] : 6;
$count   = max( 1, min( 24, $count ) );
$heading = isset( $attributes['heading'] ) ? trim( (string) $attributes['heading'] ) : '';
$city    = isset( $attributes['eventCity'] ) ? sanitize_title( (string) $attributes['eventCity'] ) : '';
$type    = isset( $attributes['eventType'] ) ? sanitize_title( (string) $attributes['eventType'] ) : '';
$show_link = ! empty( $attributes['showArchiveLink'] );
$link_label = isset( $attributes['archiveLinkLabel'] ) ? trim( (string) $attributes['archiveLinkLabel'] ) : '';

$events = awesome_events_get_upcoming_events(
	array(
		'posts_per_page' => $count,
		'event_city'     => $city,
		'event_type'     => $type,
	)
);

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'awesome-events awesome-events-loop',
	)
);
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== $heading ) : ?>
		<h2 class="awesome-events-loop__heading"><?php echo esc_html( $heading ); ?></h2>
	<?php endif; ?>

	<?php if ( $events ) : ?>
		<div class="awesome-events-grid">
			<?php foreach ( $events as $event ) : ?>
				<?php awesome_events_get_template( 'event-card.php', array( 'event' => $event ) ); ?>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="awesome-events-empty"><?php esc_html_e( 'No upcoming events found.', 'awesome-events' ); ?></p>
	<?php endif; ?>

	<?php if ( $show_link ) : ?>
		<?php
		$archive_url = get_post_type_archive_link( 'event' );
		if ( ! $archive_url ) {
			$archive_url = home_url( '/events/' );
		}
		if ( '' === $link_label ) {
			$link_label = __( 'View all events', 'awesome-events' );
		}
		?>
		<p class="awesome-events-loop__more">
			<a class="awesome-events-loop__more-link" href="<?php echo esc_url( $archive_url ); ?>">
				<?php echo esc_html( $link_label ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
