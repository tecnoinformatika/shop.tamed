<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorParamsProcessor extends UniteCreatorParamsProcessorWork{
	
	
	/**
	 * add other image thumbs based of the platform
	 */
	protected function addOtherImageThumbs($data, $name, $imageID){
		
		if(empty($data))
			$data = array();
		
		$imageID = trim($imageID);
		if(is_numeric($imageID) == false)
			return($data);
		
		$arrSizes = UniteFunctionsWPUC::getArrThumbSizes();
		
		$urlFull = UniteFunctionsWPUC::getUrlAttachmentImage($imageID);
		
		foreach($arrSizes as $size => $sizeTitle){
			
			if(empty($size))
				continue;
			
			if($size == "full")
				continue;
			
			//change the hypen to underscore
			
			$thumbName = $name."_thumb_".$size;
			if($size == "medium")
				$thumbName = $name."_thumb";
			
			$thumbName = str_replace("-", "_", $thumbName);
			
			$urlThumb = UniteFunctionsWPUC::getUrlAttachmentImage($imageID, $size);
			if(empty($urlThumb))
				$urlThumb = $urlFull;
			
			if(!isset($data[$thumbName]))
				$data[$thumbName] = $urlThumb;
			
		}
		
		return($data);
	}
	
	
	/**
	 * get post data
	 */
	protected function getPostData($postID){
		
		if(empty($postID))
			return(null);
		
		try{
		
			$output = array();
			$output["id"] = $postID;
			
		}catch(Exception $e){
			return(null);
		}
		
		return($output);
	}

	
	/**
	 * get post data
	 */
	private function getPostDataByObj($post){
		
		
		try{
			
			$arrPost = (array)$post;
			$arrData = array();
			
			$postID = UniteFunctionsUC::getVal($arrPost, "ID");
						
			$arrData["id"] = $postID;
			$arrData["title"] = UniteFunctionsUC::getVal($arrPost, "post_title");
			$arrData["alias"] = UniteFunctionsUC::getVal($arrPost, "post_name");
			$arrData["content"] = UniteFunctionsUC::getVal($arrPost, "post_content");
			$arrData["link"] = get_permalink($post);
			
			//get intro
			$intro = UniteFunctionsUC::getVal($arrPost, "post_excerpt");
			
			if(empty($intro)){
				$intro = $arrData["content"];
				
				if(!empty($intro)){
					$intro = strip_tags($intro);
					$intro = UniteFunctionsUC::limitStringSize($intro, 100);
				}
			}
			
			$arrData["intro"] = $intro;			

			//put data
			$strDate = UniteFunctionsUC::getVal($arrPost, "post_date");
			$arrData["date"] = !empty($strDate)?strtotime($strDate):"";
			
			
			$featuredImageID = UniteFunctionsWPUC::getFeaturedImageID($postID);
			
			if(!empty($featuredImageID))
				$arrData = $this->getProcessedParamsValue_image($arrData, $featuredImageID, array("name"=>"image"));
			
			
		}catch(Exception $e){
			return(null);
		}
			
		return($arrData);
	}
	
	
	/**
	 * get post list data
	 */
	private function getPostListData($value, $name, $processType){
		
		if(empty($value))
			return(array());
		
		if(is_array($value) == false)
			return(array());
		
		if($processType != self::PROCESS_TYPE_OUTPUT && $processType != self::PROCESS_TYPE_OUTPUT_BACK)
			return(null);
		
		$filters = array();	
		
		$postType = UniteFunctionsUC::getVal($value, "{$name}_posttype", "post");
		$filters["posttype"] = $postType;
		
		$category = UniteFunctionsUC::getVal($value, "{$name}_category");
		
		if(!empty($category))
			$filters["category"] = UniteFunctionsUC::getVal($value, "{$name}_category");
		
		$limit = UniteFunctionsUC::getVal($value, "{$name}_maxitems");
		
		$limit = (int)$limit;
		if($limit <= 0)
			$limit = 100;
		
		if($limit > 1000)
			$limit = 1000;
		
		$filters["limit"] = $limit;
		$filters["orderby"] = UniteFunctionsUC::getVal($value, "{$name}_orderby");
		$filters["orderdir"] = UniteFunctionsUC::getVal($value, "{$name}_orderdir1");
		
		$arrPosts = array();
		$arrPosts = UniteProviderFunctionsUC::applyFilters("uc_filter_posts_list", $arrPosts, $value, $filters);
		
		if(empty($arrPosts))
			$arrPosts = UniteFunctionsWPUC::getPosts($filters);
		
		$arrData = array();
		foreach($arrPosts as $post){
			
			$arrData[] = $this->getPostDataByObj($post);
		}
		
		return($arrData);
	}
	
	
	/**
	 * get processe param data, function with override
	 */
	protected function getProcessedParamData($data, $value, $param, $processType){
		
		$type = UniteFunctionsUC::getVal($param, "type");
		$name = UniteFunctionsUC::getVal($param, "name");
		
		//special params
		switch($type){
			
			case UniteCreatorDialogParam::PARAM_POSTS_LIST:
			    $data[$name] = $this->getPostListData($value, $name, $processType);
			break;
			default:
				$data = parent::getProcessedParamData($data, $value, $param, $processType);
			break;
		}
		
			
		return($data);
	}
	
	
	
	/**
	 * get param value, function for override, by type
	 */
	public function getSpecialParamValue($paramType, $paramName, $value, $arrValues){
		
	    switch($paramType){
	        case UniteCreatorDialogParam::PARAM_POSTS_LIST:
	        case UniteCreatorDialogParam::PARAM_CONTENT:
	            
	            $paramArrValues = array();
	            $paramArrValues[$paramName] = $value;
	            
	            foreach($arrValues as $key=>$value){
	                if(strpos($key, $paramName."_") === 0)
	                    $paramArrValues[$key] = $value;
	            }
	            
	            $value = $paramArrValues;
	            	            
	        break;
	    }
	    
	    
	    return($value);
	}
	
	
	
}