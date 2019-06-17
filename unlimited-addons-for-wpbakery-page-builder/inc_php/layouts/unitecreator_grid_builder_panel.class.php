<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorGridBuilderPanel{

	const PANE_TYPE_SETTINGS = "settings";
	const PANE_TYPE_CUSTOM_HTML = "custom_html";
	
	const PARAM_NO_HEAD = "no_head";
	
	private $optionHideAtStart = false;
	private $optionPutHideButton = false;
	private $optionInitWidth = null;
	private $optionAllowUndock = false;
	
	private $isInited = false;
	private $arrPanes = array();
	
	
	/**
	 * validate inited
	 */
	private function validateInited(){
		
		if($this->isInited == false)
			UniteFunctionsUC::throwError("The grid editor panel no inited");
	
	}
	
	
	/**
	 * add pane with settings
	 */
	public function addPane($name, $title, $objSettings, $settingsOutputID, $params = array()){
		
		$arr = array();
		$arr["type"] = self::PANE_TYPE_SETTINGS;
		$arr["name"] = $name;
		$arr["title"] = $title;
		$arr["settings"] = $objSettings;
		$arr["settings_id"] = $settingsOutputID;
		$arr["params"] = $params;
				
		$this->arrPanes[] = $arr;
	}
	
	
	/**
	 * add custom html pane
	 */
	public function addCustomHtmlPane($name, $title, $html, $params = array()){
		
		$arr = array();
		$arr["type"] = self::PANE_TYPE_CUSTOM_HTML;
		$arr["name"] = $name;
		$arr["title"] = $title;
		$arr["html"] = $html;
		$arr["params"] = $params;
		
		$this->arrPanes[] = $arr;
	}
	
	
	/**
	* validate pane type
	 */
	private function validatePaneType($type){
		switch($type){
			case self::PANE_TYPE_CUSTOM_HTML:
			case self::PANE_TYPE_SETTINGS:
			break;
			default:
				UniteFunctionsUC::throwError("Wrong pane type: $type");
			break;
		}
	}
	
	
	/**
	 * put pane
	 */
	private function putPane($pane, $index){
		
		$type = UniteFunctionsUC::getVal($pane, "type");
		$this->validatePaneType($type);
		
		$name = UniteFunctionsUC::getVal($pane, "name");
		UniteFunctionsUC::validateNotEmpty($name,"pane name");
		
		$title = UniteFunctionsUC::getVal($pane, "title");
		UniteFunctionsUC::validateNotEmpty($title,"pane title");
		
		$params = UniteFunctionsUC::getVal($pane, "params");
		if(empty($params))
			$params = array();
		
		$params["pane_type"] = $type;
		
		$title = htmlspecialchars($title);
			
		$ajaxAction = null;
		$objSettings = null;
		
		//settings type
		if($type == self::PANE_TYPE_SETTINGS){
			$objSettings = UniteFunctionsUC::getVal($pane, "settings");
			
			if(is_string($objSettings)){
				$ajaxAction = $objSettings;
				$objSettings = null;
			}
			
			
			$outputID = UniteFunctionsUC::getVal($pane, "settings_id");
			UniteFunctionsUC::validateNotEmpty($outputID,"pane output id");
			
			if($objSettings){
			    $output = new UniteSettingsOutputSidebarUC();
			    $output->init($objSettings);
			}
			
		}
		
		//custom html type
		if($type == self::PANE_TYPE_CUSTOM_HTML){
			
			$html = UniteFunctionsUC::getVal($pane, "html");
		}
		
	    $style = "";
	    $addClass = " uc-current-pane";
	    if($index > 0){
	        $style = " style='display:none'";
	        $addClass = "";
	    }
	    
	    $addHtml = "";
	    if(!empty($ajaxAction)){
			$addHtml = " data-settings-outputid='{$outputID}' data-action='{$ajaxAction}' ";
	    }
	    
	    //add params
		$htmlParams = UniteFunctionsUC::jsonEncodeForHtmlData($params, "options");
	    $addHtml .= " " . $htmlParams;
	    
	    
	    $loaderText = __("Loading Settings...", ADDONLIBRARY_TEXTDOMAIN);
	    
	   	
	    ?>
    		<div class="uc-grid-panel-pane uc-pane-<?php echo $name?><?php echo $addClass?>" data-name="<?php echo $name?>" data-title="<?php echo $title?>" <?php echo $addHtml?> <?php echo $style?> >
    			
    			<div class="uc-grid-panel-pane-loader" style='display:none'><?php echo $loaderText?></div>
    			<div class='uc-grid-panel-pane-content'>
	    			<?php 
	    				switch($type){
	    					case self::PANE_TYPE_SETTINGS:
			    				if($objSettings)
			    					$output->draw($outputID, true);
	    					break;
	    					case self::PANE_TYPE_CUSTOM_HTML:
	    						echo $html;
	    					break;
	    				}
	    			?>
    			</div>
    		</div>
	    <?php
	}
	
	
	/**
	 * put panes
	 */
	private function putPanes(){
		
		UniteFunctionsUC::validateNotEmpty($this->arrPanes, "Panes array");
		
		foreach($this->arrPanes as $index => $pane)
		    $this->putPane($pane, $index);
	}
	
	
	/**
	 * put html title
	 */
	private function putHtmlHead(){
		
	    $title = $this->arrPanes[0]["title"];
	    $name = $this->arrPanes[0]["name"];
	    
		?>
		
   		<div class="uc-grid-panel-head uc-panetype-<?php echo $name?>">
   			<a class="uc-grid-panel-head-edit" title="<?php _e("Edit My Addon", ADDONLIBRARY_TEXTDOMAIN)?>" target="_blank" href="javascript:void(0)" ><i class="fa fa-pencil" aria-hidden="true"></i></a>
   			<div class="uc-grid-panel-head-logo" style="display:none"></div>
   			<div class="uc-grid-panel-head-text" ><?php echo $title?></div>
   			
   			<a href="javascript:void(0)" class="uc-grid-panel-icon uc-panel-icon-action uc-panel-icon-copy" data-action="copy" title="<?php _e("Copy Settings", ADDONLIBRARY_TEXTDOMAIN)?>"><i class="fa fa-copy" aria-hidden="true"></i></a>
   			<a href="javascript:void(0)" class="uc-grid-panel-icon uc-panel-icon-action uc-panel-icon-paste" data-action="paste" title="<?php _e("Paste Settings", ADDONLIBRARY_TEXTDOMAIN)?>"><i class="fa fa-paste" aria-hidden="true"></i></a>
   			
   			<a href="javascript:void(0)" class="uc-grid-panel-icon uc-grid-panel-head-close" title="<?php _e("Close Panel")?>">X</a>	   			
   		</div>
		
		<?php 
	}
	
	
	/**
	 * get options for output array
	 */
	private function getArrOptionsForOutput(){
		
		$options = array();
		$options["allow_undock"] = $this->optionAllowUndock;
		
		return($options);
	}
	
	
	/**
	 * put html
	 */
	public function putHtml(){
		
		UniteFunctionsUC::validateNotEmpty($this->optionInitWidth, "grid panel init width");
		
		$addHtml = "";
		$style = "width:".$this->optionInitWidth."px;";
		
		if($this->optionHideAtStart == true){
			$style .= "display:none";
		}
		
		$addHtml .= "style='{$style}'";
		
		$options = $this->getArrOptionsForOutput();
		$strOptions = UniteFunctionsUC::jsonEncodeForHtmlData($options, "options");
		
		$addHtml .= " {$strOptions}";
		
		?>
		
		   <div class="uc-grid-panel" <?php echo $addHtml?>>
		   		
		   		<?php $this->putHtmlHead()?>
		   		
		   		<div class="uc-grid-panel-body">
		   		
		   			<?php $this->putPanes()?>
		   		
		   		</div>
		   		
		   		<?php if($this->optionPutHideButton == true):?>
		   		
		   		<div class="uc-grid-panel-bottom">
				   	
		   			<a href="javascript:void(0)" class="uc-panel-button-hide" title="Hide Panel"> 
		   				<i class="fa fa-window-close-o" aria-hidden="true"></i>
		   			 </a>
		   			 
		   		</div>
		   		
		   		<?php endif?>
		   		
		   </div>
		   
		   <a href="javascript:void(0)" title="<?php _e("Show Panel", ADDONLIBRARY_TEXTDOMAIN)?>" class="uc-grid-panel-show-handle" style="display:none">
		   		<i class="fa fa-window-close-o" aria-hidden="true"></i>
		   </a>
		
		
		<?php 
	}
	
	/**
	 * put init width
	 */
	public function setInitWidth($width){
		$this->optionInitWidth = $width;
	}
	
	
	/**
	 * set hidden at start
	 */
	public function setHiddenAtStart(){
		$this->optionHideAtStart = true;
	}
	
	/**
	 * init the panel
	 */
	public function init(){
		$this->isInited = true;
	}
	
}