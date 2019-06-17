<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorGridBuilder{

	const ID_PREFIX = "uc_grid_builder";
	private static $serial = 0;
		
	private $gridID;
	private $initData = null;
	private $putJs = false;
	private $isLiveView = false;
		
	
	/**
	 * constructor
	 */
	public function __construct(){
				
	}
	
	
	/**
	 * set grid ID
	 */
	public function setGridID($gridID){
		$this->gridID = $gridID;
	}
	
	/**
	 * switch to live view
	 */
	public function setLiveView(){
		$this->isLiveView = true;
	}
	
	
	/**
	 * set to put js init
	 */
	public function putJsInit(){
		$this->putJs = true;
	}
	
	
	/**
	 * set the layout object
	 */
	public function initByLayout(UniteCreatorLayout $objLayout){
		
		$addAddonContent = $this->isLiveView;
		
		$this->initData = $objLayout->getGridDataForEditor($addAddonContent);
		
		//dmp($this->initData);exit();
	}
	
	
	/**
	 * put js init
	 */
	private function putJs(){
		?>
			<script type="text/javascript">

				jQuery(document).ready(function(){
					
					var objBuilder = new UniteCreatorGridBuilder();
					objBuilder.init("#<?php echo $this->gridID?>");
					
				});
							
			</script>
		
		<?php 
	}
	
	
	
	
	/**
	 * put layout select dialog
	 */
	private function putLayoutDialog(){
		
		$titleText = __("Choose Row Layout", ADDONLIBRARY_TEXTDOMAIN);
		
		?>
		
        <div id="uc_dialog_row_layout" class='uc-dialog-row-layout'  title="<?php echo $titleText?>" style="display:none">
                
                <div class="uc-dialog-columns-wrapper">
                
                    <div class='uc-layout-column'>
                        
                        <div class='uc-layout-row' data-layout-type="1_1">
                            <div class="uc-layout-col uc-layout-column-size-1_1"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_4-1_4-1_4-1_4">
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_4-3_4">
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-3_4"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_4-1_4-1_2">
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_2"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_5-1_5-1_5-2_5">
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-2_5"></div>
                        </div>
                        
                    </div>
                    
                    <div class='uc-layout-column'>
                        <div class='uc-layout-row' data-layout-type="1_2-1_2">
                             <div class="uc-layout-col uc-layout-column-size-1_2"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_2"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="2_3-1_3">
                            <div class="uc-layout-col uc-layout-column-size-2_3"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_3"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="3_4-1_4">
                            <div class="uc-layout-col uc-layout-column-size-3_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_4-1_2-1_4">
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_2"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="empty" onclick="jQuery(this).unbind('click')">
                            <div class="uc-layout-col uc-layout-column-size-1_1" style="background-color: #fff" onclick=""></div>
                        </div>
                        
                    </div>
                    
                    <div class='uc-layout-column'>
                        
                        <div class='uc-layout-row' data-layout-type="1_3-1_3-1_3">
                            <div class="uc-layout-col uc-layout-column-size-1_3"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_3"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_3"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_3-2_3">
                            <div class="uc-layout-col uc-layout-column-size-1_3"></div>
                            <div class="uc-layout-col uc-layout-column-size-2_3"></div>
                        </div>
                        
                        <div class='uc-layout-row' data-layout-type="1_2-1_4-1_4">
                            <div class="uc-layout-col uc-layout-column-size-1_2"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_4"></div>
                        </div>
                                                
                        <div class='uc-layout-row' data-layout-type="2_5-1_5-1_5-1_5">
                            <div class="uc-layout-col uc-layout-column-size-2_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                            <div class="uc-layout-col uc-layout-column-size-1_5"></div>
                        </div>
                        
                        <!-- 
                        <div class='uc-layout-row' data-layout-type="empty" onclick="jQuery(this).unbind('click')">
                            <div class="uc-layout-col uc-layout-column-size-1_1" style="background-color: #fff" onclick=""></div>
                        </div>
                        -->
                        
                        
                    </div>
                    
                </div>
            </div>   
		
		<?php 
	}
	
	private function __________ACTIONS_PANEL_________(){}
	
	/**
	 * put actions panel
	 */
	public function putActionsPanel(){
		
		$objActionsPanel = new UniteCreatorGridBuilderActionsPanel();
		
		if($this->isLiveView)
			$objActionsPanel->setLiveView();
		
		if(!empty($this->initData))
			$objActionsPanel->setEditMode();
			
		
		$objActionsPanel->putPanelHtml();
		
	}
	
	
	/**
	 * get behaviour options
	 */
	private function getBuilderOptions(){
		
		$options = array();
		$options["indev"] = GlobalsUC::$inDev;
		
		//put google fonts
		$fontPanelData = HelperUC::getFontPanelData();
		$arrGoogleFonts = $fontPanelData["arrGoogleFonts"];
		$options["google_fonts"] = $arrGoogleFonts;
		
		return($options);
	}
	
	
	/**
	 * get grid options - from global object and grid settings
	 * they can be not overriden because they will be overriden in the front
	 * only keys will be overriden
	 */
	private function getGridCombinedOptions(){
		
		$optionsGlobal = UniteCreatorLayout::getGridGlobalOptions();
		$optionsGrid = UniteCreatorLayout::getGridSettingsOptions();
		
		//merge only missing keys
		foreach($optionsGrid as $key=>$value){
			
			if(array_key_exists($key, $optionsGlobal) == false)
				$optionsGlobal[$key] = $value;
		}
		
		return($optionsGlobal);
	}
	
	
	/**
	 * put grid
	 */
	public function putGrid(){
		
		if(empty($this->gridID)){
			self::$serial++;
			$this->gridID = self::ID_PREFIX.self::$serial;
		}
		
		$gridID = $this->gridID;
		
		$classAdd = " uc-grid-box";
		
		$addHtml = " data-liveview='false'";
		if($this->isLiveView == true){
			$addHtml = " data-liveview='true'";
			$classAdd = " uc-grid-live";
		}
		
		//get data-init='...'
		
		$initData = "";
		if(!empty($this->initData)){
			$initData = UniteFunctionsUC::jsonEncodeForHtmlData($this->initData, "init");
		}
		
		//get grid options
		$options = $this->getGridCombinedOptions();
		
		$dataOptions = UniteFunctionsUC::jsonEncodeForHtmlData($options, "options");
		
		//get builder options
		$builderOptions = $this->getBuilderOptions();
		$dataBuilderOptions = UniteFunctionsUC::jsonEncodeForHtmlData($builderOptions, "builder-options");
		
		
		?>
			<div class="uc-grid-builder-wrapper" >
				
				<style type="text/css" ></style>
								
				<span class="uc-grid-row-styles"></span>
				<span class="uc-grid-col-styles"></span>
				
				<div class="uc-grid-builder-outer">
					<div id="<?php echo $gridID?>" class="uc-grid-builder<?php echo $classAdd?>" <?php echo $initData.$dataOptions.$dataBuilderOptions.$addHtml?> ></div>
				</div>
				
				<?php 
				
                $this->putLayoutDialog();
				
				?>
				
			</div>
			
			
			<?php 
				if($this->putJs == true)
					$this->putJs();
		
	}
	
	
	
}