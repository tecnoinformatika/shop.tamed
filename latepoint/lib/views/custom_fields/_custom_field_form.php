<form data-os-custom-field-id="<?php echo $custom_field['id']; ?>" 
			data-os-action="<?php echo OsRouterHelper::build_route_name('custom_fields', 'save'); ?>" 
			data-os-after-call="latepoint_custom_field_saved" 
			class="os-custom-field-form os-custom-field-type-<?php echo $custom_field['type']; ?> <?php if(empty($custom_field['label'])) echo 'os-is-editing'; ?>">
	<div class="os-custom-field-form-i">
		<div class="os-custom-field-form-info <?php if($custom_field['required'] == 'on') echo 'os-custom-field-required'; ?>">
			<div class="os-custom-field-drag"></div>
			<div class="os-custom-field-name"><?php echo !empty($custom_field['label']) ? $custom_field['label'] : __('New Field', 'latepoint'); ?></div>
			<div class="os-custom-field-type"><?php echo $custom_field['type']; ?></div>
			<div class="os-custom-field-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></div>
		</div>
		<div class="os-custom-field-form-params">
			<div class="os-row">
				<div class="os-col-6">
					<?php echo OsFormHelper::text_field('custom_fields['.$custom_field['id'].'][label]', __('Field Label', 'latepoint'), $custom_field['label'], ['class' => 'os-custom-field-name-input']); ?>
				</div>
				<div class="os-col-6">
					<?php echo OsFormHelper::text_field('custom_fields['.$custom_field['id'].'][placeholder]', __('Placeholder', 'latepoint'), $custom_field['placeholder']); ?>
				</div>
				<div class="os-col-6">
				  <?php echo OsFormHelper::select_field('custom_fields['.$custom_field['id'].'][type]', __('Field Type', 'latepoint'), array( 'text' => __('Text Field', 'latepoint'), 
				  																																																														'select' => __('Select Box', 'latepoint'),
				  																																																														'textarea' => __('Text Area Field', 'latepoint'),
				  																																																														'checkbox' => __('Checkbox', 'latepoint')), $custom_field['type'], ['class' => 'os-custom-field-type-select']); ?>
				</div>
				<div class="os-col-6">
				  <?php echo OsFormHelper::select_field('custom_fields['.$custom_field['id'].'][width]', __('Field Width', 'latepoint'), array( 'os-col-12' => __('Full Width', 'latepoint'), 
													  																																																						'os-col-6' => __('Half Width', 'latepoint')), $custom_field['width']); ?>
				</div>
				<div class="os-col-12 os-custom-field-select-values" <?php if($custom_field['type'] != 'select') echo 'style="display: none;"'; ?>>
					<?php echo OsFormHelper::textarea_field('custom_fields['.$custom_field['id'].'][options]', __('Choices for Select. Enter each choice on a new line.', 'latepoint'), $custom_field['options'], array('rows' => 5)); ?>
				</div>
				<div class="os-col-12">
				  <?php echo OsFormHelper::checkbox_field('custom_fields['.$custom_field['id'].'][required]', __('Required?', 'latepoint'), 'on', ($custom_field['required'] == 'on')); ?>
				</div>
			</div>
		  <button type="submit" class="os-custom-field-save-btn latepoint-btn latepoint-btn-outline"><span><?php _e('Save', 'latepoint'); ?></span></button>
		</div>
	</div>
	<?php echo OsFormHelper::hidden_field('custom_fields['.$custom_field['id'].'][id]', $custom_field['id'], ['class' => 'os-custom-field-id']); ?>
	<a href="#" data-os-prompt="<?php _e('Are you sure you want to remove this field?', 'latepoint'); ?>"  data-os-after-call="latepoint_custom_field_removed" data-os-pass-this="yes" data-os-action="<?php echo OsRouterHelper::build_route_name('custom_fields', 'delete'); ?>" data-os-params="<?php echo OsUtilHelper::build_os_params(['id' => $custom_field['id']]) ?>" class="os-remove-custom-field"><i class="latepoint-icon latepoint-icon-cross"></i></a>
</form>