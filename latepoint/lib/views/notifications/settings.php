<div class="latepoint-settings-w os-form-w">
  <form action="" data-os-action="<?php echo OsRouterHelper::build_route_name('settings', 'update'); ?>">
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php _e('Email Settings', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
        <?php echo OsFormHelper::checkbox_field('settings[notifications_email]', __('Enable Email Notifications', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('notifications_email') == 'on')); ?>
      </div>
    </div>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php _e('SMS Settings', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
        <?php echo OsFormHelper::checkbox_field('settings[notifications_sms]', __('Enable SMS Notifications', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('notifications_sms') == 'on'), array('data-toggle-element' => '.lp-twilio-credentials')); ?>
        <div class="lp-form-checkbox-contents lp-twilio-credentials" <?php echo (OsSettingsHelper::get_settings_value('notifications_sms') == 'on') ? '' : 'style="display: none;"' ?>>
          <h3><?php _e('Twilio API Credentials', 'latepoint'); ?></h3>
          <?php echo OsFormHelper::text_field('settings[notifications_sms_twilio_phone]', __('Phone Number', 'latepoint'), OsSettingsHelper::get_settings_value('notifications_sms_twilio_phone')); ?>
          <?php echo OsFormHelper::text_field('settings[notifications_sms_twilio_account_sid]', __('Account SID', 'latepoint'), OsSettingsHelper::get_settings_value('notifications_sms_twilio_account_sid')); ?>
          <?php echo OsFormHelper::password_field('settings[notifications_sms_twilio_auth_token]', __('Auth Token', 'latepoint'), OsSettingsHelper::get_settings_value('notifications_sms_twilio_auth_token')); ?>
        </div>
      </div>
    </div>
    <?php echo OsFormHelper::button('submit', __('Save Settings', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
  </form>
</div>