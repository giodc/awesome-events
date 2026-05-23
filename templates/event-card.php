<?php
/**
 * Event card for archive listings.
 *
 * Override: your-theme/awesome-events/event-card.php
 *
 * @package AwesomeEvents
 *
 * @var WP_Post $event Event post (passed via awesome_events_get_template).
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $event ) || ! $event instanceof WP_Post ) {
	return;
}

$post_id = $event->ID;
$meta    = awesome_events_get_meta( $post_id );
$date    = awesome_events_format_date_range( $meta['start_date'], $meta['end_date'] );
?>
<article id="event-<?php echo esc_attr( (string) $post_id ); ?>" class="awesome-events-card">
	<?php if ( has_post_thumbnail( $post_id ) ) : ?>
		<figure class="awesome-events-card__media">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="awesome-events-card__body">
		<?php if ( $date ) : ?>
			<p class="awesome-events-card__date"><?php echo esc_html( $date ); ?></p>
		<?php endif; ?>

		<h2 class="awesome-events-card__title">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</a>
		</h2>

		<?php if ( $meta['location'] ) : ?>
			<p class="awesome-events-card__meta"><?php echo esc_html( $meta['location'] ); ?></p>
		<?php endif; ?>

		<?php if ( has_excerpt( $post_id ) ) : ?>
			<p class="awesome-events-card__meta"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
		<?php endif; ?>

		<div class="awesome-events-card__tags">
			<?php
			$city_terms = get_the_terms( $post_id, 'event_city' );
			if ( $city_terms && ! is_wp_error( $city_terms ) ) :
				foreach ( $city_terms as $term ) :
					?>
					<a class="awesome-events-tag" href="<?php echo esc_url( awesome_events_archive_url( array( 'event_city' => $term->slug ) ) ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</a>
					<?php
				endforeach;
			endif;

			$type_terms = get_the_terms( $post_id, 'event_type' );
			if ( $type_terms && ! is_wp_error( $type_terms ) ) :
				foreach ( $type_terms as $term ) :
					?>
					<a class="awesome-events-tag" href="<?php echo esc_url( awesome_events_archive_url( array( 'event_type' => $term->slug ) ) ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</a>
					<?php
				endforeach;
			endif;
			?>
		</div>
	</div>
</article>
