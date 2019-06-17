<?php 
if($future_bookings){ ?>
	<div class="os-sync-stat-tiles">
		<div class="os-info-tile os-tile-with-progress">
			<div class="os-tile-value"><?php echo '<span>'.$total_synced_future_bookings.'</span>'.__(' of ', 'latepoint').$total_future_bookings; ?></div>
			<div class="os-tile-label"><?php _e('Bookings Synced', 'latepoint'); ?></div>
			<a href="#" data-label-sync="<?php _e('Sync All Bookings to Google', 'latepoint'); ?>" data-label-cancel-sync="<?php _e('Stop Syncing Now', 'latepoint'); ?>" class="sync-all-bookings-to-google-trigger latepoint-btn latepoint-btn-outline latepoint-btn-sm">
				<i class="latepoint-icon latepoint-icon-grid-18"></i>
				<span><?php _e('Sync All Bookings to Google', 'latepoint'); ?></span>
			</a>
			<a href="#" data-os-prompt="<?php _e('Are you sure you want to remove all synced bookings from Google Calendar? They will remain in LatePoint, but will be removed from google calendar.', 'latepoint'); ?>" data-label-remove="<?php _e('Remove Bookings from Google Calendar', 'latepoint'); ?>" data-label-cancel-remove="<?php _e('Stop Removing', 'latepoint'); ?>" class="remove-all-bookings-from-google-trigger latepoint-btn latepoint-btn-outline latepoint-btn-danger latepoint-btn-sm">
				<i class="latepoint-icon latepoint-icon-x"></i>
				<span><?php _e('Remove Bookings from Google', 'latepoint'); ?></span>
			</a>
			<div class="os-tile-hor-progress-chart" data-total="<?php echo $total_future_bookings; ?>" data-value="<?php echo $total_synced_future_bookings; ?>"><div class="os-tile-hor-progress-chart-value" style="width: <?php echo $synced_bookings_percent; ?>%"></div></div>
		</div>
	</div>

	<div class="os-booking-tiny-boxes-container">
	<div class="os-booking-tiny-boxes-w">
		<?php
		$prev_date = false;
		foreach($future_bookings as $booking){ 
			$is_synced = $booking->get_meta_by_key('google_calendar_event_id', false);
			if(!$prev_date || $prev_date != $booking->start_date){
				if($prev_date) echo '</div></div><div class="os-booking-tiny-boxes-w">';
				$prev_date = $booking->start_date;
				echo '<div class="os-booking-tiny-box-date">
					<div class="os-day">'.$booking->format_start_date_and_time('j').'</div>
					<div class="os-month">'.$booking->format_start_date_and_time('F').'</div>
				</div><div class="os-booking-tiny-boxes-i">';
			} ?>
			<div class="os-booking-tiny-box <?php echo ($is_synced) ? 'is-synced' : 'not-synced'; ?> booking-status-<?php echo $booking->status; ?>">
				<div class="os-booking-unsync-google-trigger" data-os-action="<?php echo OsRouterHelper::build_route_name('google_calendar', 'remove_booking'); ?>"
																										data-os-after-call="latepoint_booking_unsynced" 
																										data-os-pass-this="yes" 
																										data-os-params="<?php echo OsUtilHelper::build_os_params(['booking_id' => $booking->id]); ?>"></div>
				<div class="os-booking-sync-google-trigger" data-os-action="<?php echo OsRouterHelper::build_route_name('google_calendar', 'sync_booking'); ?>"
																										data-os-remove-action="<?php echo OsRouterHelper::build_route_name('google_calendar', 'remove_booking'); ?>"
																										data-os-after-call="latepoint_booking_synced" 
																										data-os-pass-this="yes" 
																										data-os-params="<?php echo OsUtilHelper::build_os_params(['booking_id' => $booking->id]); ?>"></div>
				<div class="os-name"><?php echo $booking->service->name; ?></div>
				<div class="os-date"><?php echo $booking->nice_start_date; ?></div>
				<div class="os-date"><?php echo $booking->nice_start_time . ' - '. $booking->nice_end_time; ?></div>
				<a class="os-edit-booking-btn" href="#"<?php echo OsBookingHelper::quick_booking_btn_html($booking->id); ?>>
					<i class="latepoint-icon latepoint-icon-edit-2"></i>
					<span><?php _e('Edit Booking', 'latepoint'); ?></span>
				</a>
			</div>
		<?php } ?>
		</div>
	</div>
	</div><?php
}