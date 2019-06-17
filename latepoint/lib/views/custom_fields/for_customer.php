<div class="os-form-sub-header"><h3><?php _e('Custom Fields for Customers', 'latepoint'); ?></h3></div>
<div class="os-custom-fields-w os-custom-fields-ordering-w" data-order-update-route="<?php echo OsRouterHelper::build_route_name('custom_fields', 'update_order'); ?>" data-fields-for="customer">
	<?php foreach($custom_fields_for_customers as $custom_field){ ?>
		<?php include('_custom_field_form.php'); ?>
	<?php } ?>
</div>
<div class="add-custom-field-box add-custom-field-trigger" data-os-action="<?php echo OsRouterHelper::build_route_name('custom_fields', 'new_form'); ?>" data-os-output-target-do="append" data-os-output-target=".os-custom-fields-w">
	<div class="add-custom-field-graphic-w">
		<div class="add-custom-field-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
	</div>
	<div class="add-custom-field-label"><?php _e('Add Custom Field', 'latepoint'); ?></div>
</div>