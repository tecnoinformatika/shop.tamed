<div class="os-form-w">
	<form action="" 
				data-os-success-action="redirect" 
				data-os-redirect-to="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('customers', 'index')); ?>" 
				data-os-action="<?php echo $customer->is_new_record() ? OsRouterHelper::build_route_name('customers', 'create') : OsRouterHelper::build_route_name('customers', 'update'); ?>">
	
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header">
        	<h3><?php _e('Información general', 'latepoint'); ?></h3>
	        <?php if(!$customer->is_new_record()){ ?>
		        <div class="os-form-sub-header-actions"><?php echo __('Id del cliente:', 'latepoint').$customer->id; ?></div>
		      <?php } ?>
		    </div>
      </div>
      <div class="white-box-content">
				<div class="os-row">
				  <div class="os-col-lg-6">
				    <?php echo OsFormHelper::media_uploader_field('customer[avatar_image_id]', 0, __('Subir imagen', 'latepoint'), __('Remove Avatar', 'latepoint'), $customer->avatar_image_id); ?>
				  </div>
				</div>
				<div class="os-row">
					<div class="os-col-6">
				    <?php echo OsFormHelper::text_field('customer[first_name]', __('Nombres', 'latepoint'), $customer->first_name); ?>
					</div>
					<div class="os-col-6">
				    <?php echo OsFormHelper::text_field('customer[last_name]', __('Apellidos', 'latepoint'), $customer->last_name); ?>
					</div>
				</div>
				<div class="os-row">
					<div class="os-col-lg-6">
				    <?php echo OsFormHelper::text_field('customer[email]', __('Email', 'latepoint'), $customer->email); ?>
					</div>
					<div class="os-col-lg-6">
				    <?php echo OsFormHelper::text_field('customer[phone]', __('Telefono', 'latepoint'), $customer->phone, array('class' => 'os-mask-phone')); ?>
					</div>
				</div>
				<div class="os-row">
					<div class="os-col-lg-6">
				    <?php echo OsFormHelper::password_field('customer[password]', __('Contraseña', 'latepoint')); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if($custom_fields_for_customer){ ?>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header">
        	<h3><?php _e('Datos de vivienda', 'latepoint'); ?></h3>
		    </div>
      </div>
      <div class="white-box-content">
			  <div class="os-row">
			    <?php foreach($custom_fields_for_customer as $custom_field){
			      $required_class = ($custom_field['required'] == 'on') ? 'required' : '';
			      switch ($custom_field['type']) {
			        case 'text':
			          echo OsFormHelper::text_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
			          break;
			        case 'textarea':
			          echo OsFormHelper::textarea_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
			          break;
			        case 'select':
			          echo OsFormHelper::select_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], OsFormHelper::generate_select_options_from_custom_field($custom_field['options']), $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
			          break;
			        case 'checkbox':
			          echo OsFormHelper::checkbox_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], 'on', ($customer->get_meta_by_key($custom_field['id'], 'off') == 'on') , ['class' => $required_class], array('class' => $custom_field['width']));
			          break;
			      }
			    } ?>
			  </div>
      </div>
    </div>
	  <?php } ?>
    <div class="os-form-buttons os-flex">
    <?php 
      if($customer->is_new_record()){
        echo OsFormHelper::button('submit', __('Guardar cliente', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); 
      }else{
        echo OsFormHelper::hidden_field('customer[id]', $customer->id);
        echo OsFormHelper::button('submit', __('Guardar cambios', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); 
        echo '<a href="#" class="latepoint-btn latepoint-btn-danger remove-customer-btn" style="margin-left: auto;" 
                data-os-prompt="'.__('Are you sure you want to delete this customer? It will remove all appointments and transactions associated with this customer.', 'latepoint').'" 
                data-os-redirect-to="'.OsRouterHelper::build_link(OsRouterHelper::build_route_name('customers', 'index')).'" 
                data-os-params="'. OsUtilHelper::build_os_params(['id' => $customer->id]). '" 
                data-os-success-action="redirect" 
                data-os-action="'.OsRouterHelper::build_route_name('customers', 'destroy').'">'.__('Eliminar cliente', 'latepoint').'</a>';
      }
		?>
		</div>
  </form>
</div>
<?php if(!$customer->is_new_record()){ ?>
	<div class="customer-appointments">
		<div class="os-form-sub-header"><h3><?php _e('Reservas', 'latepoint'); ?></h3></div>
		<?php  
		if($customer->bookings){
			foreach($customer->bookings as $booking){
				$hide_customer_info = true;
				include(LATEPOINT_VIEWS_ABSPATH.'dashboard/_booking_info_box_small.php');
			}
		}else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-book"></i></div>
		    <h2><?php _e('El cliente no tiene reservas de momento', 'latepoint'); ?></h2>
		    <a <?php echo OsBookingHelper::quick_booking_btn_html(false, array('customer_id'=> $customer->id)); ?> href="#" class="latepoint-btn">
		      <i class="latepoint-icon latepoint-icon-plus-square"></i>
		      <span><?php _e('Crear reserva', 'latepoint'); ?></span>
		    </a>
		  </div>
			<?php
		} ?>
	</div>
<?php } ?>