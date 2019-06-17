<div class="latepoint-settings-w os-form-w">
  <form action="" data-os-action="<?php echo OsRouterHelper::build_route_name('settings', 'update'); ?>">
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php _e('Payment Settings', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
        <?php echo OsFormHelper::checkbox_field('settings[enable_payments]', __('Enable Accepting Payments', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('enable_payments') == 'on'), array('data-toggle-element' => '.lp-payments-settings')); ?>
        <div class="lp-form-checkbox-contents lp-payments-settings" <?php echo (OsSettingsHelper::get_settings_value('enable_payments') == 'on') ? '' : 'style="display: none;"' ?>>
          <?php echo OsFormHelper::select_field('settings[payments_environment]', __('Environment', 'latepoint'), array(LATEPOINT_ENV_LIVE => __('Live (Production)', 'latepoint'), LATEPOINT_ENV_DEV => __('Sandbox (Development)', 'latepoint'), LATEPOINT_ENV_DEMO => __('Demo', 'latepoint')), OsSettingsHelper::get_payments_environment()); ?>
          <?php echo OsFormHelper::checkbox_field('settings[enable_payments_cc]', __('Accept Credit Cards', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('enable_payments_cc') == 'on'), array('data-toggle-element' => '.lp-payments-cc')); ?>
          <div class="lp-form-checkbox-contents lp-payments-cc" <?php echo (OsSettingsHelper::get_settings_value('enable_payments_cc') == 'on') ? '' : 'style="display: none;"' ?>>
            <?php echo OsFormHelper::checkbox_field('settings[enable_payments_stripe]', __('Enable Stripe Payments', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('enable_payments_stripe') == 'on'), array('data-toggle-element' => '.lp-stripe-settings')); ?>
            <div class="lp-form-checkbox-contents lp-stripe-settings" <?php echo (OsSettingsHelper::get_settings_value('enable_payments_stripe') == 'on') ? '' : 'style="display: none;"' ?>>
              <h3><?php _e('Stripe API Settings', 'latepoint'); ?></h3>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::password_field('settings[stripe_secret_key]', __('Secret Key', 'latepoint'), OsSettingsHelper::get_settings_value('stripe_secret_key')); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::text_field('settings[stripe_publishable_key]', __('Publishable Key', 'latepoint'), OsSettingsHelper::get_settings_value('stripe_publishable_key')); ?>
                </div>
              </div>
              <?php  
              $selected_stripe_country_code = OsSettingsHelper::get_settings_value('stripe_country_code', 'US');
              $country_currencies = OsPaymentsStripeHelper::load_country_currencies_list($selected_stripe_country_code);
              $selected_stripe_currency_iso_code = OsSettingsHelper::get_settings_value('stripe_currency_iso_code', $country_currencies['default_currency']); ?>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[stripe_country_code]', __('Country', 'latepoint'), OsPaymentsStripeHelper::load_countries_list(), $selected_stripe_country_code); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[stripe_currency_iso_code]', __('Currency Code', 'latepoint'), $country_currencies['currencies'], $selected_stripe_currency_iso_code); ?>
                </div>
              </div>
            </div>
            <?php echo OsFormHelper::checkbox_field('settings[enable_payments_braintree]', __('Enable Braintree Payments', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('enable_payments_braintree') == 'on'), array('data-toggle-element' => '.lp-braintree-settings')); ?>
            <div class="lp-form-checkbox-contents lp-braintree-settings" <?php echo (OsSettingsHelper::get_settings_value('enable_payments_braintree') == 'on') ? '' : 'style="display: none;"' ?>>
              <h3><?php _e('Braintree API Settings', 'latepoint'); ?></h3>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::password_field('settings[braintree_merchant_id]', __('Merchant ID', 'latepoint'), OsSettingsHelper::get_settings_value('braintree_merchant_id')); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::text_field('settings[braintree_tokenization_key]', __('Tokenization Key', 'latepoint'), OsSettingsHelper::get_settings_value('braintree_tokenization_key')); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::text_field('settings[braintree_publishable_key]', __('Public Key', 'latepoint'), OsSettingsHelper::get_settings_value('braintree_publishable_key')); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::password_field('settings[braintree_secret_key]', __('Private Key', 'latepoint'), OsSettingsHelper::get_settings_value('braintree_secret_key')); ?>
                </div>
              </div>
              <?php  
              $selected_braintree_country_code = OsSettingsHelper::get_settings_value('braintree_country_code', 'US');
              $country_currencies = OsPaymentsBraintreeHelper::load_country_currencies_list($selected_braintree_country_code);
              $selected_braintree_currency_iso_code = OsSettingsHelper::get_settings_value('braintree_currency_iso_code', $country_currencies['default_currency']); ?>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[braintree_country_code]', __('Country', 'latepoint'), OsPaymentsBraintreeHelper::load_countries_list(), $selected_braintree_country_code); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[braintree_currency_iso_code]', __('Currency Code', 'latepoint'), $country_currencies['currencies'], $selected_braintree_currency_iso_code); ?>
                </div>
              </div>
            </div>
          </div>
          <?php echo OsFormHelper::checkbox_field('settings[enable_payments_paypal]', __('Accept PayPal', 'latepoint'), 'on', OsSettingsHelper::is_on('enable_payments_paypal'), array('data-toggle-element' => '.lp-paypal-settings')); ?>
          <div class="lp-form-checkbox-contents lp-paypal-settings" <?php echo OsSettingsHelper::is_on('enable_payments_paypal') ? '' : 'style="display: none;"'; ?>>
            <h3><?php _e('PayPal API Settings', 'latepoint'); ?></h3>
            <div class="lp-paypal-api-settings" <?php echo OsSettingsHelper::is_on('paypal_use_braintree_api') ? 'style="display: none;"' : ''; ?>>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::text_field('settings[paypal_client_id]', __('Client ID', 'latepoint'), OsSettingsHelper::get_settings_value('paypal_client_id')); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::password_field('settings[paypal_client_secret]', __('Secret Key', 'latepoint'), OsSettingsHelper::get_settings_value('paypal_client_secret')); ?>
                </div>
              </div>
              <?php  
              $selected_paypal_country_code = OsSettingsHelper::get_settings_value('paypal_country_code', 'US');
              $country_currencies = OsPaymentsPaypalHelper::load_country_currencies_list($selected_paypal_country_code);
              $selected_paypal_currency_iso_code = OsSettingsHelper::get_settings_value('paypal_currency_iso_code', $country_currencies['default_currency']); ?>
              <div class="os-row">
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[paypal_country_code]', __('Country', 'latepoint'), OsPaymentsPaypalHelper::load_countries_list(), $selected_paypal_country_code); ?>
                </div>
                <div class="os-col-6">
                  <?php echo OsFormHelper::select_field('settings[paypal_currency_iso_code]', __('Currency Code', 'latepoint'), $country_currencies['currencies'], $selected_paypal_currency_iso_code); ?>
                </div>
              </div>
            </div>
            <?php echo OsFormHelper::checkbox_field('settings[paypal_use_braintree_api]', __('Use Braintree Payments for PayPal Processing', 'latepoint'), 'on', OsSettingsHelper::is_on('paypal_use_braintree_api'), array('data-toggle-element' => '.lp-paypal-api-settings', 'data-inverse-toggle' => 'yes')); ?>
          </div>

          <?php echo OsFormHelper::checkbox_field('settings[enable_payments_local]', __('Allow Paying Locally', 'latepoint'), 'on', (OsSettingsHelper::get_settings_value('enable_payments_local') == 'on')); ?>
        </div>
      </div>
    </div>
    <?php echo OsFormHelper::button('submit', __('Save Settings', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
  </form>
</div>