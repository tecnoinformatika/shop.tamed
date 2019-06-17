<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayouts extends UniteCreatorLayoutsWork{
	
	
	/**
	 * check if layout exists by title
	 */
	public function isLayoutExistsByTitle($title){
	
		$isExists = UniteFunctionsWPUC::isPostExistsByTitle($title, GlobalsProviderUC::LAYOUTS_POST_TYPE);
			
		return($isExists);
	}
	
	/**
	 * get layouts array from posts
	 */
	protected function getLayoutsFromPosts(){
		
		$arrPosts = UniteFunctionsWPUC::getPostsByType(GlobalsProviderUC::LAYOUTS_POST_TYPE);
		
		if(empty($arrPosts))
			$arrPosts = array();
		
		return($arrPosts);
	}
	
	
	/**
	 * get layouts array short version - without content
	 */
	public function getArrLayoutsShort($addEmpty = false,$params = array()){
		
		$arrPosts = $this->getLayoutsFromPosts();
		
		$arrLayouts = array();
		
		if($addEmpty == true){
			$arrLayouts["empty"] = "[Not Selected]";
		}
		
		foreach($arrPosts as $post){
			
			$postID = UniteFunctionsUC::getVal($post, "ID");
			$title = UniteFunctionsUC::getVal($post, "post_title");
			
			$arrLayouts[$postID] = $title;
			
		}
	
		return($arrLayouts);
	}
	
	
}
	