<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorExporterBase extends UniteElementsBaseUC{
	
	protected $pathExport;
	protected $pathImport;
	public static $serial = 0;	//serial number
	
	
	/**
	 * constructor
	 */
	public function __construct(){
	}
	
	
	/**
	 * prepare global export path
	 */
	protected function prepareExportFolders_globalExport(){
	
		$pathCache = GlobalsUC::$path_cache;
	
		UniteFunctionsUC::mkdirValidate($pathCache, "Cache");
	
		$pathExport = $pathCache."export/";
	
		UniteFunctionsUC::mkdirValidate($pathExport, "Export");
	
		$this->pathExport = $pathExport;
	
	}
	
	/**
	 * prepare global import folders
	 */
	protected function prepareImportFolders_globalImport(){
		
		//create cache folder
		$pathCache = GlobalsUC::$path_cache;
		UniteFunctionsUC::mkdirValidate($pathCache, "cache");
		
		//create import folder
		$this->pathImport = $pathCache."import/";
		UniteFunctionsUC::mkdirValidate($this->pathImport, "import");
		
		//create index.html
		UniteFunctionsUC::writeFile("", $this->pathImport."index.html");
		
	}
	
	
	
}