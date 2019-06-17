<div class="latepoint-w">
	
	<h4 style="color: #828186;"><?php printf( __('Bienvenido %s', 'latepoint'), $customer->full_name); ?> <a class="button" style="padding: 5px !important;" href="<?php echo OsRouterHelper::build_pre_route_link(OsRouterHelper::build_route_name('customers', 'logout') ) ?>"><?php _e('Salir', 'latepoint'); ?></a></h4><?php if(!$customer->is_new_record()){ ?>
		        <div class="os-form-sub-header-actions"><?php echo __('Id de cliente:', 'latepoint').$customer->id; ?></div>
		      <?php } ?>
	<div class="latepoint-tabs-w">
		<div class="latepoint-tab-triggers customer-dashboard-tabs">
			<a href="#" data-tab-target=".tab-content-customer-bookings" class="active latepoint-tab-trigger"><?php _e('Agenda', 'latepoint'); ?></a>
			<a href="#" data-tab-target=".tab-content-customer-info-form" class="latepoint-tab-trigger"><?php _e('Mis datos', 'latepoint'); ?></a>
		</div>
		<div class="latepoint-tab-content tab-content-customer-bookings active">
			<?php if($customer->future_bookings || $customer->past_bookings){ ?>
				<?php if($customer->future_bookings){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php _e('Próximas citas', 'latepoint'); ?></h5>
					<div class="heading-extra"><?php printf( __('%d Citas'), count($customer->future_bookings)); ?></div>
				</div>
				<div class="customer-bookings-tiles">
					<?php 
					foreach($customer->future_bookings as $booking){
						$editable_booking = true;
						include('_booking_tile.php');
					} ?>
				</div>
				<?php } ?>
				<?php
				if($customer->past_bookings){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php _e('Citas pasadas', 'latepoint'); ?></h5>
					<div class="heading-extra"><?php printf( __('%d Citas'), count($customer->past_bookings)); ?></div>
				</div>
				<div class="customer-bookings-tiles">
					<?php 
						foreach($customer->past_bookings as $booking){
							$editable_booking = false;
							include('_booking_tile.php');
					} ?>
				</div>
				<?php } ?>
			<?php }else{ 
				echo '<div class="latepoint-message-info latepoint-message">'.__('Nada en la agenda por ahora', 'latepoint').'</div>';
			}
			?>
		</div>
		<div class="latepoint-tab-content tab-content-customer-info-form">
			<form action="" 
				data-os-action="<?php echo $customer->is_new_record() ? OsRouterHelper::build_route_name('customers', 'create') : OsRouterHelper::build_route_name('customers', 'update'); ?>">
			  <div class="os-row">
			    <?php echo OsFormHelper::text_field('customer[first_name]', __('Nombres', 'latepoint'), $customer->first_name, array('class' => 'required'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::text_field('customer[last_name]', __('Apellidos', 'latepoint'), $customer->last_name, array('class' => 'required'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::text_field('customer[phone]', __('Telefono', 'latepoint'), $customer->phone, array('class' => 'os-mask-phone'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::text_field('customer[email]', __('Email', 'latepoint'), $customer->email, array('class' => 'required'), array('class' => 'os-col-6')); ?>
				</div>
				<?php if($custom_fields_for_customer){ ?>
				    <div class="white-box">
				      <div class="white-box-header">
				        <div class="os-form-sub-header">
				        	<h3 style="padding-bottom: 10px; color: #828186; text-align: center"><?php _e('Datos de tu hogar inteligente', 'latepoint'); ?></h3>
						    </div>
				      </div>
				      <div class="white-box-content" style="border: #c1bfc9; border-top-style: none; border-right-style: none;border-bottom-style: none; border-left-style: none; border-style: solid; padding: 25px; margin-bottom: 10px; border-radius: 10px;">
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
				<?php echo OsFormHelper::hidden_field('customer[id]', $customer->id);
				      echo OsFormHelper::button('submit', __('Guardar cambios', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
			</form>
		</div>
		
	</div>
</div>