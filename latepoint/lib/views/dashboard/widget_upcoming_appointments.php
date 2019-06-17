<div class="os-widget os-widget-animated os-widget-upcoming-appointments" data-os-reload-action="<?php echo OsRouterHelper::build_route_name('dashboard', 'widget_upcoming_appointments'); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php _e('Proximas citas', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<?php $agents_selector_css = (is_array($agents) && (count($agents) == 1)) ? 'display: none;' : ''; ?>
			<select name="agent_id" id="" class="os-trigger-reload-widget" style="<?php echo $agents_selector_css; ?>">
				<option value=""><?php _e('Todos los técnicos', 'latepoint'); ?></option>
				<?php if($agents){ ?>
					<?php foreach($agents as $agent){ ?>
					<option value="<?php echo $agent->id ?>" <?php if($agent->id == $agent_id) echo 'selected="selected"' ?>><?php echo $agent->full_name; ?></option>
					<?php } ?>
				<?php } ?>
			</select>
			<select name="service_id" id="" class="os-trigger-reload-widget">
				<option value=""><?php _e('Todos los servicios', 'latepoint'); ?></option>
				<?php if($services){ ?>
					<?php foreach($services as $service){ ?>
					<option value="<?php echo $service->id ?>" <?php if($service->id == $service_id) echo 'selected="selected"' ?>><?php echo $service->name; ?></option>
					<?php } ?>
				<?php } ?>
			</select>
		</div>		
	</div>
	<?php if($upcoming_bookings){ ?>
		<?php foreach($upcoming_bookings as $booking){
						$hide_agent_info = true;
						include('_booking_info_box_small.php'); ?>
		<?php } ?>
	<?php }else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
		    <h2><?php _e('No hay citas próximas', 'latepoint'); ?></h2>
		    <a href="#" <?php echo OsBookingHelper::quick_booking_btn_html(); ?> class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php _e('Crear cita', 'latepoint'); ?></span></a>
		  </div>
	<?php } ?>
</div>