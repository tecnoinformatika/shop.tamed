<?php

// no direct access
defined('UNLIMITED_ADDONS_INC') or die;


class AddonLibrary_WidgetLayout extends WP_Widget {
	
    public function __construct(){
    	
        // widget actual processes
     	$widget_ops = array('classname' => 'widget_addonlibrary_layout', 'description' => __('Show Unlimited Addons Layout') );
        parent::__construct('addonlibrary-widget', __('Unlimited Addons Layout', UNLIMITED_ADDONS_TEXTDOMAIN), $widget_ops);
    }

    
    /**
     * 
     * the form
     */
    public function form($instance) {
		
    	$objLayouts = new UniteCreatorLayouts();
    	$arrLayouts = $objLayouts->getArrLayoutsShort(true);
    	$fieldID = "addonlibrarylayoutid";
    	$layoutID = UniteFunctionsUC::getVal($instance, $fieldID);
    	
    	if(empty($arrLayouts)){
    		?>
    		<div style="padding-top:10px;padding-bottom:10px;">
    		<?php _e("No layouts found, Please create a layout", UNLIMITED_ADDONS_TEXTDOMAIN); ?>
    		</div>
    		<?php }
    	else{
    		$fieldOutputID = $this->get_field_id( $fieldID );
    		$fieldOutputName = $this->get_field_name( $fieldID );
    		
    		$selectLayouts = HelperHtmlUC::getHTMLSelect($arrLayouts, $layoutID,'name="'.$fieldOutputName.'" id="'.$fieldOutputID.'"',true);
    		?>
				<div style="padding-top:10px;padding-bottom:10px;">
				
				<?php _e("Title", UNLIMITED_ADDONS_TEXTDOMAIN)?>: 
				&nbsp; <input type="text" id="<?php echo $this->get_field_id( "title" );?>" name="<?php echo $this->get_field_name( "title" )?>" value="<?php echo UniteFunctionsUC::getVal($instance, 'title')?>" />
				
				<br><br>
				
				<?php _e("Choose a Layout", UNLIMITED_ADDONS_TEXTDOMAIN)?>: 
				<?php echo $selectLayouts?>
				
				</div>
				
				<br>
    		
    		<?php 
    	}

    }
 
    
    /**
     * 
     * update
     */
    public function update($new_instance, $old_instance) {
    	
        return($new_instance);
    }

    
    /**
     * 
     * widget output
     */
    public function widget($args, $instance) {
    	
    	$title = UniteFunctionsUC::getVal($instance, "title");
		    	
    	$layoutID =  UniteFunctionsUC::getVal($instance, "addonlibrarylayoutid");
    	
    	if(empty($layoutID))
    		return(false);
    	    	
    	//widget output
    	$beforeWidget = UniteFunctionsUC::getVal($args, "before_widget");
    	$afterWidget = UniteFunctionsUC::getVal($args, "after_widget");
    	$beforeTitle = UniteFunctionsUC::getVal($args, "before_title");
    	$afterTitle = UniteFunctionsUC::getVal($args, "after_title");
    	
    	echo $beforeWidget;
    	
    	if(!empty($title))
    		echo $beforeTitle.$title.$afterTitle;
    	
    	if(is_numeric($layoutID) == false)
    		_e("no layout selected", UNLIMITED_ADDONS_TEXTDOMAIN);
    	else
    		HelperUC::outputLayout($layoutID);
 		
    	echo $afterWidget;
    }
 
    
}


?>