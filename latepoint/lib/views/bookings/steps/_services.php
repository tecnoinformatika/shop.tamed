<div class="step-services-w latepoint-step-content">
  <?php 
  if(OsSettingsHelper::steps_show_service_categories()){
    OsBookingHelper::generate_services_and_categories_list(false, $show_service_categories_arr, $show_services_arr, $preselected_category);
  }else{
    OsBookingHelper::generate_services_list($services);
  } ?>
</div>
<?php if(!isset($no_params)) include '_booking_params.php'; ?>