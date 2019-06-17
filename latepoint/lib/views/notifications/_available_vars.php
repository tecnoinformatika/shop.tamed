<div class="available-vars-w">
  <div class="latepoint-message latepoint-message-subtle">
    <div><?php _e('You can use these variables in your email and sms notifications. Just click on the variable with {} brackets and it will automatically copy to your buffer and you can simply paste it where you want to use it. It will be converted into a value for the agent/service or appointment.', 'latepoint'); ?></div>
  </div>
  <div class="available-vars-i">
    <div class="available-vars-block">
      <h4><?php _e('Appointment', 'latepoint'); ?></h4>
      <ul>
        <li><span class="var-label"><?php _e('Service Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{service_name}</span></li>
        <li><span class="var-label"><?php _e('Start Date:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{start_date}</span></li>
        <li><span class="var-label"><?php _e('Start Time:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{start_time}</span></li>
        <li><span class="var-label"><?php _e('End Time:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{end_time}</span></li>
        <li><span class="var-label"><?php _e('Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{booking_status}</span></li>
        <li><span class="var-label"><?php _e('Previous Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{booking_old_status}</span></li>
      </ul>
    </div>
    <div class="available-vars-block">
      <h4><?php _e('Customer', 'latepoint'); ?></h4>
      <ul>
        <li><span class="var-label"><?php _e('Full Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{customer_full_name}</span></li>
        <li><span class="var-label"><?php _e('Email Address:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{customer_email}</span></li>
        <li><span class="var-label"><?php _e('Phone:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{customer_phone}</span></li>
        <li><span class="var-label"><?php _e('Comments:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{customer_notes}</span></li>
      </ul>
    </div>
    <div class="available-vars-block">
      <h4><?php _e('Agent', 'latepoint'); ?></h4>
      <ul>
        <li><span class="var-label"><?php _e('Full Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{agent_full_name}</span></li>
        <li><span class="var-label"><?php _e('Email:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{agent_email}</span></li>
        <li><span class="var-label"><?php _e('Phone:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{agent_phone}</span></li>
      </ul>
    </div>
    <div class="available-vars-block">
      <h4><?php _e('Location', 'latepoint'); ?></h4>
      <ul>
        <li><span class="var-label"><?php _e('Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{location_name}</span></li>
        <li><span class="var-label"><?php _e('Full Address:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{location_full_address}</span></li>
      </ul>
    </div>
    <div class="available-vars-block">
      <h4><?php _e('Other', 'latepoint'); ?></h4>
      <ul>
        <li><span class="var-label"><?php _e('Password Reset Token:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{token}</span></li>
      </ul>
    </div>
    <?php if(OsCustomFieldsHelper::get_custom_fields_arr('customer')){ ?>
      <div class="available-vars-block">
        <h4><?php _e('Custom Fields', 'latepoint'); ?></h4>
        <ul>
          <?php foreach(OsCustomFieldsHelper::get_custom_fields_arr('customer') as $custom_field){ ?>
            <li><span class="var-label"><?php echo $custom_field['label']; ?></span> <span class="var-code os-click-to-copy">{<?php echo $custom_field['id']; ?>}</span></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>
  </div>
</div>