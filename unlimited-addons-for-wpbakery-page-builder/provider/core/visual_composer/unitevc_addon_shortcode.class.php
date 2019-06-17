<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UCVCAddonBase extends WPBakeryShortCode {

	
	/**
	 * get params values
	 * @param $atts
	 */
	protected function getParamsValues($atts){
		
		if(empty($atts))
			return(array());
		
		$arrValues = $atts;
		
		if(isset($arrValues["uc_items_data"]))
			unset($arrValues["uc_items_data"]);
		
		if(isset($arrValues["uc_fonts_data"]))
			unset($arrValues["uc_fonts_data"]);
		
		return($arrValues);
	}
	
	
	/**
	 * get items data
	 */
	private function getItemsData($atts){
		
		if(!isset($atts["uc_items_data"]))
			return(array());
		
		$itemsData = $atts["uc_items_data"];
		if(empty($itemsData))
			return(array());
		
		$arrItems = UniteVCCustomParams::decodeContent($itemsData);
		
		return($arrItems);
	}
	
	/**
	 * get items data
	 */
	private function getFontsData($atts){
	
		if(!isset($atts["uc_fonts_data"]))
			return(array());
	
		$fontsData = $atts["uc_fonts_data"];
		if(empty($fontsData))
			return(null);
		
		$arrFonts = UniteVCCustomParams::decodeContent($fontsData);
		
		return($arrFonts);
	}
	
	
	/**
	 * addon shortcode with all the addon related methods
	 */
	protected function content($atts, $content = null) {
		
		try{
			
			
			$addonAlias = UniteFunctionsUC::getVal($this->settings, "addon_name");
			$arrParamValues = $this->getParamsValues($atts);
			
			
			if($content !== null){
				$arrParamValues["content"] = $content;
			}
			
			$objAddon = new UniteCreatorAddon();
			$objAddon->initByAlias($addonAlias, UniteVcIntegrateUC::ADDONTYPE_VC);
			
			
			//decode editors content
			$arrParamsEditors = $objAddon->getParams(array(UniteCreatorDialogParam::PARAM_EDITOR, UniteCreatorDialogParam::PARAM_TEXTAREA));
			
			foreach($arrParamsEditors as $param){
				
				$name = UniteFunctionsUC::getVal($param, "name");
				if(isset($arrParamValues[$name]))
					$arrParamValues[$name] = UniteFunctionsUC::maybeDecodeTextContent($arrParamValues[$name]);
			}
			
			//decode html
			foreach($arrParamValues as $key=>$value){
				$arrParamValues[$key] = htmlspecialchars_decode($value);
			}
			
			
			$arrItemsData = $this->getItemsData($atts);
			$arrFontsData = $this->getFontsData($atts);
			
			
			//------- init addon
			
			if(!empty($arrParamValues))
				$objAddon->setParamsValues($arrParamValues);
			
			if(!empty($arrItemsData))
				$objAddon->setArrItems($arrItemsData);
			
			if(!empty($arrFontsData))
				$objAddon->setArrFonts($arrFontsData);
			
			
			//------- init output
			
			$output = new UniteCreatorOutput();
			$output->initByAddon($objAddon);
			
			$cssFilesPlace = HelperUC::getGeneralSetting("css_includes_to");
			
			//process only js in include css in body
			$includesProcessType = ($cssFilesPlace == "footer")?"all":"js";
			
			$output->processIncludes($includesProcessType);
			
			//decide if the js will be in footer
			$scriptsHardCoded = false;
			$isInFooter = HelperUC::getGeneralSetting("js_in_footer");
			$isInFooter = UniteFunctionsUC::strToBool($isInFooter);
			
			if($isInFooter == false)
				$scriptsHardCoded = true;
			
			$putCssIncludesInBody = ($cssFilesPlace == "body")?true:false;
			
			$htmlOutput = $output->getHtmlBody($scriptsHardCoded, $putCssIncludesInBody);
			
		}catch(Exception $e){
			$htmlOutput = "";
			HelperHtmlUC::outputExceptionBox($e, "Unlimited Addons Error");
		}
		
		
		return $htmlOutput;
	}

}
