<?php
/**
 * Single event template.
 *
 * Copy to override:
 *   wp-content/themes/your-theme/awesome-events/single-event.php
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
<div class="awesome-events awesome-events-single">
	<?php echo awesome_events_back_link_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		$post_id = get_the_ID();
		$meta    = awesome_events_get_meta( $post_id );
		$date    = awesome_events_format_date_range( $meta['start_date'], $meta['end_date'] );
		?>

		<?php echo awesome_events_past_notice_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<article id="event-<?php the_ID(); ?>" <?php post_class( 'awesome-events-single__article' ); ?>>
			<header class="awesome-events-single__header">
				<h1 class="awesome-events-single__title"><?php the_title(); ?></h1>
				<?php if ( $date ) : ?>
					<p class="awesome-events-card__date"><?php echo esc_html( $date ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="awesome-events-single__featured">
					<?php the_post_thumbnail( 'large' ); ?>
				</figure>
			<?php endif; ?>

			<div class="awesome-events-single__layout">
				<div class="awesome-events-single__content wp-content">
					<?php the_content(); ?>
				</div>

				<aside class="awesome-events-details" aria-label="<?php esc_attr_e( 'Event details', 'awesome-events' ); ?>">
					<h2 class="awesome-events-details__title"><?php esc_html_e( 'Details', 'awesome-events' ); ?></h2>
					<ul class="awesome-events-details__list">
						<?php if ( $date ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Date', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value"><?php echo esc_html( $date ); ?></p>
							</li>
						<?php endif; ?>

						<?php if ( $meta['time'] ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Time', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value"><?php echo esc_html( $meta['time'] ); ?></p>
							</li>
						<?php endif; ?>

						<?php if ( $meta['location'] ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Venue', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value"><?php echo esc_html( $meta['location'] ); ?></p>
							</li>
						<?php endif; ?>

						<?php if ( $meta['address'] ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Address', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value"><?php echo esc_html( $meta['address'] ); ?></p>
							</li>
						<?php endif; ?>

						<?php if ( $meta['tickets'] ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Tickets / entry', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value"><?php echo esc_html( $meta['tickets'] ); ?></p>
							</li>
						<?php endif; ?>

						<?php if ( $meta['url'] ) : ?>
							<li>
								<span class="awesome-events-details__label"><?php esc_html_e( 'Official website', 'awesome-events' ); ?></span>
								<p class="awesome-events-details__value">
									<a href="<?php echo esc_url( $meta['url'] ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $meta['url'] ); ?>
									</a>
								</p>
							</li>
						<?php endif; ?>
					</ul>

					<?php
					$city_html = awesome_events_the_terms_list( $post_id, 'event_city' );
					$type_html = awesome_events_the_terms_list( $post_id, 'event_type' );
					if ( $city_html || $type_html ) :
						?>
						<div class="awesome-events-single__terms">
							<?php if ( $city_html ) : ?>
								<?php echo wp_kses_post( $city_html ); ?>
							<?php endif; ?>
							<?php if ( $type_html ) : ?>
								<?php echo wp_kses_post( $type_html ); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</aside>
			</div>
		</article>
	<?php endwhile; ?>
</div>
</div>

<?php
get_footer();
