<div class="os-row">
	<div class="os-col-lg-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo ($total_agents_on_duty) ? $total_agents_on_duty : '0'; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('Agents on duty', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
			</div>
		</div>
	</div>
	<div class="os-col-lg-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo ($total_bookings) ? $total_bookings : '0'; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('Appointments', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
			</div>
		</div>
	</div>
	<div class="os-col-lg-4">
		<div class="os-info-tile">
			<div class="os-tile-value"><?php echo ($total_new_customers_for_date) ? $total_new_customers_for_date : '0'; ?></div>
			<div class="os-tile-info">
				<div class="os-tile-label"><?php _e('New Customers', 'latepoint'); ?></div>
				<div class="os-tile-desc"><?php echo($nice_selected_date); ?></div>
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
	<div class="os-col-lg-6 os-col-lg-br">
		<?php echo $widget_agents_availability_timeline; ?>
	</div>
	<div class="os-col-lg-6">
		<?php echo $widget_top_agents; ?>
	</div>
</div>

