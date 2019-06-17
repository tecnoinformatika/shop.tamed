<div class="step-verify-w latepoint-step-content">
  <div class="latepoint-step-content-text-left">
    <div><?php _e('Verifique la información de su reserva, puede volver a editarla o hacer clic en el botón "Enviar Solicitud" para confirmar su reserva..', 'latepoint'); ?></div>
  </div>
  <div class="confirmation-info-w">
  	<div class="confirmation-app-info">
		  <h5><?php _e('Appointment Info', 'latepoint'); ?></h5>
		  <ul>
		  	<li><?php _e('Date:', 'latepoint'); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $booking->start_timestamp ); ?></strong></li>
		  	<li>
          <?php _e('Time:', 'latepoint'); ?> 
          <strong>
            <?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->start_time); ?>
            <?php if(OsSettingsHelper::get_settings_value('show_booking_end_time') == 'on') echo ' - '. OsTimeHelper::minutes_to_hours_and_minutes($booking->calculate_end_time()); ?>
          </strong>
        </li>
        <?php if(!empty($booking->location->full_address)){ ?>
          <li><?php _e('Ubicación:', 'latepoint'); ?> <strong><?php echo $booking->location->full_address; ?></strong></li>
        <?php } ?>
        <?php if(!OsSettingsHelper::is_on('steps_hide_agent_info')){ ?>
    	  	<li><?php _e('Técnico:', 'latepoint'); ?> <strong><?php echo $booking->get_agent_full_name(); ?></strong></li>
        <?php } ?>
		  	<li><?php _e('Servicio:', 'latepoint'); ?> <strong><?php echo $booking->service->name; ?></strong></li>
		  </ul>
  	</div>
  	<div class="confirmation-customer-info">
		  <h5><?php _e('Información de cliente', 'latepoint'); ?></h5>
		  <ul>
		  	<li><?php _e('Nombre:', 'latepoint'); ?> <strong><?php echo $customer->full_name; ?></strong></li>
		  	<li><?php _e('Teléfono:', 'latepoint'); ?> <strong><?php echo $customer->formatted_phone; ?></strong></li>
		  	<li><?php _e('Email:', 'latepoint'); ?> <strong><?php echo $customer->email; ?></strong></li>
        <?php if($custom_fields_for_customer){
          foreach($custom_fields_for_customer as $custom_field){
            echo '<li>'.$custom_field['label'].': <strong>'.$customer->get_meta_by_key($custom_field['id'], __('n/a', 'latepoint')).'</strong></li>';
          }
        } ?>
		  </ul>
  	</div>
  </div>
  <?php if(OsSettingsHelper::is_accepting_payments() && ($booking->full_amount_to_charge() > 0)){ ?>
  <div class="payment-summary-info">
    <h5><?php _e('Payment Info', 'latepoint'); ?></h5>
    <div class="confirmation-info-w">
      <div class="confirmation-app-info">
        <ul>
          <li><?php _e('Payment Method:', 'latepoint'); ?> <strong><?php echo $booking->payment_method_nice_name; ?></strong></li>
        </ul>
      </div>
      <div class="confirmation-customer-info">
        <ul>
          <li>
          <?php if($booking->payment_method == LATEPOINT_PAYMENT_METHOD_LOCAL){
            echo __('Balance Due:', 'latepoint').'<strong>'.$booking->formatted_full_price().'</strong>';
          }else{
            if($booking->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT){
              echo __('Deposit Amount:', 'latepoint').'<strong>'.$booking->formatted_deposit_price().'</strong>';
            }else{
              echo __('Charge Amount:', 'latepoint').'<strong>'.$booking->formatted_full_price().'</strong>';
            }
          } ?>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <?php } ?>
</div>
<?php if(!isset($no_params)) include '_booking_params.php'; ?>