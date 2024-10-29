<div class="wrap">

	<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

	<form method="post" action="options.php" id="schedule_selections">
		<!-- Display necessary hidden fields for settings -->
		<?php settings_fields( 'autonettv_relay_api_settings_schedule_fields' ); ?>

		<!-- Display the settings sections for the page -->
		<?php do_settings_sections( 'autonettv-relay-api-schedule-selections' ); ?>

		<!-- Default Submit Button -->
		<?php submit_button(); ?>
	</form>

<?php

$schedule = get_option( 'autonettv_relay_api_settings_schedule_fields' );

if(isset($schedule['frequency']) && isset($schedule['start_date']) && isset($schedule['end_date'])) {

    echo "<p>Scheduling Cron Jobs</p>";

    $taskToSchedule = 'autonettv_relay_api_events_hook';

	add_action($taskToSchedule,'autonettv_relay_api_events()');

	if ( ! wp_next_scheduled( $taskToSchedule ) && ! wp_installing() ) {
		wp_schedule_event( time(), $schedule['frequency'], $taskToSchedule);
	} else {
		wp_clear_scheduled_hook($taskToSchedule);
		wp_schedule_event( time(), $schedule['frequency'], $taskToSchedule );
    }

}

$nextScheduled = wp_next_scheduled( 'autonettv_relay_api_events_hook' );
echo "<p>Artical Post Sync (APS) will run next: " . date("D, M j, Y G:i:s", $nextScheduled) . "</p>";

echo "<!--";
$events = wp_get_scheduled_event('autonettv_relay_api_events_hook');
print_r($events);
echo "-->";