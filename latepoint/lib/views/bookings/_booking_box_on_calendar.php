<?php
$booking_duration = $booking->end_time - $booking->start_time;
if($booking_duration <= 0) $booking_duration = $booking->service->duration;
$booking_duration_percent = $booking_duration * 100 / $work_total_minutes;
$booking_start_percent = ($booking->start_time - $work_start_minutes) / ($work_end_minutes - $work_start_minutes) * 100;
if($booking_start_percent < 0) $booking_start_percent = 0;
$buffer_before_height_percent = $booking->buffer_before / $booking_duration * 100;
$buffer_after_height_percent = $booking->buffer_after / $booking_duration * 100;
?>
<div class="ch-day-booking status-<?php echo $booking->status; ?>" <?php echo OsBookingHelper::quick_booking_btn_html($booking->id); ?> style="top: <?php echo $booking_start_percent; ?>%; height: <?php echo $booking_duration_percent; ?>%; background-color: <?php echo $booking->service->bg_color; ?>">
	<?php if($buffer_before_height_percent) echo '<div class="ch-day-buffer-before" style="height: '.$buffer_before_height_percent.'%;"></div>'; ?>
	<div class="ch-day-booking-i">
		<div class="booking-service-name"><?php echo $booking->service->name; ?></div>
		<div class="booking-time"><?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->start_time); ?> - <?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->end_time); ?></div>
	</div>
	<?php if($buffer_after_height_percent) echo '<div class="ch-day-buffer-after" style="height: '.$buffer_after_height_percent.'%;"></div>'; ?>
</div>