<?php
/**
 * Monthly event calendar for the archive.
 *
 * Override: your-theme/awesome-events/event-calendar.php
 *
 * @package AwesomeEvents
 */

defined( 'ABSPATH' ) || exit;

$cal        = awesome_events_get_calendar_month();
$year       = $cal['year'];
$month      = $cal['month'];
$events     = awesome_events_get_calendar_events_by_day( $year, $month );
$grid       = awesome_events_get_calendar_grid( $year, $month );
$weekdays   = awesome_events_get_calendar_weekdays();
$today      = awesome_events_today();
$month_ts   = strtotime( sprintf( '%04d-%02d-01', $year, $month ) . ' 12:00:00' );
$title      = wp_date( 'F Y', $month_ts );

$tz         = wp_timezone();
$prev_month = ( new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), $tz ) )->modify( '-1 month' );
$next_month = ( new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), $tz ) )->modify( '+1 month' );

$prev_url = awesome_events_archive_url( array( 'cal_month' => $prev_month->format( 'Y-m' ) ) );
$next_url = awesome_events_archive_url( array( 'cal_month' => $next_month->format( 'Y-m' ) ) );
?>
<section class="awesome-events-calendar" aria-labelledby="awesome-events-calendar-title">
	<div class="awesome-events-calendar__nav">
		<a class="awesome-events-calendar__nav-btn" href="<?php echo esc_url( $prev_url ); ?>">
			<span aria-hidden="true">←</span>
			<?php echo esc_html( wp_date( 'M Y', $prev_month->getTimestamp() ) ); ?>
		</a>
		<h2 id="awesome-events-calendar-title" class="awesome-events-calendar__title"><?php echo esc_html( $title ); ?></h2>
		<a class="awesome-events-calendar__nav-btn" href="<?php echo esc_url( $next_url ); ?>">
			<?php echo esc_html( wp_date( 'M Y', $next_month->getTimestamp() ) ); ?>
			<span aria-hidden="true">→</span>
		</a>
	</div>

	<div class="awesome-events-calendar__weekdays" aria-hidden="true">
		<?php foreach ( $weekdays as $label ) : ?>
			<span class="awesome-events-calendar__weekday"><?php echo esc_html( $label ); ?></span>
		<?php endforeach; ?>
	</div>

	<div class="awesome-events-calendar__grid" role="grid" aria-label="<?php echo esc_attr( $title ); ?>">
		<?php foreach ( $grid as $date ) : ?>
			<?php if ( null === $date ) : ?>
				<div class="awesome-events-calendar__cell awesome-events-calendar__cell--pad" role="gridcell" aria-hidden="true"></div>
			<?php else : ?>
				<?php
				$day_num   = (int) substr( $date, 8, 2 );
				$is_today  = ( $date === $today );
				$day_items = $events[ $date ] ?? array();
				?>
				<div
					class="awesome-events-calendar__cell<?php echo $is_today ? ' is-today' : ''; ?><?php echo $day_items ? ' has-events' : ''; ?>"
					role="gridcell"
				>
					<span class="awesome-events-calendar__day-num"><?php echo esc_html( (string) $day_num ); ?></span>
					<?php if ( $day_items ) : ?>
						<ul class="awesome-events-calendar__events">
							<?php foreach ( $day_items as $item ) : ?>
								<li>
									<a href="<?php echo esc_url( $item['url'] ); ?>" title="<?php echo esc_attr( $item['title'] ); ?>">
										<?php echo esc_html( $item['title'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
