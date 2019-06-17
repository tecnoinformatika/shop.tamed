<?php 
if($agents){ ?>
	<?php if(!$calendar_only){ ?>
		<div class="page-header-main-actions">
			<label><?php _e('Calendar Month', 'latepoint'); ?></label>
			<?php echo OsFormHelper::select_field('monthly_calendar_month_select', false, OsUtilHelper::get_months_for_select(), $calendar_start_date->format('n')); ?>
			<?php echo OsFormHelper::select_field('monthly_calendar_year_select', false, [OsTimeHelper::today_date('Y') - 1, OsTimeHelper::today_date('Y'), OsTimeHelper::today_date('Y') + 1], $calendar_start_date->format('Y')); ?>
		</div>
	<?php } ?>
	<?php
	$agents_head_html = '';
	foreach($agents as $agent){
		$agents_head_html.= 
			'<div class="ma-head-agent">
				<div class="ma-head-agent-avatar" style="background-image: url('.$agent->get_avatar_url().')"></div>
				<div class="ma-head-agent-name">'.$agent->full_name.'</div>
			</div>';
	}
	$calendar_not_scrollable_class = (count($agents) > 4) ? '' : 'calendar-month-not-scrollable';
	if(!$calendar_only) echo '<div class="calendar-month-agents-w '.$calendar_not_scrollable_class.'" data-route="'.OsRouterHelper::build_route_name('bookings', 'monthly_agents').'">';
		echo '<div class="ma-floated-days-w">';
			echo '<div class="ma-head-info"><span>'.__('Date', 'latepoint').'</span><span>'.__('Agent', 'latepoint').'</span></div>';
			$calendar_start_date = new OsWpDateTime($start_date_string);
			for($i = 0; $i < $calendar_start_date->format('d'); $i++){
		    list($work_start_minutes, $work_end_minutes) = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $calendar_start_date->format('Y-m-d')]);
				$total_work_time = $work_end_minutes - $work_start_minutes;
				echo '<div class="ma-day ma-day-number-'.$calendar_start_date->format('N').'">';
					echo '<div class="ma-day-info">';
						echo '<span class="ma-day-number">'.$calendar_start_date->format('j').'</span>';
						echo '<span class="ma-day-weekday">'.OsUtilHelper::get_weekday_name_by_number($calendar_start_date->format('N'), true).'</span>';
					echo '</div>';
				echo '</div>';
		    $calendar_start_date->modify('+1 day');
			}
		echo '</div>';
		echo '<div class="ma-days-with-bookings-w">';
			echo '<div class="ma-days-with-bookings-i">';
				echo '<div class="ma-head">';
					echo $agents_head_html;
				echo '</div>';
				$calendar_start_date = new OsWpDateTime($start_date_string);
				for($i = 0; $i < $calendar_start_date->format('d'); $i++){
					echo '<div class="ma-day ma-day-number-'.$calendar_start_date->format('N').'">';
						foreach($agents as $agent){
					    list($work_start_minutes, $work_end_minutes) = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $calendar_start_date->format('Y-m-d'), 'agent_id' => $agent->id]);
							$total_work_time = $work_end_minutes - $work_start_minutes;
							echo '<div class="ma-day-agent-bookings">';
								if($total_work_time > 0){
									$calendar_start_date_appointments = new OsBookingModel();
									$calendar_start_date_appointments = $calendar_start_date_appointments->should_not_be_cancelled()->where(array('start_date' => $calendar_start_date->format('Y-m-d'), 'agent_id' => $agent->id))->get_results_as_models();
									if($calendar_start_date_appointments){
										foreach($calendar_start_date_appointments as $booking){
												$width = ($booking->end_time - $booking->start_time) / $total_work_time * 100;
												$left = ($booking->start_time - $work_start_minutes) / $total_work_time * 100;

												echo '<div class="ma-day-booking" 
																style="left: '.$left.'%; width: '.$width.'%; background-color: '.$booking->service->bg_color.'" 
																'.OsBookingHelper::quick_booking_btn_html($booking->id).'>';
																$hide_agent_info = true;
																include(LATEPOINT_VIEWS_ABSPATH.'dashboard/_booking_info_box_small.php');
												echo '</div>';
										}
									}
								}else{
									echo '<div class="ma-day-off"><span>'.__('Day Off', 'latepoint').'</span></div>';
								}
							echo '</div>';
						}
					echo '</div>';
			    $calendar_start_date->modify('+1 day');
			}
			echo '</div>';
		echo '</div>';
	if(!$calendar_only) echo '</div>';
}else{ ?>
  <div class="no-results-w">
    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
    <h2><?php _e('No Agents Created', 'latepoint'); ?></h2>
    <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'new_form') ) ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Create Agent', 'latepoint'); ?></span></a>
  </div>
<?php } ?>
