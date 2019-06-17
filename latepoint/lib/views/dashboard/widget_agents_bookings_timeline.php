<div class="os-widget os-widget-agents-bookings-timeline os-widget-animated" data-os-reload-action="<?php echo OsRouterHelper::build_route_name('dashboard', 'widget_agents_bookings_timeline'); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php _e('Appointments Timeline', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<div class="os-date-range-picker" data-single-date="yes">
				<span class="range-picker-value"><?php echo $target_date_string; ?></span>
				<i class="latepoint-icon latepoint-icon-chevron-down"></i>
				<input type="hidden" name="date_from" value="<?php echo $target_date; ?>"/>
				<input type="hidden" name="date_to" value="<?php echo $target_date; ?>"/>
			</div>
		</div>
	</div>
	<?php if($agents){ ?>

		<?php 
		$work_start_end_time = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $target_date]);
		$work_start_minutes = $work_start_end_time[0];
		$work_end_minutes = $work_start_end_time[1];
		$work_total_minutes = $work_end_minutes - $work_start_minutes;
		$timeblock_interval = OsSettingsHelper::get_timeblock_interval();
		?>
		<div class="agent-day-bookings-timeline-compact-w">
			<div class="agents-avatars">
					<?php foreach($agents as $agent){ ?>
						<a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $agent->id) ) ?>" class="avatar-w" style="background-image: url(<?php echo $agent->get_avatar_url(); ?>);"><span><?php echo $agent->full_name; ?></span></a>
					<?php } ?>
			</div>
			<div class="agents-timelines-w">
				<div class="timeline-top-w">
					<?php
					for($timeslot = $work_start_minutes; $timeslot <= $work_end_minutes; $timeslot+= $timeblock_interval){
						if(($timeslot % 60) == 0){
							echo '<div class="timeslot with-tick"><div class="tick"></div><div class="timeslot-time"><div class="timeslot-hour">'.OsTimeHelper::minutes_to_hours($timeslot).'</div><div class="timeslot-ampm">'.OsTimeHelper::am_or_pm($timeslot).'</div></div></div>';
						}else{
							echo '<div class="timeslot"></div>';
						}
					}
					?>
				</div>
				<?php foreach($agents as $agent){ ?>
					<div class="agent-timeline-w">
						<div class="agent-timeline">
							<?php 
							$daily_bookings = $agent->bookings_for_date($target_date, OsLocationHelper::get_selected_location_id());
							if($daily_bookings && $work_total_minutes){
								foreach($daily_bookings as $booking){
									$width = ($booking->end_time - $booking->start_time) / $work_total_minutes * 100;
									$left = ($booking->start_time - $work_start_minutes) / $work_total_minutes * 100;
									if($width <= 0 || $left >= 100 || (($left + $width) <= 0)) continue;
									if($left < 0){
										$width = $width + $left;
										$left = 0;
									}
									if(($left + $width) > 100) $width = 100 - $left;
									echo '<div class="booking-block" 
														style="background-color: '.$booking->service->bg_color.'; left: '.$left.'%; width: '.$width.'%"
														'.OsBookingHelper::quick_booking_btn_html($booking->id).'>';
										$hide_agent_info = true;
										include('_booking_info_box_small.php');
									echo '</div>';
								}
							}
							do_action('latepoint_appointments_timeline', OsWpDateTime::os_createFromFormat('Y-m-d', $target_date), ['agent_id' => $agent->id,'work_start_minutes' => $work_start_minutes, 'work_end_minutes' => $work_end_minutes, 'work_total_minutes' => $work_total_minutes]);
							?>
						</div>
					</div>
				<?php } ?>
				<?php if(count($agents) > 1){ ?>
					<div class="timeline-bottom-w">
						<?php
						for($timeslot = $work_start_minutes; $timeslot <= $work_end_minutes; $timeslot+= $timeblock_interval){
							if(($timeslot % 60) == 0){
								echo '<div class="timeslot with-tick"><div class="timeslot-time"><div class="timeslot-hour">'.OsTimeHelper::minutes_to_hours($timeslot).'</div><div class="timeslot-ampm">'.OsTimeHelper::am_or_pm($timeslot).'</div></div></div>';
							}else{
								echo '<div class="timeslot"></div>';
							}
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>

	<?php }else{ ?>

		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-user-plus"></i></div>
		    <h2><?php _e('No Agents Created', 'latepoint'); ?></h2>
		    <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'new_form') ) ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Create Agent', 'latepoint'); ?></span></a>
		  </div>
	<?php } ?>
</div>