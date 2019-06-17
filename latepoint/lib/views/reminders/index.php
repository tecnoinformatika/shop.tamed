<div class="os-reminders-w">
  <?php foreach($reminders as $reminder){ ?>
    <?php include('new_form.php'); ?>
  <?php } ?>
</div>
<div class="add-reminder-box" data-os-action="<?php echo OsRouterHelper::build_route_name('reminders', 'new_form'); ?>" data-os-output-target-do="append" data-os-output-target=".os-reminders-w">
  <div class="add-custom-field-graphic-w">
    <div class="add-custom-field-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
  </div>
  <div class="add-custom-field-label"><?php _e('Add Reminder', 'latepoint'); ?></div>
</div>
<?php include(LATEPOINT_VIEWS_ABSPATH. 'notifications/_available_vars.php'); ?>
<?php wp_enqueue_editor(); ?>