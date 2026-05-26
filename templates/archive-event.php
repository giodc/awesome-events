<?php
/**
 * Event archive template.
 *
 * Copy to override:
 *   wp-content/themes/your-theme/awesome-events/archive-event.php
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

get_header();

$filters = awesome_events_get_archive_filters();
?>
<div class="container">
<div class="awesome-events awesome-events-archive">
	<header class="awesome-events-archive__header">
		<h1 class="awesome-events-archive__title"><?php post_type_archive_title(); ?></h1>
		<?php if ( $filters['past'] ) : ?>
			<p class="awesome-events-archive__intro"><?php esc_html_e( 'Past events, listed by most recent date.', 'awesome-events' ); ?></p>
		<?php else : ?>
			<p class="awesome-events-archive__intro"><?php esc_html_e( 'Upcoming events, listed by date.', 'awesome-events' ); ?></p>
		<?php endif; ?>
	</header>

	<?php awesome_events_get_template( 'event-filters.php' ); ?>

	<?php awesome_events_get_template( 'event-calendar.php' ); ?>

	<?php if ( have_posts() ) : ?>
		<?php
		global $wp_query;
		$groups = awesome_events_group_posts_by_month( $wp_query->posts );
		?>

		<?php foreach ( $groups as $month_key => $month_posts ) : ?>
			<?php
			$first_start = (string) get_post_meta( $month_posts[0]->ID, '_event_start_date', true );
			$heading     = '0000-00' === $month_key
				? __( 'Undated', 'awesome-events' )
				: awesome_events_month_heading( $first_start );
			?>
			<section class="awesome-events-month">
				<h2 class="awesome-events-month__heading"><?php echo esc_html( $heading ); ?></h2>
				<div class="awesome-events-grid">
					<?php foreach ( $month_posts as $event ) : ?>
						<?php awesome_events_get_template( 'event-card.php', array( 'event' => $event ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>

		<nav class="awesome-events-pagination" aria-label="<?php esc_attr_e( 'Events pagination', 'awesome-events' ); ?>">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => __( '← Previous', 'awesome-events' ),
					'next_text' => __( 'Next →', 'awesome-events' ),
				)
			);
			?>
		</nav>
	<?php else : ?>
		<div class="awesome-events-empty">
			<p><?php esc_html_e( 'No events match your filters.', 'awesome-events' ); ?></p>
			<p><a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>"><?php esc_html_e( 'View all upcoming events', 'awesome-events' ); ?></a></p>
		</div>
	<?php endif; ?>
</div>
</div>

<?php
get_footer();
