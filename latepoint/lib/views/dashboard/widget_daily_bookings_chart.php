<div class="os-widget os-widget-animated os-widget-daily-bookings" data-os-reload-action="<?php echo OsRouterHelper::build_route_name('dashboard', 'widget_daily_bookings_chart'); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php _e('Daily Bookings', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<?php $agents_selector_css = (is_array($agents) && (count($agents) == 1)) ? 'display: none;' : ''; ?>
			<select name="agent_id" id="" class="os-trigger-reload-widget" style="<?php echo $agents_selector_css; ?>">
				<option value=""><?php _e('All Agents', 'latepoint'); ?></option>
				<?php foreach($agents as $agent){ ?>
				<option value="<?php echo $agent->id ?>" <?php if($agent->id == $agent_id) echo 'selected="selected"' ?>><?php echo $agent->full_name; ?></option>
				<?php } ?>
			</select>
			<select name="service_id" id="" class="os-trigger-reload-widget">
				<option value=""><?php _e('All Services', 'latepoint'); ?></option>
				<?php foreach($services as $service){ ?>
				<option value="<?php echo $service->id ?>" <?php if($service->id == $service_id) echo 'selected="selected"' ?>><?php echo $service->name; ?></option>
				<?php } ?>
			</select>
			<div class="os-date-range-picker">
				<span class="range-picker-value"><?php echo $date_period_string; ?></span>
				<input type="hidden" name="date_from" value="<?php echo $date_from; ?>"/>
				<input type="hidden" name="date_to" value="<?php echo $date_to; ?>"/>
				<i class="latepoint-icon latepoint-icon-chevron-down"></i>
			</div>
		</div>
	</div>
	<?php if($daily_bookings_chart_data_values_string){ ?>
		<div class="daily-bookings-chart-w">
			<canvas id="chartDailyBookings" 
				data-chart-labels="<?php echo $daily_bookings_chart_labels_string; ?>" 
				data-chart-values="<?php echo $daily_bookings_chart_data_values_string; ?>"></canvas>
		</div>
	<?php }else{ ?>
	  <div class="no-results-w">
	    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
	    <h2><?php _e('No Appointments Found', 'latepoint'); ?></h2>
	    <a href="#" <?php echo OsBookingHelper::quick_booking_btn_html(); ?> class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Create Appointment', 'latepoint'); ?></span></a>
	  </div>
	<?php } ?>
</div>