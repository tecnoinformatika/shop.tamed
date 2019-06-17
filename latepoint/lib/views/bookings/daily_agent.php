<?php 
list($agent_work_start_minutes, $agent_work_end_minutes) = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $target_date->format('Y-m-d'), 'agent_id' => $selected_agent->id]);
$agent_total_work_time = $agent_work_end_minutes - $agent_work_start_minutes;
$day_off_class = ($agent_total_work_time > 0) ? '' : 'agent-has-day-off'; ?>
<div class="bookings-daily-agent" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'daily_agent'); ?>">
	<div class="daily-agent-calendar-w">
			<div class="calendar-daily-head-w">
				<div class="calendar-daily-target-date <?php echo $day_off_class; ?>"><?php echo OsUtilHelper::get_month_name_by_number($target_date->format('n')).' '.__($target_date->format('jS')); ?></div>
				<label for=""><?php _e('Agent: ', 'latepoint'); ?></label>
				<select name="agent_id" id="" class="agent-select os-trigger-reload-widget">
					<?php foreach($agents as $agent){ ?>
					<option value="<?php echo $agent->id ?>" <?php if($agent->id == $selected_agent_id) echo 'selected="selected"' ?>><?php echo $agent->full_name; ?></option>
					<?php } ?>
				</select>
			</div>
		<div class="calendar-daily-agent-w">
			<?php if(($work_start_minutes < $work_end_minutes) && ($timeblock_interval > 0)){ 
				$total_periods = floor(($work_end_minutes - $work_start_minutes) / $timeblock_interval) + 1;
				$period_height = floor(OsSettingsHelper::get_day_calendar_min_height() / $total_periods);
				$period_css = (($total_periods * 20) < OsSettingsHelper::get_day_calendar_min_height()) ? "height: {$period_height}px;" : ''; ?>

			<div class="calendar-hours">
				<div class="ch-hours">
					<?php for($minutes = $work_start_minutes; $minutes <= $work_end_minutes; $minutes+= $timeblock_interval){ ?>
						<?php 
						$period_class = 'chh-period';
						$period_class.= (($minutes == $work_end_minutes) || (($minutes + $timeblock_interval) > $work_end_minutes)) ? ' last-period' : '';
						$period_class.= (($minutes % 60) == 0) ? ' chh-period-hour' : ' chh-period-minutes';
						echo '<div class="'.$period_class.'" style="'.$period_css.'"><span>'.OsTimeHelper::minutes_to_hours_and_minutes($minutes).'</span></div>';
						?>
					<?php } ?>
				</div>
				<div class="ch-day-periods ch-day-<?php echo strtolower($target_date->format('N')); ?>">

					<?php for($minutes = $work_start_minutes; $minutes <= $work_end_minutes; $minutes+= $timeblock_interval){ ?>
						<?php 
						$period_class = 'chd-period';
						if($minutes > $agent_work_end_minutes || $minutes < $agent_work_start_minutes || !OsBookingHelper::is_minute_in_work_periods($minutes, $work_periods_arr)) $period_class.= ' chd-period-off ';
						$period_class.= (($minutes == $work_end_minutes) || (($minutes + $timeblock_interval) > $work_end_minutes)) ? ' last-period' : '';
						$period_class.= (($minutes % 60) == 0) ? ' chd-period-hour' : ' chd-period-minutes';
						$btn_params = OsBookingHelper::quick_booking_btn_html(false, array('start_time'=> $minutes, 'agent_id' => $selected_agent_id, 'start_date' => $target_date->format('Y-m-d')));
						echo '<div class="'.$period_class.'" '.$btn_params.' style="'.$period_css.'"><div class="chd-period-minutes-value">'.OsTimeHelper::minutes_to_hours_and_minutes($minutes).'</div></div>';
						?>
					<?php } ?>

					<?php 
					if($bookings){
						foreach($bookings as $booking){
							include('_booking_box_on_calendar.php');
						}
					}
					do_action('latepoint_calendar_daily_timeline', $target_date, ['agent_id' => $selected_agent_id, 'work_start_minutes' => $work_start_minutes, 'work_end_minutes' => $work_end_minutes, 'work_total_minutes' => $work_total_minutes]);
					?>
				</div>
			</div>
			<?php }else{ ?>
			  <div class="no-results-w">
			    <div class="icon-w"><i class="latepoint-icon latepoint-icon-calendar"></i></div>
			    <h2><?php _e('You have not set any working hours for this day.', 'latepoint'); ?></h2>
			    <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('settings', 'general')); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Edit Working Hours', 'latepoint'); ?></span></a>
			  </div>
			<?php } ?>
		</div>
	</div>
	<div class="daily-agent-side">
		<div class="dam-params">
			<h3><?php _e('Calendar', 'latepoint'); ?></h3>
			<label for=""><?php _e('Service:', 'latepoint'); ?></label>
			<select name="service_id" id="" class="service-select os-trigger-reload-widget">
				<?php foreach($services as $service){ ?>
				<option value="<?php echo $service->id ?>" <?php if($service->id == $selected_service_id) echo 'selected="selected"' ?>><?php echo $service->name; ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="daily-agent-monthly-calendar-w">
	    <?php 
	    $calendar_settings = ['service_id' => $selected_service_id, 
	    											'agent_id' => $selected_agent_id, 
	    											'location_id' => OsLocationHelper::get_selected_location_id(), 
	    											'number_of_months_to_preload' => 0,
	    											'allow_full_access' => true, 
	    											'highlight_target_date' => true];
	    OsBookingHelper::generate_monthly_calendar($target_date->format('Y-m-d'), $calendar_settings); ?>
    </div>
		<div class="daily-agent-availability-w">
    	<h3><?php echo __('Service Availability For', 'latepoint').' <span>'.$nice_selected_date.'<span>'; ?></h3>
	    <?php OsAgentHelper::availability_timeline($selected_agent, $selected_service, OsLocationHelper::get_selected_location(), $target_date->format('Y-m-d'), array('show_avatar' => false)); ?>
	  </div>
		<div class="os-row">
			<div class="os-col-6">
				<div class="os-info-tile tile-centered">
					<div class="os-tile-value"><?php echo $total_bookings; ?></div>
					<div class="os-tile-info">
						<div class="os-tile-label"><?php _e('Appointments', 'latepoint'); ?></div>
						<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
					</div>
				</div>
			</div>
			<div class="os-col-6">
				<div class="os-info-tile tile-centered">
					<div class="os-tile-value"><?php echo $total_openings; ?></div>
					<div class="os-tile-info">
						<div class="os-tile-label"><?php _e('Openings', 'latepoint'); ?></div>
						<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php if($services_count_by_types){ ?>
			<div class="service-type-donut-chart-w">
				<div class="os-row os-row-align-center">
					<div class="os-col-6">
						<canvas class="os-donut-chart" style="width: 200px; height: 200px;" 
							data-chart-labels="<?php echo $service_types_chart_labels_string; ?>" 
							data-chart-colors="<?php echo $service_types_chart_data_colors; ?>" 
							data-chart-values="<?php echo $service_types_chart_data_values_string; ?>"></canvas>
					</div>
					<div class="os-col-6">
						<div class="service-type-donut-chart-data">
							<h3 class="chart-heading"><?php _e('Service Types', 'latepoint'); ?></h3>
							<div class="chart-labels">
							<?php foreach($services_count_by_types as $service){ ?>
								<div class="chart-label">
									<div class="chart-label-color" style="background-color: <?php echo $service['bg_color']; ?>"></div>
									<div class="name"><?php echo $service['name']; ?></div>
									<div class="value"><?php echo sprintf(__('%d Appointments', 'latepoint'), $service['count']); ?></div>
								</div>
							<?php } ?>
						</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>