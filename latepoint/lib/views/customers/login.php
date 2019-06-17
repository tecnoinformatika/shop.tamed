<div class="latepoint-w">
	<div class="os-form-w latepoint-login-form-w">
		<h4><?php _e('Accede a tu cuenta', 'latepoint'); ?></h4>
		<form action="" data-os-action="<?php echo OsRouterHelper::build_route_name('customers', 'do_login'); ?>" data-os-success-action="redirect">
			<?php echo OsFormHelper::text_field('customer_login[email]', __('Email', 'latepoint')); ?>
			<?php echo OsFormHelper::password_field('customer_login[password]', __('Contraseña', 'latepoint')); ?>
			<div class="os-form-buttons os-flex">
				<?php echo OsFormHelper::button('submit', __('Inciar sesión', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
				<a href="#" class="latepoint-btn latepoint-btn-primary latepoint-btn-link" data-os-action="<?php echo OsRouterHelper::build_route_name('customers', 'request_password_reset_token'); ?>" data-os-output-target=".latepoint-login-form-w"><?php _e('Olvidaste tu contraseña?', 'latepoint'); ?></a>
			</div>
		</form>
	</div>
</div>