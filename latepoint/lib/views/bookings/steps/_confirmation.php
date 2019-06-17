<div class="step-confirmation-w latepoint-step-content">
  <h3 class="confirmation-header"><?php _e('Gracias por agendar la activación de tu casa inteligente.', 'latepoint'); ?></h3>
  <div class="confirmation-number"><?php _e('Número de confirmación:', 'latepoint'); ?> <strong><?php echo $booking->id; ?></strong></div>
  <a href="<?php echo $booking->ical_download_link; ?>" class="ical-download-btn" target="_blank"><i class="latepoint-icon latepoint-icon-calendar"></i><span><?php _e('Add to my Calendar', 'latepoint'); ?></span></a>
  <div class="confirmation-info-w">
  	<div class="confirmation-app-info">
		  <h5><?php _e('Información de agenda', 'latepoint'); ?></h5>
		  <ul>
		  	<li><?php _e('Fecha:', 'latepoint'); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $booking->start_timestamp ); ?></strong></li>
		  	<li>
          <?php _e('Hora:', 'latepoint'); ?> 
          <strong>
            <?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->start_time); ?>
            <?php if(OsSettingsHelper::get_settings_value('show_booking_end_time') == 'on') echo ' - '. OsTimeHelper::minutes_to_hours_and_minutes($booking->end_time); ?>
          </strong>
        </li>
        <?php if(!empty($booking->location->full_address)){ ?>
          <li><?php _e('Ubicación:', 'latepoint'); ?> <strong><?php echo $booking->location->full_address; ?></strong></li>
        <?php } ?>
        <?php if(!OsSettingsHelper::is_on('steps_hide_agent_info')){ ?>
  		  	<li><?php _e('Técnico:', 'latepoint'); ?> <strong><?php echo $booking->agent->full_name; ?></strong></li>
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
          <li><?php _e('Charge Amount:', 'latepoint'); ?> <strong><?php echo $booking->formatted_full_price(); ?></strong></li>
        </ul>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php if($customer->is_guest && (OsSettingsHelper::get_settings_value('steps_hide_registration_prompt') != 'on')){ ?>
    <div class="step-confirmation-set-password">
      <h5><?php _e('Create Your Account', 'latepoint'); ?></h5>
      <div class="set-password-fields">
        <?php echo OsFormHelper::password_field('customer[password]', __('Enter Password', 'latepoint')); ?>
        <?php echo OsFormHelper::password_field('customer[password_confirmation]', __('Confirm Password', 'latepoint')); ?>
        <a href="#" class="latepoint-btn latepoint-btn-primary set-customer-password-btn" data-btn-action="<?php echo OsRouterHelper::build_route_name('customers', 'set_account_password_on_booking_completion'); ?>"><?php _e('Save', 'latepoint'); ?></a>
      </div>
      <?php echo OsFormHelper::hidden_field('account_nonse', $customer->account_nonse); ?>
    </div>
    <div class="info-box text-center">
    	<?php _e('Did you know that you can create an account to manage your reservations and schedule new appointments?', 'latepoint'); ?>
    	<div class="info-box-buttons">
    		<a href="#" class="show-set-password-fields"><?php _e('Create Account', 'latepoint'); ?></a>
    	</div>
    </div>
  <?php } ?>
</div>