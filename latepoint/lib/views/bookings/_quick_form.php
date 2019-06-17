<div class="os-form-w quick-booking-form-w">
  <form action="" data-os-show-errors-as-notification="yes" data-os-action="<?php echo ($booking->is_new_record()) ? OsRouterHelper::build_route_name('bookings', 'create') : OsRouterHelper::build_route_name('bookings', 'update'); ?>" class="booking-quick-edit-form">
    <div class="os-form-header">
      <?php if($booking->is_new_record()){ ?>
        <h2><?php _e('New Appointment', 'latepoint'); ?></h2>
      <?php }else{ ?>
        <h2><?php _e('Edit Appointment #', 'latepoint'); ?><?php echo $booking->id; ?></h2>
      <?php } ?>
      <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
    </div>
    <div class="os-form-content">
      <div class="os-form-group os-form-group-transparent">
        <div class="os-services-select-field-w">
          <div class="services-options-list">
            <?php 
            foreach($services as $service){ ?>
              <div class="service-option <?php if($service->id == $booking->service_id) { echo 'selected'; } ?>" 
                data-id="<?php echo $service->id; ?>" 
                data-buffer-before="<?php echo $service->buffer_before; ?>" 
                data-buffer-after="<?php echo $service->buffer_after; ?>" 
                data-duration="<?php echo $service->duration; ?>">
                  <div class="service-color" style="background-color: <?php echo $service->bg_color; ?>"></div>
                  <span><?php echo $service->name ?></span>
              </div><?php 
            } ?>
          </div>
          <?php if($booking->service_id){ ?>
            <div class="service-option-selected" 
              data-id="<?php echo $booking->service->id; ?>" 
              data-buffer-before="<?php echo $booking->service->buffer_before; ?>" 
              data-buffer-after="<?php echo $booking->service->buffer_after; ?>" 
              data-duration="<?php echo $booking->service->duration; ?>">
                <div class="service-color" style="background-color: <?php echo $booking->service->bg_color; ?>"></div>
                <span><?php echo $booking->service->name ?></span>
            </div>
          <?php }else{ ?>
            <div class="service-option-selected">
              <div class="service-color"></div>
              <span><?php _e('Select Service','latepoint'); ?></span>
            </div>
          <?php } ?>
        </div>
      </div>
      <?php if(OsLocationHelper::count_locations() > 1){ ?>
        <div class="os-row">
          <div class="os-col-12">
            <?php echo OsFormHelper::select_field('booking[location_id]', __('Select Location', 'latepoint'), OsLocationHelper::get_locations_list(), $booking->location_id, ['class' => 'location_id_holder']); ?>
          </div>
        </div>
        <?php
      }else{
        echo OsFormHelper::hidden_field('booking[location_id]', $booking->location_id, ['class' => 'location_id_holder']);
      } ?>
      <div class="os-row">
        <div class="os-col-6">
          <div class="agent-info-w <?php echo ($booking->agent_id) ? 'selected': 'selecting'; ?>">
            <div class="agents-selector-w">
              <div class="os-form-group os-form-select-group os-form-group-transparent">
                <label for=""><?php _e('Agent', 'latepoint'); ?></label>
                <select name="booking[agent_id]" class="os-form-control agent-selector">
                  <?php foreach($agents as $agent){ ?>
                    <option value="<?php echo $agent->id; ?>" <?php if($agent->id == $booking->agent_id) echo 'selected'; ?>><?php echo join(' ', array($agent->first_name, $agent->last_name)); ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="os-col-6">
          <?php echo OsFormHelper::select_field('booking[status]', __('Status', 'latepoint'), OsBookingHelper::get_statuses_list(), $booking->status, array('placeholder' => 'Set Status')); ?>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[start_date]', __('Start Date', 'latepoint'), $booking->start_date, array('class' => 'trigger-quick-availability', 'data-route' => OsRouterHelper::build_route_name('bookings', 'quick_availability'))); ?>
        </div>
        <div class="os-col-6">
          <a href="#" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'quick_availability'); ?>" class="latepoint-btn latepoint-btn-white open-quick-availability-btn trigger-quick-availability"><i class="latepoint-icon latepoint-icon-calendar"></i><span><?php _e('Show Calendar', 'latepoint'); ?></span></a>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-12">
          <div class="ws-period os-period-transparent">
            <div class="quick-start-time-w">
              <?php echo OsFormHelper::time_field('booking[start_time]', __('Start Time', 'latepoint'), $booking->start_time, true); ?>
            </div>
            <div class="quick-end-time-w">
              <?php echo OsFormHelper::time_field('booking[end_time]', __('End Time', 'latepoint'), $booking->end_time, true); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[buffer_before]', __('Buffer Before', 'latepoint'), $booking->buffer_before); ?>
        </div>
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[buffer_after]', __('Buffer After', 'latepoint'), $booking->buffer_after); ?>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-12">
        </div>
      </div>



      <div class="customer-info-w selected">
        <div class="os-form-sub-header">
          <h3><?php _e('Customer', 'latepoint'); ?></h3>
          <div class="os-form-sub-header-actions">
            <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-white customer-info-create-btn" 
              data-os-output-target=".customer-quick-edit-form-w" 
              data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'customer_quick_edit_form'); ?>">
              <i class="latepoint-icon latepoint-icon-plus"></i><span><?php _e('New', 'latepoint'); ?></span>
            </a>
            <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-white customer-info-load-btn">
              <i class="latepoint-icon latepoint-icon-search"></i><span><?php _e('Find', 'latepoint'); ?></span>
            </a>
          </div>
        </div>
        <div class="customers-selector-w">
          <div class="customers-selector-search-w">
            <i class="latepoint-icon latepoint-icon-search"></i>
            <input type="text" class="customers-selector-search-input" placeholder="<?php _e('Start typing to search...', 'latepoint'); ?>">
            <span class="customers-selector-cancel">
              <i class="latepoint-icon latepoint-icon-x"></i>
              <span><?php _e('cancel', 'latepoint'); ?></span>
            </span>
          </div>
          <?php if($customers){ ?>
            <div class="customers-options-list">
              <?php foreach($customers as $customer){ ?>
                <div class="customer-option" data-os-params="<?php echo OsUtilHelper::build_os_params(['customer_id' => $customer->id]); ?>"
                    data-os-after-call="latepoint_quick_booking_customer_selected"
                    data-os-output-target=".customer-quick-edit-form-w" 
                    data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'customer_quick_edit_form'); ?>">
                  <div class="customer-option-avatar" style="background-image: url(<?php echo OsCustomerHelper::get_avatar_url($customer); ?>)"></div>
                  <div class="customer-option-info">
                    <h4 class="customer-option-info-name"><span><?php echo $customer->full_name; ?></span></h4>
                    <ul>
                      <li>
                        <?php _e('Email: ','latepoint'); ?>
                        <strong><?php echo $customer->email; ?></strong>
                      </li>
                      <li>
                        <?php _e('Phone: ','latepoint'); ?>
                        <strong><?php echo $customer->formatted_phone; ?></strong>
                      </li>
                    </ul>
                  </div>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
        <div class="customer-quick-edit-form-w">
          <?php require('customer_quick_edit_form.php'); ?>
        </div>
      </div>
    </div>
    <div class="os-form-buttons">
      <?php 
      echo '<button type="submit" class="latepoint-btn">'.__('Save Changes', 'latepoint').'</button>';
      echo '<a href="#" class="latepoint-side-panel-close-trigger latepoint-btn latepoint-btn-secondary">'.__('Cancel', 'latepoint').'</a>';
      echo OsFormHelper::hidden_field('booking[id]', $booking->id);
      echo OsFormHelper::hidden_field('booking[service_id]', $booking->service_id);
      echo OsFormHelper::hidden_field('nonse', OsUtilHelper::create_nonce( $booking->id ));
      ?>
    </div>
  </form>
</div>