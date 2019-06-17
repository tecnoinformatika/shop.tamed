<div class="latepoint-booking-form-element <?php if(!$show_next_btn) echo 'hidden-buttons'; ?> latepoint-color-<?php echo OsSettingsHelper::get_booking_form_color_scheme(); ?>">
  <div class="latepoint-side-panel">
    <div class="latepoint-progress">
      <ul>
        <?php foreach($steps_models as $index => $step_model){ ?>
          <li data-step-name="<?php echo $step_model->name; ?>" class="<?php if($active_step_model->name == $step_model->name) echo ' active '; ?>">
            <a href="#"><?php echo '<span> '. esc_html($step_model->title) . '</span>'; ?></a>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="latepoint-step-desc-w">
      <div class="latepoint-step-desc">
        <?php if($active_step_model->icon_image_url){ ?>
          <div class="latepoint-desc-media img-w" style="background-image: url(<?php echo $active_step_model->icon_image_url; ?>)"></div>
        <?php } ?>
        <h3 class="latepoint-desc-title"><?php echo $active_step_model->title; ?></h3>
        <div class="latepoint-desc-content"><?php echo stripcslashes($active_step_model->description); ?></div>
      </div>
      <?php foreach($steps_models as $index => $step_model){ ?>
        <div data-step-name="<?php echo $step_model->name; ?>" class="latepoint-step-desc-library <?php if($active_step_model->name == $step_model->name) echo ' active '; ?>">
          <?php if($step_model->icon_image_url){ ?>
            <div class="latepoint-desc-media img-w" style="background-image: url(<?php echo $step_model->icon_image_url; ?>)"></div>
          <?php } ?>
          <h3 class="latepoint-desc-title"><?php echo $step_model->title; ?></h3>
          <div class="latepoint-desc-content"><?php echo $step_model->description; ?></div>
        </div>
      <?php } ?>
    </div>
    <div class="latepoint-questions"><?php echo OsSettingsHelper::get_steps_support_text(); ?></div>
  </div>
  <div class="latepoint-form-w">
    <form class="latepoint-form" 
      data-selected-label="<?php _e('Seleccionado', 'latepoint'); ?>" 
      data-route-name="<?php echo OsRouterHelper::build_route_name('bookings', 'get_step'); ?>" 
      action="#">
      <div class="latepoint-heading-w">
        <h3 class="os-heading-text"><?php echo $active_step_model->sub_title; ?></h3>
        <?php foreach($steps_models as $index => $step_model){ ?>
          <div data-step-name="<?php echo $step_model->name; ?>" class="os-heading-text-library <?php if($active_step_model->name == $step_model->name) echo ' active '; ?>"><?php echo $step_model->sub_title; ?></div>
        <?php } ?>
        <a href="#" class="latepoint-lightbox-close"><i class="latepoint-icon-common-01"></i></a>
      </div>
      <div class="latepoint-body" style="max-height: 100% !important;">
        <?php include($active_step_partial_path); ?>
      </div>
      <div class="latepoint-footer">
        <a href="#" class="latepoint-btn latepoint-btn-white latepoint-prev-btn disabled"><i class="latepoint-icon-arrow-2-left"></i> <span><?php _e('Atras', 'latepoint'); ?></span></a>
        <a href="#" class="latepoint-btn latepoint-btn-primary latepoint-next-btn <?php echo ($show_next_btn) ? '' : 'disabled'; ?>" data-pre-last-step-label="<?php _e('Enviar solicitud', 'latepoint'); ?>" data-label="<?php _e('Siguiente paso', 'latepoint'); ?>"><span><?php _e('Siguiente paso', 'latepoint'); ?></span> <i class="latepoint-icon-arrow-2-right"></i></a>
      </div>
    </form>
  </div>
  <div class="latepoint-summary-w">
    <h3 class="summary-header"><span><?php _e('Resumen', 'latepoint'); ?></span><div class="os-lines"></div></h3>
    <div class="os-summary-lines">
      <?php 
      $selectable_values = ['service' => ['label' => __('Servicio', 'latepoint'), 'value' => $booking->service->name ],
                            'location' => ['label' => __('Ubicacion', 'latepoint'), 'value' => $booking->location->name ],
                            'agent' => ['label' => __('Técnico', 'latepoint'), 'value' => $booking->agent->full_name ],
                            'date' => ['label' => __('Fecha', 'latepoint'), 'value' => '' ],
                            'time' => ['label' => __('Hora', 'latepoint'), 'value' => '' ],
                            'customer' => ['label' => __('Cliente', 'latepoint'), 'value' => $booking->customer->full_name]];
      if(OsLocationHelper::count_locations() <= 1) unset($selectable_values['location']);
      if((OsAgentHelper::count_agents() <= 1) || OsSettingsHelper::is_on('steps_hide_agent_info')) unset($selectable_values['agent']);
      foreach($selectable_values as $key => $selectable_value){ ?>
        <div class="os-summary-line <?php if(!empty($selectable_value['value'])) echo 'os-has-value'; ?>">
          <div class="os-summary-label">
            <?php echo $selectable_value['label']; ?>
          </div>
          <div class="os-summary-value os-summary-value-<?php echo $key; ?>">
            <?php echo $selectable_value['value']; ?>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</div>