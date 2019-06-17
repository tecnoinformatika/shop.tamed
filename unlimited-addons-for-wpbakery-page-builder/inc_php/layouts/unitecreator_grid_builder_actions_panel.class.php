<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorGridBuilderActionsPanel extends HtmlOutputBaseUC{
	
	private $isLiveView = false;
	protected $isEditMode = false;
	protected $title;
	protected $layoutID = null;
	protected $shortcodeWrappers = "{}";
	
	
	/**
	 * get main menu items
	 */
	protected function getArrMainMenuItems(){
		
		$arrMenu = array();
		
		$urlLayoutsList = HelperUC::getViewUrl_LayoutsList();
		$urlPreview = $this->getUrlPreview();
		
		$arrMenu[] = array(
			   "text"=>__("Import Page", ADDONLIBRARY_TEXTDOMAIN),
			   "action"=>"import",
			   "icon"=>"fa fa-download");
		
		$arrMenu[] = array(
			   "text"=>__("Export Page", ADDONLIBRARY_TEXTDOMAIN),
			   "action"=>"export",
			   "icon"=>"fa fa-upload");
		
		$arrMenu[] = array(
			   "text"=>__("Preview Page", ADDONLIBRARY_TEXTDOMAIN),
			   "href"=>$urlPreview,
			   "isblank"=>true,
			   "icon"=>"fa fa-eye");
		
		$arrMenu[] = array(
			   "text"=>__("Save And Exit", ADDONLIBRARY_TEXTDOMAIN),
			   "action"=>"save_exit",
			   "data"=>array(
			   					"url_back"=>$urlLayoutsList,
								"message"=>__("Exiting to pages list...", ADDONLIBRARY_TEXTDOMAIN)
							),
			   "icon"=>"fa fa-save");
				
		
		return($arrMenu);
	}
	
	
	/**
	 * get main menu html
	 */
	public function getMainMenuHtml(){
		
		$arrItems = $this->getArrMainMenuItems();
		
		$html = "";
		$html .= "<ul class='uc-grid-panel-menu'>".self::BR;
		
		foreach($arrItems as $item){
			
			$href = UniteFunctionsUC::getVal($item, "href");
			if(empty($href)){
				$href = "javascript:void(0)";
			}
						
			$action = UniteFunctionsUC::getVal($item, "action");
			$text = UniteFunctionsUC::getVal($item, "text");
			$icon = UniteFunctionsUC::getVal($item, "icon");
			$isblank = UniteFunctionsUC::getVal($item, "isblank");
			$data = UniteFunctionsUC::getVal($item, "data");
			
			$htmlBlank = "";
			if($isblank === true)
				$htmlBlank = " target='_blank'";
			
			$htmlData = "";
			if(!empty($data))
				$htmlData = UniteFunctionsUC::jsonEncodeForHtmlData($data,"params");
			
			$dataAction = "";
			$class = "";
			if(!empty($action)){
				$dataAction = " data-action='$action'";
				$class = " class='uc-panel-action-button'";
			}
			
			$html .= self::TAB."<li>".self::BR;
			
			$addHtml = $dataAction.$class.$htmlBlank.$htmlData;
			
			$html .= "	<a href=\"$href\" {$addHtml}>".self::BR;
			$html .= "		<i class='{$icon}' aria-hidden='true'></i>".self::BR;
			$html .= "		<span>{$text}</span>".self::BR;
			$html .= "	</a>".self::BR;
			
			$html .= "</li>".self::BR;
			
		}
		
		$html .= "</ul>".self::BR;
		
		return($html);
	}
		
	
	/**
	 * set live view
	 */
	public function setLiveView(){
		
		$this->isLiveView = true;
	}
	
	
	/**
	 * put layout title edit window
	 */
	protected function putLayoutTitleWindow($title){
	    
	    $isNew = empty($this->layoutID);
	    
	    
	    $styleNew = "";
	    $styleExisting = " style='display:none'";
	    
	    if($isNew == false){
	        $styleNew = " style='display:none'";
	        $styleExisting = "";
	    }
	        
	    
		?>
		            
    		<div class='uc-layout-title-panel'>
    			
                <div class="uc-visible-part">
    				
	    			<span id="uc_page_title"><?php echo UniteFunctionsUC::sanitizeAttr($this->title)?></span>
	                	<i class="fa fa-angle-down" aria-hidden="true"></i>
					</div>
					
					<div id="uc_layout_title_box" class="uc-layout-title-box">
					
	                	<div class="uc-layout-title-box-inner unite-ui">
	                		
	                		<!-- page name -->
	                		<div class='uc-page-name-wrapper'>
	                        	 <div class="uc-titlebox-label"><?php _e("Page Name", ADDONLIBRARY_TEXTDOMAIN)?>:</div>
	                        	 <input type="text" class="unite-input-regular" value="<?php echo UniteFunctionsUC::sanitizeAttr($this->title)?>" id="uc_layout_title" placeholder="<?php _e("New Page", ADDONLIBRARY_TEXTDOMAIN)?>">
	                        	 <a id="uc_button_rename_page" href="javascript:void(0)" class="unite-button-primary" ><?php echo _e("Save", ADDONLIBRARY_TEXTDOMAIN)?></a>
	                        	 <span id="uc_button_rename_page_loader" class="loader_text" style="display:none"><?php _e("Saving", ADDONLIBRARY_TEXTDOMAIN)?>...</span>
                        	 </div>
                        	 
                        
                        	<!-- shortcode -->
                        	 <div class="uc-titlebox-label uc-label-shortcode"><?php _e("Shortcode:", ADDONLIBRARY_TEXTDOMAIN)?></div> 
                        	 
                        	 <div class="uc-layout-newpage" <?php echo $styleNew?> >
                        	 
                            	 <div class="vert_sap10"></div>
                            	 	
                            	 	<div class="uc-titlebox-text">
                            		 	<?php _e("The shortcode will be availble after save page")?>
                            	  </div>
                            	  
                        	 </div>
                        	 
                        	 <div class="uc-layout-existingpage" <?php echo $styleExisting?> >
                        	 
                            	 <input type="text" id="uc_layout_shortcode" class="uc-input-shortcode unite-input-regular"  data-shortcode="<?php echo GlobalsUC::$layoutShortcodeName?>" data-wrappers="<?php echo $this->shortcodeWrappers?>" readonly onfocus="this.select()" value="" title="<?php echo UniteFunctionsUC::sanitizeAttr($this->title)?>">
                        		 
                        		 <div class="vert_sap10"></div>
                        		 
                        		 <a id="uc_link_copy_shortcode" class="uc-shortcode-text-copy"><?php _e("Copy shortcode to clipoard", ADDONLIBRARY_TEXTDOMAIN)?></a>
    	                	
    	                	</div>	
                        	 		                		
	                		
                		</div>
                	
				</div>
	                     	                
            </div>
		
		<?php 
	}
	
	/**
	 * get preview page url
	 */
	protected function getUrlPreview(){
		
		$urlPreview = HelperUC::getViewUrl_LayoutPreview(0, true);
		
		if($this->isEditMode)
			$urlPreview = HelperUC::getViewUrl_LayoutPreview($this->layoutID, true);
		
		return($urlPreview);
	}
	
	/**
	 * put panel html
	 */
	public function putPanelHtml(){
		
		$isNew = empty($this->layoutID);
		
		$styleNew = "";
		$styleExisting = " style='display:none'";
		
		if($isNew == false){
		    $styleNew = " style='display:none'";
		    $styleExisting = "";
		}
		
		$urlLayoutsList = HelperUC::getViewUrl_LayoutsList();
		
		$urlPreview = $this->getUrlPreview();
		
		//box and live tabs
		$urlLiveView = HelperUC::getViewUrl_Layout($this->layoutID,"viewmode=live");
		$urlBoxView = HelperUC::getViewUrl_Layout($this->layoutID, "viewmode=box");
		
		$urlBoxView = htmlspecialchars($urlBoxView);
		$urlLiveView = htmlspecialchars($urlLiveView);
		
		if($this->isLiveView == false){		//box view
			
			$textEditMode = __("Live", ADDONLIBRARY_TEXTDOMAIN);
			$classEditMode = "uc-editmode-live";
			$titleEditMode = __("To Live View", ADDONLIBRARY_TEXTDOMAIN);
			$redirectMessage = __("Redirecting to Live View", ADDONLIBRARY_TEXTDOMAIN);
			$iconEditMode = "fa fa-desktop";
			$editViewMode = "box";
		}
		else{	//live view
			
			$textEditMode = __("Box", ADDONLIBRARY_TEXTDOMAIN);
			$classEditMode = "uc-editmode-box";	
			$titleEditMode = __("To Box View", ADDONLIBRARY_TEXTDOMAIN);
			$redirectMessage = __("Redirecting to Box View", ADDONLIBRARY_TEXTDOMAIN);
			$iconEditMode = "fa fa-th-large";	
			$editViewMode = "live";
		}
		
		?>
			<div class="uc-edit-layout-panel">
				
				<!-- left buttons  -->
				
            	<a href="javascript:void(0)" data-action="open_main_menu" class="uc-toppanel-button unite-float-left">
	                <i class="fa fa-bars uc-menu-closed" aria-hidden="true"></i>
	                <i class="fa fa-times uc-menu-opened" aria-hidden="true"></i>
	            	<span><?php _e("Menu",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
				
				<!--  
            	<a id="uc_button_export_layout" href="javascript:void(0)" class="uc-toppanel-button unite-float-left uc-layout-existingpage" <?php echo $styleExisting?> >
                	<em><i class="fa fa-download" aria-hidden="true"></i></em>
            		<span><?php _e("Export",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a> 
	          	
	     		<a id="uc_button_import_layout" href="javascript:void(0)" class="uc-toppanel-button unite-float-left uc-layout-existingpage" <?php echo $styleExisting?>>
                	<em><i class="fa fa-upload" aria-hidden="true"></i></em>
            		<span><?php _e("Import",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
            	-->
            	
	     		<a id="uc_button_view_size_mobile" href="javascript:void(0)" data-action="view_mobile" title="<?php _e("To Mobile View", ADDONLIBRARY_TEXTDOMAIN)?>" class="uc-toppanel-button unite-float-left uc-button-view-related uc-view-desktop">
                	<em><i class="fa fa-mobile" aria-hidden="true"></i></em>
            		<span><?php _e("Mobile",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
            	
	     		<a id="uc_button_view_size_desktop" href="javascript:void(0)" data-action="view_desktop" title="<?php _e("To Desktop View", ADDONLIBRARY_TEXTDOMAIN)?>" class="uc-toppanel-button unite-float-left uc-button-view-related uc-view-tablet">
                	<em><i class="fa fa-desktop" aria-hidden="true"></i></em>
            		<span><?php _e("Desktop",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
            	
	     		<a id="uc_button_view_size_tablet" href="javascript:void(0)" data-action="view_tablet" title="<?php _e("To Tablet View", ADDONLIBRARY_TEXTDOMAIN)?>" class="uc-toppanel-button unite-float-left uc-button-view-related uc-view-mobile">
                	<em><i class="fa fa-tablet" aria-hidden="true"></i></em>
            		<span><?php _e("Tablet",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
            	
            	<div id="uc_buffer_indicator" class="uc-buffer-container unite-float-left" style='display:none;'>
            		<span class='uc-buffer-container-content'></span>
            		<div class='uc-buffer-container-icon-close'></div>
            	</div>
				
				<!-- left buttons end -->
	            
            	<!-- page title panel -->
            	<?php $this->putLayoutTitleWindow($this->title)?>
            	
            
				<!-- right buttons -->
				 
             	<a href="javascript:void(0)" id="uc_button_update_layout" class="uc-toppanel-button unite-float-right uc-button-save-layout"> 
                	
                	<i id="uc_layout_save_button_icon" class="fa fa-check-square" aria-hidden="true"></i>
                	<i id="uc_layout_save_button_loader" class="fa fa-spinner" style='display:none'></i>
                	
            		<span><?php _e("Save",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
	         
	         
            	<a id="uc-button-preview-layout" href="<?php echo $urlPreview?>" target="_blank" class="uc-toppanel-button unite-float-right uc-layout-existingpage" <?php echo $styleExisting?>>
	                <i class="fa fa-eye" aria-hidden="true"></i>
	            	<span><?php _e("Preview",ADDONLIBRARY_TEXTDOMAIN)?></span>
	        	</a>
	         
	            
            	<a id="uc_button_edit_mode" class="uc-toppanel-button unite-float-right uc-link-editmode <?php echo $classEditMode?>" data-message="<?php echo $redirectMessage?>" data-urlbox="<?php echo $urlBoxView?>" data-urllive="<?php echo $urlLiveView?>" data-mode="<?php echo $editViewMode?>" href="javascript:void(0)" title="<?php echo $titleEditMode?>" >
                	<i class="<?php echo $iconEditMode?>" aria-hidden="true"></i>
            		<span><?php echo $textEditMode ?></span>
            	</a>
	           	            	
            	<a id="uc_button_grid_settings" href="javascript:void(0)" class="uc-toppanel-button unite-float-right">
	                <i class="fa fa-cog" aria-hidden="true"></i>
	            	<span><?php _e("Settings",ADDONLIBRARY_TEXTDOMAIN)?></span>
            	</a>
            	
			</div>
		
		
		<?php 
	}
	
	
	
	/**
	 * init by layout
	 */
	public function initByLayout(UniteCreatorLayout $objLayout){
		
		$isInited = $objLayout->isInited();
		
		if($isInited)
			$this->layoutID = $objLayout->getID();
		
		//init the layout object if in edit mode
		if(!empty($this->layoutID)){
			$this->isEditMode = true;
			
			$this->title = $objLayout->getTitle();
		}else{
			
			//if new mode - get new title
			$this->title = $objLayout->getNewLayoutTitle();
		}
				
	}
	
	
}