<div class="os-form-sub-header"><h3><?php _e('Step Editing', 'latepoint'); ?></h3></div>
<div class="steps-ordering-w" data-step-order-update-route="<?php echo OsRouterHelper::build_route_name('settings', 'udpate_order_of_steps'); ?>">
	<?php
	foreach($steps as $step){ ?>
		<div class="step-w" data-step-name="<?php echo $step->name; ?>" data-step-order-number="<?php echo $step->order_number; ?>">
			<div class="step-head">
				<div class="step-drag"></div>
				<div class="step-name"><?php echo $step->title; ?></div>
				<?php if($step->name == 'locations' && (OsLocationHelper::count_locations() <= 1)){ ?>
					<a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('locations', 'index') ); ?>" class="step-message"><?php _e('Since you only have one location, this step will be skipped', 'latepoint'); ?></a>
				<?php } ?>
				<?php if($step->name == 'payment' && !OsSettingsHelper::is_accepting_payments()){ ?>
					<a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('settings', 'payments') ); ?>" class="step-message"><?php _e('Payment processing is disabled. Click to setup.', 'latepoint'); ?></a>
				<?php } ?>
				<button class="step-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></button>
			</div>
			<div class="step-body">
				<div class="os-form-w">
				  <form data-os-action="<?php echo OsRouterHelper::build_route_name('settings', 'update_step'); ?>" action="">
				  	<div class="os-row">
				  		<div class="os-col-6">
								<?php echo OsFormHelper::text_field('step[title]', __('Step Title', 'latepoint'), $step->title, ['add_string_to_id' => $step->name]); ?>
				  		</div>
				  		<div class="os-col-6">
								<?php echo OsFormHelper::text_field('step[sub_title]', __('Step Sub Title', 'latepoint'), $step->sub_title, ['add_string_to_id' => $step->name]); ?>
				  		</div>
				  	</div>
		        <?php echo OsFormHelper::textarea_field('step[description]', __('Short Description', 'latepoint'), $step->description, ['add_string_to_id' => $step->name]); ?>
				  	<div class="os-row">
				  		<div class="os-col-12">
				  			<?php echo OsFormHelper::checkbox_field('step[use_custom_image]', __('Use Custom Step Image', 'latepoint'), 'on', $step->is_using_custom_image(), ['data-toggle-element' => '.custom-step-image-w-'.$step->name, 'add_string_to_id' => $step->name], ['class' => 'toggle-element-outside']); ?>
				  		</div>
				  	</div>
				  	<div class="os-row custom-step-image-w-<?php echo $step->name; ?>" style="<?php echo ($step->is_using_custom_image()) ? '' : 'display: none;'; ?>">
				  		<div class="os-col-12">
				  			<div class="os-form-group">
					        <?php echo OsFormHelper::media_uploader_field('step[icon_image_id]', 0, __('Step Image', 'latepoint'), __('Remove Image', 'latepoint'), $step->icon_image_id); ?>
					      </div>
					  	</div>
				  	</div>
		        <?php echo OsFormHelper::hidden_field('step[name]', $step->name, ['add_string_to_id' => $step->name]); ?>
		        <?php echo OsFormHelper::hidden_field('step[order_number]', $step->order_number, ['add_string_to_id' => $step->name]); ?>
		        <div class="os-form-buttons">
			        <?php echo OsFormHelper::button('submit', __('Save Step', 'latepoint'), 'submit', ['class' => 'latepoint-btn', 'add_string_to_id' => $step->name]);  ?>
			        <a href="#" class="latepoint-btn latepoint-btn-secondary step-edit-cancel-btn"><?php _e('Cancel', 'latepoint'); ?></a>
			      </div>
					</form>
				</div>
			</div>
		</div><?php
	}
	?>
</div>
<div class="os-form-w">
  <form action="" data-os-action="<?php echo OsRouterHelper::build_route_name('settings', 'update'); ?>">
		<div class="os-form-sub-header"><h3><?php _e('Other Step Settings', 'latepoint'); ?></h3></div>
		<?php echo OsFormHelper::checkbox_field('settings[steps_show_service_categories]', __('Show Service Categories', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('steps_show_service_categories') == 'on')); ?>
		<?php echo OsFormHelper::checkbox_field('settings[steps_show_agent_bio]', __('Show Agent Bio Popup', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('steps_show_agent_bio') == 'on')); ?>
		<?php echo OsFormHelper::checkbox_field('settings[steps_hide_registration_prompt]', __('Hide "Create Account" Prompt on Confirmation Step', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('steps_hide_registration_prompt') == 'on')); ?>
		<?php echo OsFormHelper::checkbox_field('settings[steps_hide_login_register_tabs]', __('Remove Login/Register Tabs on Contact Info Step', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('steps_hide_login_register_tabs') == 'on')); ?>
		<?php echo OsFormHelper::checkbox_field('settings[steps_hide_agent_info]', __('Do not show Agent Name on Summary and Confirmation steps', 'latepoint'), 'on', OsSettingsHelper::is_on('steps_hide_agent_info')); ?>
    <?php echo OsFormHelper::checkbox_field('settings[allow_any_agent]', __('Add "Any Agent" option to agent selection', 'latepoint'), 'on', OsSettingsHelper::is_on('allow_any_agent'), array('data-toggle-element' => '.lp-any-agent-settings')); ?>
    <div class="lp-form-checkbox-contents lp-any-agent-settings" <?php echo (OsSettingsHelper::is_on('allow_any_agent')) ? '' : 'style="display: none;"' ?>>
      <h3><?php _e('Any Agent Settings', 'latepoint'); ?></h3>
      <?php echo OsFormHelper::select_field('settings[any_agent_order]', __('If "Any Agent" Selected Assign to', 'latepoint'), [ 
        LATEPOINT_ANY_AGENT_ORDER_RANDOM => __('Random', 'latepoint'),
        LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH => __('Most expensive agent', 'latepoint'),
        LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW => __('Least expensive agent', 'latepoint'),
        LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH => __('Agent with most bookings on that day', 'latepoint'),
        LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW => __('Agent with least bookings on that day', 'latepoint') ], OsSettingsHelper::get_any_agent_order()); ?>
    </div>
		<?php echo OsFormHelper::wp_editor_field('settings[steps_support_text]', 'settings_steps_support_text', __('Extra information on lightbox', 'latepoint'), OsSettingsHelper::get_steps_support_text(), array('editor_height' => 100)); ?>
		<?php echo OsFormHelper::button('submit', __('Save Settings', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
	</form>
</div>
