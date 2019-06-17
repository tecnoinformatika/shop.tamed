<div class="os-widget os-widget-animated os-widget-top-agents" data-os-reload-action="<?php echo OsRouterHelper::build_route_name('dashboard', 'widget_top_agents'); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php _e('Top Agents', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<div class="os-date-range-picker">
				<span class="range-picker-value"><?php echo $date_period_string; ?></span>
				<input type="hidden" name="date_from" value="<?php echo $date_from; ?>"/>
				<input type="hidden" name="date_to" value="<?php echo $date_to; ?>"/>
				<i class="latepoint-icon latepoint-icon-chevron-down"></i>
			</div>
		</div>
	</div>
	<?php 
	if($top_agents){
		foreach($top_agents as $top_agent){ 
			$agent = new OsAgentModel($top_agent->agent_id);
			?>
			<a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $agent->id) ) ?>" class="agent-info-box-small">
				<div class="avatar-w" style="background-image: url(<?php echo $agent->get_avatar_url(); ?>);"></div>
				<div class="agent-info">
					<div class="agent-name"><?php echo $agent->full_name; ?></div>
					<div class="agent-sub-info">
						<span class="label"><?php _e('Hours:', 'latepoint'); ?></span>
						<span class="value"><?php echo round($top_agent->total_minutes/60, 1); ?></span>
					</div>
				</div>
				<div class="agent-circle-chart">
					<div class="circle-chart" data-max-value="<?php echo $total_bookings; ?>" data-chart-value="<?php echo $top_agent->total_appointments; ?>" data-chart-color="#EDF9EF" data-chart-color-fade="#7DD5A2" id="agentCircle<?php echo $agent->id; ?>"></div>
				</div>
			</a>
		<?php } ?>
	<?php }else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
		    <h2><?php _e('No Appointments in Period', 'latepoint'); ?></h2>
		    <a href="#" <?php echo OsBookingHelper::quick_booking_btn_html(); ?> class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Create Appointment', 'latepoint'); ?></span></a>
		  </div>
	<?php } ?>
</div>