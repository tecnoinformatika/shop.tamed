<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorOutput extends UniteCreatorOutputWork{
	
	
	/**
	 * process html before output, function for override
	 */
	protected function processHtml($html){
		
		$html = do_shortcode($html);
		
		return($html);
	}
	
	/**
	 * put header additions in header html, functiob for override
	 */
	protected function putPreviewHtml_headerAdd(){
		//wp_head();
	}
	
	
	/**
	 * put footer additions in body html, functiob for override
	 */
	protected function putPreviewHtml_footerAdd(){
	}
	
	
}