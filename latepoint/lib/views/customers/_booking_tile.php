<div class="customer-booking status-<?php echo $booking->status; ?>" data-id="<?php echo $booking->id; ?>">
	<h6 class="customer-booking-service-name"><?php echo $booking->service->name; ?></h6>
	<div class="customer-booking-service-color"></div>
	<div class="customer-booking-info">
		<div class="customer-booking-info-row"><span class="booking-info-label"><?php _e('Fecha', 'latepoint'); ?></span><span class="booking-info-value"><?php echo $booking->nice_start_date; ?></span></div>
		<div class="customer-booking-info-row"><span class="booking-info-label"><?php _e('Hora', 'latepoint'); ?></span><span class="booking-info-value"><?php echo $booking->nice_start_time; ?> - <?php echo $booking->nice_end_time; ?></span></div>
		<div class="customer-booking-info-row"><span class="booking-info-label"><?php _e('Técnico', 'latepoint'); ?></span><span class="booking-info-value"><?php echo $booking->agent->full_name; ?></span></div>
		<div class="customer-booking-info-row"><span class="booking-info-label"><?php _e('Estado', 'latepoint'); ?></span><span class="booking-info-value status-<?php echo $booking->status; ?>"><?php echo $booking->nice_status; ?></span></div>
	</div>
	<?php if($editable_booking){ ?>
		<div class="customer-booking-buttons">
			<a href="<?php echo $booking->ical_download_link; ?>" target="_blank" class="latepoint-btn">
				<i class="latepoint-icon latepoint-icon-ui-83"></i>
				<span><?php _e('Agregar al calendario', 'latepoint'); ?></span>
			</a>
			<?php /* <a href="#" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-ui-46"></i><span><?php _e('Edit', 'latepoint'); ?></span></a> */ ?>
			<a href="#" class="latepoint-btn latepoint-btn-danger latepoint-request-booking-cancellation" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'request_cancellation'); ?>">
				<i class="latepoint-icon latepoint-icon-ui-24"></i>
				<span><?php _e('Cancelar', 'latepoint'); ?></span>
			</a>
		</div>
	<?php } ?>
</div>