<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorPageBuilder{

	protected $layoutID = null;
	protected $objLayout;
	protected $objActionsPanel;
	protected $isLiveView;
	protected $isEditMode = false;
	protected $objGridEditor;
	protected $objLayouts;
	
	private $optionPanelHiddenAtStart = true;
	private $optionPanelInitWidth = 278;
	
	//outer related
	protected $urlViewLayoutEdit;
	protected $browserAddonType = null;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->browserAddonType = GlobalsUC::$layoutsAddonType;
		
	}
	
	/**
	 * put browser dialog
	 */
	private function putBrowserDialog(){
		
		$objBrowser = new UniteCreatorBrowser();
		$objBrowser->initAddonType($this->browserAddonType);
		$objBrowser->putBrowser();		
	}
	
	
	/**
	 * set view mode
	 */
	protected function setViewMode(){
		
		$stateName = "layout_view_mode";
		
		//check live mode
		$viewMode = UniteFunctionsUC::getGetVar("viewmode", "",UniteFunctionsUC::SANITIZE_KEY);
		
		//save view mode
		if(!empty($viewMode)){
			HelperUC::setState($stateName, $viewMode);
		}else{
			$viewMode = HelperUC::getState($stateName);
		}
		
		if(empty($viewMode))
			$viewMode = "live";
		
		$this->isLiveView = true;
		if($viewMode == "box")
			$this->isLiveView = false;
				
	}
	
	/**
	 * init common objects
	 */
	protected function initCommon(UniteCreatorLayout $objLayout){
		
		$this->objLayout = $objLayout;
		
		$isInited = $objLayout->isInited();
		
		if($isInited)
			$this->layoutID = $objLayout->getID();
		
		$this->setViewMode();
		
		if(!empty($this->layoutID))
			$this->isEditMode = true;
		
	}
	
	protected function a____________INNER____________(){}
	
	
	/**
	 * init inner page builder
	 */
	public function initInner(UniteCreatorLayout $objLayout){

		$this->initCommon($objLayout);
		
		$this->objGridEditor = new UniteCreatorGridBuilderProvider();
		$this->objGridEditor->putJsInit();
		$this->objGridEditor->setGridID("uc_grid_builder");
		
		if($this->isLiveView == true)
			$this->objGridEditor->setLiveView();
			
		//init the layout object if in edit mode
		if(!empty($this->layoutID))			
			$this->objGridEditor->initByLayout($this->objLayout);
		
	}
	
	
	/**
	 * put inner html
	 */
	protected function putInnerHtml(){
				
		?>
<div class="unite-content-wrapper unite-inputs uc-content-layout">

		<div id="uc_edit_layout_wrapper">
		
		<?php UniteProviderFunctionsUC::putInitHelperHtmlEditor()?>

			<div class="unite-clear"></div>
		
		<!-- right buttons end -->
		
			<?php 
				$this->objGridEditor->putGrid();
			?>
  		 
	</div>	<!-- layout edit wrapper --> 
</div>

<?php 
	
	}
	
	
	/**
	 * display inner side
	 */
	public function displayInner(){
				
				
		$this->putInnerHtml();
		
		//require HelperUC::getPathTemplate("layout_edit");
		
	}
	
	private function __________PANEL_________(){}
	
	/**
	 * put global settings dialog. stand alone function
	 */
	public function putLayoutsGlobalSettingsDialog(){
		
		$settingsGeneral = UniteCreatorLayout::getGlobalSettingsObject();
		
		$outputGeneralSettings = new UniteSettingsOutputWideUC();
		$outputGeneralSettings->setShowSaps(true);
		$outputGeneralSettings->init($settingsGeneral);
		
		?>
		
		<div id="uc_dialog_layout_global_settings" title="<?php HelperUC::putText("layouts_global_settings"); ?>" class="unite-inputs" style="display:none">
				
				<div class="unite-dialog-inner-constant">
		
				<?php 		
					$outputGeneralSettings->draw("uc_layout_general_settings", true);
					
				?>
				</div>
				
				<?php 
					$prefix = "uc_dialog_layout_global_settings";
					$buttonTitle = __("Update Global Settings", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Updating...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Settings Updated", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
		</div>		
		
		
		<?php
	}
	
	
	/**
	 * get page font names
	 */
	public static function getPageFontNames($forAddons = false){
		
		$arrFontNames = array();
		
		if($forAddons == false)
			$arrFontNames["page"] = __("Page Font", ADDONLIBRARY_TEXTDOMAIN);
		
		$arrFontNames["title"] = __("Title Font", ADDONLIBRARY_TEXTDOMAIN);
		$arrFontNames["subtitle"] = __("Subtitle Font", ADDONLIBRARY_TEXTDOMAIN);
		$arrFontNames["accent"] = __("Accent Text Font", ADDONLIBRARY_TEXTDOMAIN);
		$arrFontNames["user"] = __("User Defined Font", ADDONLIBRARY_TEXTDOMAIN);
		
		return($arrFontNames);
	}
	
	
	/**
	 * add default page fonts
	 */
	protected function modifyGridDialogSettings_addPageFonts($objGridSettings){
		
		//set default page fonts
		$settingFonts = $objGridSettings->getSettingByName("page_fonts");
		
		$arrFontNames = self::getPageFontNames();
		
		$settingFonts["font_param_names"] = $arrFontNames;
		
		$objGridSettings->updateArrSettingByName("page_fonts", $settingFonts);
		
		return($objGridSettings);
	}
	
	
	/**
	 * modify grid settings for dialog
	 */
	private function modifyGridDialogSettings($objGridSettings){
		
		$arrSettings = $objGridSettings->getArrSettings();
				
		$descPrefix = __(". If %s, it will be set to global value: ", ADDONLIBRARY_TEXTDOMAIN);
		
		$optionsGlobal = UniteCreatorLayout::getGridGlobalOptions();
		
		//$arrExceptToEmpty = array("show_row_titles");
		$arrExceptToEmpty = array();
		
		foreach($arrSettings as $setting){
		
			$name = UniteFunctionsUC::getVal($setting, "name");
		
			//set replace sign
			switch($name){
				default:
					$replaceSign = "empty";
				break;
			}
		
			$descActualPrefix = sprintf($descPrefix, $replaceSign);
		
			//handle excepts
			$globalOptionExists = array_key_exists($name, $optionsGlobal);
			if($globalOptionExists == false)
				continue;
		
			$globalValue = UniteFunctionsUC::getVal($optionsGlobal, $name);
			$setting["description"] .=  $descActualPrefix.$globalValue;
			$setting["placeholder"] =  $globalValue;
		
			//handle to empty excerpts
			$isExceptEmpty = array_search($name, $arrExceptToEmpty);
			if($isExceptEmpty === false){
				$setting["value"] = "";
				$setting["default_value"] = "";
			}
		
			$objGridSettings->updateArrSettingByName($name, $setting);
			
		}
		
		
		//add default fonts
		$objGridSettings = $this->modifyGridDialogSettings_addPageFonts($objGridSettings);
				
		return($objGridSettings);
	}	
	
	
	
	/**
	 * put side panel
	 */
	private function putSidePanel(){
	    		
		$objSidePanel = new UniteCreatorGridBuilderPanel();

		//add main menu pane
	    $title = __("Main Menu", ADDONLIBRARY_TEXTDOMAIN);
		
		$htmlMainMenu = $this->objActionsPanel->getMainMenuHtml();
	    
		$params = array(UniteCreatorGridBuilderPanel::PARAM_NO_HEAD=>true);
	    
	    $objSidePanel->addCustomHtmlPane("main-menu", $title, $htmlMainMenu, $params);
		
		//add grid settings pane
		$objGridSettings = UniteCreatorLayout::getGridSettingsObject();
	    $objGridSettings = $this->modifyGridDialogSettings($objGridSettings);
	    
	    $title = __("Page Settings", ADDONLIBRARY_TEXTDOMAIN);
	    
	    $objSidePanel->addPane("grid-settings", $title, $objGridSettings, "uc_settings_grid");
		
	    //add row settings
		
	    $objRowSettings = HelperUC::getSettingsObject("layout_row_settings");
	    $title = __("Row Settings", ADDONLIBRARY_TEXTDOMAIN);
	
	    $objSidePanel->addPane("row-settings", $title, $objRowSettings, "uc_settings_row");
		
	    //add column settings
	    $objColumnSettings = HelperUC::getSettingsObject("layout_column_settings");
	    $title = __("Column Settings", ADDONLIBRARY_TEXTDOMAIN);
	
	    $objSidePanel->addPane("col-settings", $title, $objColumnSettings, "uc_settings_col");

	    //add addon container settings
	    $objColumnSettings = HelperUC::getSettingsObject("layout_addon_container_settings");
	    $title = __("Addon Container Settings", ADDONLIBRARY_TEXTDOMAIN);
	    
	    $objSidePanel->addPane("addon-container-settings", $title, $objColumnSettings, "uc_settings_addon_container");
	    
	    //add addon settings
	    $title = __("Addon Settings", ADDONLIBRARY_TEXTDOMAIN);
	    $objSidePanel->addPane("addon-settings", $title, "get_addon_settings_html", "uc_settings_addon");
	    
	    
		//init
		$objSidePanel->init();
		
		if($this->optionPanelHiddenAtStart == true)
			$objSidePanel->setHiddenAtStart();
		
		$objSidePanel->setInitWidth($this->optionPanelInitWidth);
		
		//put html
		$objSidePanel->putHtml();
	}
	
	private function __________OUTER_________(){}
	
	
	/**
	 * init outer by layout
	 */
	public function initOuter($objLayout){
		
		$this->initCommon($objLayout);
		
		$this->objActionsPanel = new UniteCreatorGridBuilderActionsPanel();
								
		//get layout iframe url
		$urlParams = "";
		
		if(!empty($this->layoutID)){
			$this->layoutID = (int)$this->layoutID;
			$urlParams = "id=".$this->layoutID;
		}
		
		$this->urlViewLayoutEdit = HelperUC::getViewUrl(GlobalsUC::VIEW_LAYOUT_IFRAME, $urlParams, true);
		
		//init top actions panel
		$this->objActionsPanel->initByLayout($this->objLayout);
		
		if($this->isLiveView)
			$this->objActionsPanel->setLiveView();
		
		require HelperUC::getPathViewObject("layouts_view.class");
		require HelperUC::getPathViewProvider("provider_layouts_view.class");
		$this->objLayouts = new UniteCreatorLayoutsViewProvider();
		
	}
	
	
	/**
	 * put edit script
	 */
	protected function putOuterScript(){
		?>
		
		<script>

			var g_objPageBuilder = null;
			
			jQuery(document).ready(function(){
				
				g_objPageBuilder = new UniteCreatorPageBuilder();
				g_objPageBuilder.init();				
			});
			
		</script>
		
		<?php 
	}
	
	
	/**
	 * put html
	 */
	protected function putOuterHtml(){
		
		$addHtml = "";
		if(!empty($this->layoutID))
			$addHtml = "data-pageid=\"{$this->layoutID}\"";

		$iframeWrapperAddHtml = "";
		
		if($this->optionPanelHiddenAtStart == false){
		    
			$paddingLeft = $this->optionPanelInitWidth;
			$iframeWrapperAddHtml .= "style='padding-left:{$paddingLeft}px'";
		}
			
			
		?>	
			<div id="uc_page_builder" class="uc-page-builder uc-view-desktop uc-state-saved" <?php echo $addHtml?>>
				
				<?php 
					UniteProviderFunctionsUC::putInitHelperHtmlEditor();
					
					$this->objActionsPanel->putPanelHtml();
					$this->putSidePanel();
				?>
				
				<div class="uc-iframe-wrapper" <?php echo $iframeWrapperAddHtml?>>
					<iframe src="<?php echo $this->urlViewLayoutEdit?>" frameborder="0" class="uc-layout-iframe"></iframe>
				</div>
			</div>
			
		<?php 
		
		$this->putHtmlStatuses();
		
		$this->objLayouts->putDialogImportLayout();
		
		if($this->isEditMode)
		  	UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_LAYOUT_EDIT_HTML);
		
		$this->putBrowserDialog();
		  	
	}
	
	/**
	 * put statuses html
	 */
	protected function putHtmlStatuses(){
		?>
		
		<div class="uc-layout-statuses">
	        
	        <div id="uc_layout_status_loader" class="uc-save-status" style="display:none">
	        	<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
	        	<span><?php _e("Saving...", ADDONLIBRARY_TEXTDOMAIN)?></span>
	        </div>
	        
	        <div id="uc_layout_status_success" class="uc-save-status" style="display:none"></div>
	        
	        <div id="uc_layout_status_error" class="uc-save-status uc-status-error" style="display:none">
		        <i class="fa fa-exclamation-triangle" aria-hidden="true" style="font-color:red; margin-left: 0;"></i>
		        <span class="uc-layout-error-message"></span>
		        <a href="javascript:void(0)" class="uc-save-status-close" >X</a>
	        </div>
  		 
  		 </div>
		
		<?php 
	}
	
	
	/**
	 * display outer part of the page builder
	 */
	public function displayOuter(){
		
		$this->putOuterHtml();
		$this->putOuterScript();
		
	}
	
}