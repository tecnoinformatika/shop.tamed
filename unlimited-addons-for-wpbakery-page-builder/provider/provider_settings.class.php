<?php
/**
 * @package Addon Library
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('ADDON_LIBRARY_INC') or die('Restricted access');

class UniteCreatorSettings extends UniteCreatorSettingsWork{
		
	
	private function showTax(){
										
		$showTax = UniteFunctionsUC::getGetVar("maxshowtax", "", UniteFunctionsUC::SANITIZE_NOTHING);
		$showTax = UniteFunctionsUC::strToBool($showTax);
		//$showTax = true;
		if($showTax == true){
			
			$args = array("taxonomy"=>"");
			$cats = get_categories($args);
			
			$arr1 = UniteFunctionsWPUC::getTaxonomiesWithCats();
			
			//dmp($arr1);exit();
			
			$arrPostTypes = UniteFunctionsWPUC::getPostTypesAssoc();
			$arrTax = UniteFunctionsWPUC::getTaxonomiesWithCats();
			$arrCustomTypes = get_post_types(array('_builtin' => false));
			
			$arr = get_taxonomies();
			
			$taxonomy_objects = get_object_taxonomies( 'post', 'objects' );
   			dmp($taxonomy_objects);
   			
			dmp($arrCustomTypes);
			dmp($arrPostTypes);
			exit();
		}
		
	}
	
	
	/**
	 * add post list picker
	 */
	protected function addPostsListPicker($name,$value,$title,$extra){
		
		$simpleMode = UniteFunctionsUC::getVal($extra, "simple_mode");
		$simpleMode = UniteFunctionsUC::strToBool($simpleMode);
		
		$arrPostTypes = UniteFunctionsWPUC::getPostTypesWithCats();
		
		//fill simple types
		$arrTypesSimple = array();
		
		if($simpleMode)
			$arrTypesSimple = array("Post"=>"post","Page"=>"page");
			
		else{
			
			foreach($arrPostTypes as $arrType){
				
				$postTypeName = UniteFunctionsUC::getVal($arrType, "name");
				$postTypeTitle = UniteFunctionsUC::getVal($arrType, "title");
				
				$arrTypesSimple[$postTypeTitle] = $postTypeName;
			}
			
		}

		$postType = UniteFunctionsUC::getVal($value, $name."_posttype", "post");
		
		$params = array();
		
		if($simpleMode == false){
			$params["datasource"] = "post_type";
			$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-type";
			
			//$dataCats = UniteFunctionsUC::jsonEncodeForHtmlData($arrPostTypes);
			$dataCats = UniteFunctionsUC::encodeContent($arrPostTypes);
			
			$params[UniteSettingsUC::PARAM_ADDPARAMS] = "data-arrposttypes='$dataCats' data-settingtype='select_post_type'";
		}
		
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$params["description"] = __("Select which Post Type or Custom Post Type you wish to display", ADDONLIBRARY_TEXTDOMAIN);
		
		$this->addSelect($name."_posttype", $arrTypesSimple, __("Post Type", ADDONLIBRARY_TEXTDOMAIN), $postType, $params);
		
		//add categories
		$arrCats = array();
		
		if($simpleMode == true){
			$arrCats = $arrPostTypes["post"]["cats"];
			$arrCats = array_flip($arrCats);
			$firstItemValue = reset($arrCats);
		}else{
			$firstItemValue = "";
		}
				
		$category = UniteFunctionsUC::getVal($value, $name."_category", $firstItemValue);
		
		$params = array();
		
		if($simpleMode == false){
			$params["datasource"] = "post_category";
			$params[UniteSettingsUC::PARAM_CLASSADD] = "unite-setting-post-category";
		}
		
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$params["description"] = __("Filter Posts by Specific Category", ADDONLIBRARY_TEXTDOMAIN);
		$this->addSelect($name."_category", $arrCats, __("Post Category", ADDONLIBRARY_TEXTDOMAIN), $category, $params);
		
		//add maxitems
		$params = array("unit"=>"posts");
		$maxItems = UniteFunctionsUC::getVal($value, $name."_maxitems", 10);
		$params["origtype"] = UniteCreatorDialogParam::PARAM_TEXTFIELD;
		
		$params["description"] = "Enter how many Posts you wish to display, -1 for unlimited";
		
		$this->addTextBox($name."_maxitems", $maxItems, __("Max Posts", ADDONLIBRARY_TEXTDOMAIN), $params);
		
		//add orderby
		$arrOrder = UniteFunctionsWPUC::getArrSortBy();
		$arrOrder = array_flip($arrOrder);
		
		$arrDir = UniteFunctionsWPUC::getArrSortDirection();
		$arrDir = array_flip($arrDir);
		
		//orderby1
		$params = array();
		
		$params[UniteSettingsUC::PARAM_ADDFIELD] = $name."_orderdir1";
		
		$orderBY = UniteFunctionsUC::getVal($value, $name."_orderby", UniteFunctionsWPUC::SORTBY_ID);
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		$params["description"] = __("Select how you wish to order posts", ADDONLIBRARY_TEXTDOMAIN);
		
		$this->addSelect($name."_orderby", $arrOrder, __("Order By", ADDONLIBRARY_TEXTDOMAIN), $orderBY, $params);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$params["description"] = __("Select order direction. Descending A-Z or Accending Z-A", ADDONLIBRARY_TEXTDOMAIN);
		
		$orderDir1 = UniteFunctionsUC::getVal($value, $name."_orderdir1", UniteFunctionsWPUC::ORDER_DIRECTION_DESC );
		$this->addSelect($name."_orderdir1", $arrDir, self::PARAM_NOTEXT, $orderDir1, $params);
		
		$params = array();
		$params["origtype"] = UniteCreatorDialogParam::PARAM_HR;
		
		$this->addHr("", $params);
		
	}
	
	
}