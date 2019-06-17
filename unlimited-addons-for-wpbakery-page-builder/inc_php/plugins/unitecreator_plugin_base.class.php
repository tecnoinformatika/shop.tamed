<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


/**
 * plugin base
 *
 */
class UniteCreatorPluginBase extends UniteCreatorFilters{
	
	protected $urlPlugin;
	protected $pathPlugin;
	
	private $isRegistered = false;
	private $objPlugins;
	
	
	/**
	 * set path and url plugin
	 */
	private function initPaths($pathPlugin){
		
		if(empty($pathPlugin))
			return(false);
		
		$this->pathPlugin = $pathPlugin;
		$this->urlPlugin = HelperUC::pathToFullUrl($this->pathPlugin);
		
	}
	
	
	/**
	 * constructor
	 */
	public function __construct($pathPlugin = null){
		
		$this->initPaths($pathPlugin);
		
		$this->objPlugins = new UniteCreatorPlugins();
	}
	
	
	/**
	 * validate that the plugin is registered
	 */
	protected function validateRegistered(){
		
		if($this->isRegistered == false)
			UniteFunctionsUC::throwError("The plugin is not registered");
	}
	
	
	/**
	 * register the plugin
	 */
	protected function register($name, $title, $version, $description, $params){
		
		$this->objPlugins->registerPlugin($name, $title, $version, $description, $params);
	}
	
	
	/**
	 * add action
	 */
	protected function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		UniteProviderFunctionsUC::addAction($tag, array($this,$function_to_add),$priority, $accepted_args);
	}
	
	
	/**
	 * add filter
	 */
	protected function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		UniteProviderFunctionsUC::addFilter($tag, array($this,$function_to_add), $priority, $accepted_args);
	}
	
		
	
}