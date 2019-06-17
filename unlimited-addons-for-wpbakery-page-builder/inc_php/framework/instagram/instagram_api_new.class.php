<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class InstagramAPINewUC{
	
	private static $urlBase = "https://www.instagram.com/";
	const CACHE_RESPONSE = true;
	
	
	/**
	 * get data from raw response
	 */
	private function getDataFromRawResponse($responseRaw){
		    
		$regex = '#window\._sharedData\s?=\s?(.*);<\/script>#';
		$found = preg_match($regex, $responseRaw, $arrMatches);
		if(empty($found))
			UniteFunctionsUC::throwError("no data found");
	
		if(count($arrMatches) < 2)
			UniteFunctionsUC::throwError("no data extracted");
		
		$jsonData = $arrMatches[1];
		
		return($jsonData);
	}
	
	
	/**
	 * call api
	 */
	private function serverRequest($url){
		
		$arrHeaders = array();
		$arrHeaders[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36";
		$arrHeaders[] = "Origin: https://www.instagram.com";
		$arrHeaders[] = "Referer: https://www.instagram.com";
		$arrHeaders[] = "Connection: close";
		$arrHeaders[] = "Host: www.instagram.com";
				
		$response = null;
		
		if(self::CACHE_RESPONSE == true){
			$cacheKey = $this->createCacheKey($url);
			$response = HelperInstaUC::getFromCache($cacheKey);
		}
				
		if(empty($response) || self::CACHE_RESPONSE == false){
			
			$responseRaw = HelperInstaUC::getRemoteUrl($url, $arrHeaders);
			
			//decode raw response
			$response = $this->getDataFromRawResponse($responseRaw);
			
			if(self::CACHE_RESPONSE == true)
				HelperInstaUC::cacheResponse($cacheKey, $response);
		}
				
		return($response);
	}
	
	
	/**
	 * request server and get response array
	 */
	private function requestForData($url){
		
		$jsonData = $this->serverRequest($url);
		
		if(empty($jsonData))
			UniteFunctionsUC::throwError("Wrong API Response");
		
		$arrData = json_decode($jsonData, true);
		if(empty($arrData))
			UniteFunctionsUC::throwError("Wrong Response");
		
		$arrData = UniteFunctionsUC::convertStdClassToArray($arrData);
		
		return($arrData);
	}
	
	
	/**
	 * request for items
	 */
	private function requestForItems($url){
		
		$arrData = $this->requestForData($url);
				
		$objItems = new InstaObjUserUCItemsUC();
		$objItems->initNewAPI($arrData);
		
		return($objItems);
	}
	
	
	/**
	 * request for comments
	 */
	private function requestForComments($url){
		
		$arrData = $this->requestForData($url);
			
		$objComments = new InstaObjCommentsUC();
		$objComments->initByData($arrData);
		
		return($objComments);
	}
	
	
	/**
	 * request for item data
	 */
	private function requestForItemData($url){
		
		$arrData = $this->requestForData($url);
				
		$objItem = new InstaObjItemUC();
		
		$itemData = UniteFunctionsUC::getVal($arrData, "media");
		if(empty($itemData)){
			$itemData = $arrData["graphql"];
			$itemData = $itemData["shortcode_media"];
		}
		
		if(empty($itemData))
			UniteFunctionsUC::throwError("No item data found");
		
		$objItem->initNewAPI($itemData);
		
		return($objItem);
	}
	
	
	/**
	** create cache key from url
	 */
	private function createCacheKey($url){
		
		$info = parse_url($url);
		$path = UniteFunctionsUC::getVal($info, "path");
		$query = UniteFunctionsUC::getVal($info, "query");
		$key = "instagallery_".$path."_".$query;
		$key = HelperInstaUC::convertTitleToHandle($key);
		return($key);
	}
	
	
	/**
	 * create url
	 */
	private function createUrl($query, $lastID=null,$params = ""){
		
		$url = self::$urlBase.$query."/";
		
		if(!empty($lastID))
			$url .= "&max_id=".$lastID;
		
		if(!empty($params))
			$url .= "&".$params;
		
		return($url);
	}
	

	
	/**
	 * get images from user
	 */
	public function getUserData($user, $lastID = null, $userID = null){
				
		$user = HelperInstaUC::sanitizeUser($user);
				
		HelperInstaUC::validateInstance($user,"user");
				
		$url = $this->createUrl($user, $lastID);
				
		$objItems = $this->requestForItems($url);
				
		return($objItems);
	}
	
	
	/**
	 * convert items to simple array
	 */
	private function convertItemsToSimpleArray($objItems, $maxItems = null){
		
		if($maxItems !== null){
			$maxItems = (int)$maxItems;
			if($maxItems < 1)
				$maxItems = null;
		}
		
		$arrItems = $objItems->getItems();
		
		$arrItemsData = array();
		
		foreach($arrItems as $index=>$item){
			
			if($maxItems && $index >= $maxItems)
				break;	
			
			$data = $item->getDataSimple();
			$arrItemsData[] = $data;
		}
		
		
		return($arrItemsData);
	}
	
	
	/**
	 * get items data uf it's user or tag
	 */
	public function getItemsData($mixed, $lastID=null, $userID = null, $maxItems = null){
		
		$type = "";
		if(strpos($mixed,"@") === 0)
			$type = "user";
		else
			if(strpos($mixed,"#") === 0)
				$type = "tag";
		
		if(empty($type)){
			$type = "user";
			$mixed .= "@".$mixed;
		}
		
		
		if(empty($type))
			UniteFunctionsUC::throwError("Wrong type, should be user or tag");
		
		switch($type){
			case "user":
				$objItems = $this->getUserData($mixed, $lastID, $userID);
			break;
			case "tag":
				$objItems = $this->getTagData($mixed, $lastID, $userID);
			break;
		}
		
		$arrItems = $this->convertItemsToSimpleArray($objItems, $maxItems);
				
		$pageData = $objItems->getArrPageData();
		
		
		$response = array();
		$response["main"] = $pageData;
		$response["items"] = $arrItems;
		
		return($response);
	}
	
	
	
	
	/**
	 * get tag data
	 */
	public function getTagData($tag, $lastID=null, $userID = null){
		
		$tag = HelperInstaUC::sanitizeTag($tag);
		HelperInstaUC::validateInstance($tag,"tag");
		
		$query = "explore/tags/$tag";
		$url = $this->createUrl($query, $lastID);
		
		$objItems = $this->requestForItems($url);
		$objItems->setIsTag();
		
		return($objItems);
	}
	
	
	/**
	 * get arr comments of some item
	 */
	public function getArrComments($itemID){
		
		$query = "p/$itemID";
		$url = $this->createUrl($query);
		$objComments = $this->requestForComments($url);
		
		return($objComments);
	}
	
	
	/**
	 * get video item data
	 */
	public function getItemData($itemID){
		
		$query = "p/$itemID";
		$url = $this->createUrl($query,"","");
		
		$objItem = $this->requestForItemData($url);
		
		return($objItem);
	}
	
}