<?php

/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayout extends UniteCreatorLayoutWork{
	
	
	/**
	 * construct the layout
	 */
	public function __construct(){
		
		$this->addonType = "wp";
		
		parent::__construct();
	}
	
	
	/**
	 * init by post id
	 */
	public function initByID($id){
		
		$id = (int)$id;
		
		if(empty($id))
			UniteFunctionsUC::throwError("Empty layout ID");
		
		$post = @get_post($id);
		if(empty($post))
			UniteFunctionsUC::throwError("layout with id: $id not found");

		$this->id = $id;
		
		$title = $post->post_title;
		
		$layoutData = get_post_meta($id, "layout_data");
		if(is_array($layoutData))
			$layoutData = $layoutData[0];
		
		$record = array();
		$record["ordering"] = 0;
		$record["title"] = $title;
		$record["layout_data"] = $layoutData;
		
		$this->initByRecord($record);
		
	}
		
	
	/**
	 * update layout in db
	 */
	public function createLayoutInDB($arrInsert){
		
		$title = $arrInsert["title"];
		$name = sanitize_title($title);
		$layoutData = $arrInsert["layout_data"];
		
		$arrPost = array();
		$arrPost["post_title"] = $title;
		$arrPost["post_name"] = $name;
		$arrPost["post_content"] = "[uc_layout currentpage]";
		$arrPost["post_type"] = "uc_layout";
		$arrPost["post_status"] = "publish";
		
		
		$postID = wp_insert_post($arrPost);
		
		add_post_meta($postID, "layout_data", $layoutData);
		
		return($postID);
	}
	
	
	/**
	 * update layout in db
	 */
	public function updateLayoutInDB($arrUpdate){
		
		$layoutData = $arrUpdate["layout_data"];
		$postID = $this->id;
		
		//update post
		$arrPost = array();
		$arrPost["ID"] = $postID;
		
		//update title if needed
		if(isset($arrUpdate["title"])){
			$arrPost["post_title"] = $arrUpdate["title"];
			
			$success = wp_update_post($arrPost);
			
			if($success == 0)
				UniteFunctionsUC::throwError("Unable to update layout");
		}
		
		$updated = update_post_meta($postID, "layout_data", $layoutData);
		
		if($updated == false){
			$oldData = $this->getRawLayoutData();
			if($oldData != $layoutData)
				UniteFunctionsUC::throwError("Unable to update layout data");
		}
		
	
	}
	
	/**
	 * delete layout
	 */
	public function delete(){
		$this->validateInited();
		
		wp_delete_post($this->id, true);
	}
	
	
}