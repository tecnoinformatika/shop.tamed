<div class="os-row">
	<div class="os-col-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo $total_openings; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('Openings', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
			</div>
		</div>
	</div>
	<div class="os-col-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo $total_bookings; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('Appointments', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
			</div>
		</div>
	</div>
	<div class="os-col-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo $total_pending_bookings; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('Need Approval', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php _e('Total', 'latepoint'); ?></div>
			</div>
		</div>
	</div>
</div>
<div class="os-row">
	<div class="os-col-12">
		<?php echo $widget_agents_bookings_timeline; ?>
	</div>
</div>
<div class="os-row">
	<div class="os-col-8 os-col-br">
		<?php echo $widget_upcoming_appointments; ?>
	</div>
	<div class="os-col-4">
		<div class="os-widget os-widget-animated">
			<div class="os-widget-header with-actions">
				<h3 class="os-widget-header-text"><?php _e('By Service Type', 'latepoint'); ?></h3>
			</div>
			<?php if($services_count_by_types){ ?>
				<div class="service-type-donut-chart-w on-agent-dashboard">
					<div class="service-type-donut-chart-i">
					<canvas class="os-donut-chart" style="width: 150px; height: 150px;" 
						data-chart-labels="<?php echo $service_types_chart_labels_string; ?>" 
						data-chart-colors="<?php echo $service_types_chart_data_colors; ?>" 
						data-chart-values="<?php echo $service_types_chart_data_values_string; ?>"></canvas>
					</div>
					<div class="service-type-donut-chart-data">
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
			<?php }else{ ?>
			  <div class="no-results-w">
			    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
			    <h2><?php _e('No Appointments Today', 'latepoint'); ?></h2>
			    <a href="#" <?php echo OsBookingHelper::quick_booking_btn_html(); ?> class="latepoint-btn">
			    	<i class="latepoint-icon latepoint-icon-plus-square"></i>
			    	<span><?php _e('Create Appointment', 'latepoint'); ?></span>
		    	</a>
			  </div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="os-row">
	<div class="os-col-12">
		<div class="tall-slots-timeline">
			<?php echo $widget_agents_availability_timeline; ?>
		</div>
	</div>
</div>
<div class="os-row">
	<div class="os-col-12">
		<?php echo $widget_daily_bookings_chart; ?>
	</div>
</div>