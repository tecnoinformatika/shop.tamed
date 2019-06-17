<div class="step-datepicker-w latepoint-step-content">
  <div class="os-dates-w">
    <?php OsBookingHelper::generate_monthly_calendar($calendar_start_date, ['service_id' => $booking->service_id, 'agent_id' => $booking->agent_id, 'location_id' => $booking->location_id]); ?>
  </div>
  <div class="time-selector-w">
    <div class="times-header"><?php _e('Elegir hora de cita para', 'latepoint'); ?> <span></span></div>
    <div class="os-times-w">
      <div class="timeslots"></div>
    </div>
  </div>
</div>
<?php if(!isset($no_params)) include '_booking_params.php'; ?>