<?php 

class OsServiceHelper {

	public static function get_default_colors(){
		return array('#1449ff', '#8833F9', '#49C47F', '#E9A019', '#F93375', '#19CED6', '#252a3e', '#8d87a5', '#b9b784');
	}

  public static function get_services_list(){
    $services = new OsServiceModel();
    $services = $services->get_results_as_models();
    $services_list = [];
    foreach($services as $service){
      $services_list[] = ['value' => $service->id, 'label' => $service->name];
    }
    return $services_list;
  }

	public static function generate_service_categories_list($parent_id = false){
    $service_categories = new OsServiceCategoryModel();
    $args = array();
    $args['parent_id'] = $parent_id ? $parent_id : 'IS NULL';
    $service_categories = $service_categories->where($args)->order_by('order_number asc')->get_results_as_models();
    if(!is_array($service_categories)) return;
		foreach($service_categories as $service_category){ ?>
			<div class="os-category-parent-w" data-id="<?php echo $service_category->id; ?>">
				<div class="os-category-w">
					<div class="os-category-head">
						<div class="os-category-drag"></div>
						<div class="os-category-name"><?php echo $service_category->name; ?></div>
						<div class="os-category-services-meta"><?php _e('ID: ', 'latepoint'); ?><span><?php echo $service_category->id; ?></span></div>
						<div class="os-category-services-count"><span><?php echo $service_category->count_services(); ?></span> <?php _e('Services Linked', 'latepoint'); ?></div>
						<button class="os-category-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></button>
					</div>
					<div class="os-category-body">
						<?php include(LATEPOINT_VIEWS_ABSPATH. 'service_categories/_form.php'); ?>
					</div>
				</div>
				<div class="os-category-children">
					<?php 
					if(is_array($service_category->services)){
						foreach($service_category->services as $service){
							echo '<div class="service-in-category-w status-'.$service->status.'" data-id="'.$service->id.'"><div class="os-category-service-drag"></div><div class="os-category-service-name">'.$service->name.'</div><div class="os-category-service-meta">ID: '.$service->id.'</div></div>';
						}
					} ?>
					<?php OsServiceHelper::generate_service_categories_list($service_category->id); ?>
				</div>
			</div>
			<?php
		}
	}

}