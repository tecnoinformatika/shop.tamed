<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayoutsExporterWork extends UniteCreatorExporterBase{
	
	const KEY_LOCAL = "[uc_local]";
	const IMAGES_IMPORT_FOLDER = "addon_library";
	
	protected $addonsType = "";
	
	protected $arrExportImages = array();
	protected $arrCacheImageFilenames = array();
	
	protected $objLayout;
	protected $arrAddons;
	
	//export
	protected $pathExportLayouts;
	protected $pathCopyLayout;
	protected $pathExportLayout;
	protected $pathExportLayoutAddons;
	protected $pathExportLayoutImages;
	protected $pathExportZip;
	protected $filenameZip;
	protected $exportedFilename;
	protected $isLayoutsFolderInited;
	protected $isOutputCatFolder = false;
	
	//import
	protected $lastImportID;
	protected $pathImportLayout;
	protected $pathImportLayoutImages;
	protected $pathImportLayoutAddons;
	protected $arrImportImages;
	
	
	/**
	 * constructor
	 */
	public function _construct(){
		
		parent::__construct();
	}
	
	
	private function a_________________EXPORT_____________(){}
	
	
	/**
	 * validate inited
	 */
	private function validateInited(){
		
		if(empty($this->objLayout))
			UniteFunctionsUC::throwError("The layout object is not inited");
	}
	
	/**
	 * init by layout
	 */
	public function initByLayout(UniteCreatorLayout $objLayout){
		
		$this->objLayout = $objLayout;
	}
	
	/**
	 * prepare layout file
	 */
	protected function putLayoutFile(){
		
		$record = $this->objLayout->getRecordForExport();
		
		$strLayout = json_encode($record);
		
		$filename = "layout.txt";
		$filepath = $this->pathExportLayout.$filename;
		
		UniteFunctionsUC::writeFile($strLayout, $filepath);
	}
	
	
	/**
	 * create layout category copy path
	 * return created path
	 */
	private function createCategoryCopyPath(){
		
		$arrCat = $this->objLayout->getCategory();
		
		$catName = UniteFunctionsUC::getVal($arrCat, "name");
		if(empty($catName))
			UniteFunctionsUC::throwError("Category should not be empty");
		
		$catID = UniteFunctionsUC::getVal($arrCat, "id");
		if(empty($catID))
			UniteFunctionsUC::throwError("Category should not be empty");
		
		$path = $this->pathExportLayouts.$catName."/";
		UniteFunctionsUC::mkdirValidate($path, "Copy Layout Cat Folders");
		
		return($path);		
	}
	
	/**
	 * prepare export folders - layout
	 */
	private function prepareExportFolders_layout(){
		
		UniteFunctionsUC::validateDir($this->pathExportLayouts, "Export Layouts");
		
		//make layout folder
		$this->pathExportLayout = $this->pathExportLayouts."layout_".UniteFunctionsUC::getRandomString(10)."/";
		UniteFunctionsUC::mkdirValidate($this->pathExportLayout, "Export Layout");
		
		//make inner paths
		$this->pathExportLayoutAddons = $this->pathExportLayout."addons/";
		UniteFunctionsUC::mkdirValidate($this->pathExportLayoutAddons, "Layout Addons");
		
		$this->pathExportLayoutImages = $this->pathExportLayout."images/";
		UniteFunctionsUC::mkdirValidate($this->pathExportLayoutImages, "Layout Images");
		
		//set path copy layout, with category folder or without
		
		$this->pathCopyLayout = $this->pathExportLayouts;
		if($this->isOutputCatFolder == true)
			$this->pathCopyLayout = $this->createCategoryCopyPath();
		
	}
	
	
	/**
	 * prepare export folders - layout
	 */
	private function prepareExportFolders_layouts(){
		
		$this->prepareExportFolders_globalExport();
		
		//make layouts folder
		$this->pathExportLayouts = $this->pathExport."layouts/";
		UniteFunctionsUC::mkdirValidate($this->pathExportLayouts, "Layouts");
		
		//clean folder
		if($this->isLayoutsFolderInited == false)
			UniteFunctionsUC::deleteDir($this->pathExportLayouts, false);
		
		//create index.html
		UniteFunctionsUC::writeFile("", $this->pathExportLayouts."index.html");
		
		$this->isLayoutsFolderInited = true;
	}
	
	
	/**
	 * put layout addons
	 */
	private function putLayoutAddons(){
		
		$this->validateInited();
		
		//get all layout addons
		$arrAddons = $this->objLayout->getArrAddons();
		
		foreach($arrAddons as $addon){
			$objAddonExporter = new UniteCreatorExporter();
			
			if(!empty($this->addonsType))
				$addon->setType($this->addonsType);
			
			$objAddonExporter->initByAddon($addon);
			$objAddonExporter->export($this->pathExportLayoutAddons, true);
		}
		
	}
	
	
	/**
	 * prepare export file zip
	 */
	private function prepareExportZip(){
		
		$title = $this->objLayout->getTitle();
		$handle = HelperUC::convertTitleToHandle($title);
		
		$prefix = "layout_";
		if(!empty($this->addonsType))
			$prefix = "layout_".$this->addonsType."_";
		
		UniteFunctionsUC::validateDir($this->pathCopyLayout,"copy layout path");
		
		//get unique filepath
		$filename = "{$prefix}{$handle}";
		$filenameZip = $filename.".zip";
		$filepath = $this->pathCopyLayout.$filenameZip;
		
		
		if(file_exists($filepath)){
			$counter = 0;
			do{
				$counter++;
				$filename = "{prefix}{$handle}{$counter}";
				$filenameZip = $filename.".zip";
				
				$filepath = $this->pathCopyLayout.$filenameZip;
				
				$fileExists = file_exists($filepath);
	
			}while($fileExists == false);
		}
	
		$this->exportedFilename = $filename;
		$this->filenameZip = $filenameZip;
		
		//actual zip
		$zip = new UniteZipUC();
		$zip->makeZip($this->pathExportLayout, $filepath);
	
		if(file_exists($this->pathExportLayout) == false)
			UniteFunctionsUC::throwError("zip file {$filepath} could not be created");
	
		$this->pathExportZip = $filepath;
	}
	
	
	/**
	 * download export file
	 */
	private function downloadExportFile(){
	
		UniteFunctionsUC::downloadFile($this->pathExportZip);
	}
	
	
	/**
	 * get export file data
	 */
	private function getExportedFileData(){
		
		$filepath = $this->pathExportZip;
		$urlFile = HelperUC::pathToFullUrl($filepath);
		$filename = $this->filenameZip;
		
		$output = array();
		$output["filepath"] = $filepath;
		$output["urlfile"] = $urlFile;
		$output["filename"] = $filename;
		$output["exported_filename"] = $this->exportedFilename;
		
		return($output);
	}
		
	/**
	 * delete export layout folder after the zip is done
	 */
	public function deleteExportLayoutFolder(){
		
		if(is_dir($this->pathExportLayout))
			UniteFunctionsUC::deleteDir($this->pathExportLayout, true);
			
	}
	
	/**
	 * set output with category folders
	 */
	public function setOutputWithCats(){
		$this->isOutputCatFolder = true;
	}
	
	/**
	 * export layout file
	 */
	public function export($isReturnData = false){
		
		try{
	
			$this->validateInited();
						
			$this->prepareExportFolders_layouts();
			$this->prepareExportFolders_layout();
			
			$this->objLayout->cleanLayoutSettingsBeforeExport();
			
			$this->putLayoutAddons();
			$this->putLayoutImages();
			
			$this->putLayoutFile();
			
			$this->prepareExportZip();
			
			$this->deleteExportLayoutFolder();
			
			if($isReturnData == true){
				$arrData = $this->getExportedFileData();
								
				return($arrData);
			}
			
			$this->downloadExportFile();
			exit();
	
		}catch(Exception $e){
	
			$prefix = "Export Layout Error: ";
			if(!empty($this->objLayout)){
				$title = $this->objLayout->getTitle();
				$prefix = "Export Layout (<b>$title</b>) Error: ";
	
			}
	
			$message = $prefix.$e->getMessage();
	
			echo $message;
			exit();
		}
	
	}
	
	
	private function a_____________EXPORT_IMAGES_____________(){}
	
	/**
	 * get save filename, image should not exists
	 */
	private function processConfigImage_getSaveFilename($pathImage){
		
		$info = pathinfo($pathImage);
		$filename = UniteFunctionsUC::getVal($info, "basename");
		if(empty($filename))
			return(null);
			
		$isFileExists = array_key_exists($filename, $this->arrCacheImageFilenames);
		if($isFileExists == false)
			return($filename);
	
		$saveFilename = $filename;
		
		$basename = $info["filename"];
		$ext = $info["extension"];
		
		$counter = 0;
		$textPortion = UniteFunctionsUC::getStringTextPortion($basename);
		if(empty($textPortion))
			$textPortion = $basename."_";
		
		do{
			$counter++;
			$saveFilename = $textPortion.$counter.".".$ext;
			$isFileExists = array_key_exists($saveFilename, $this->arrCacheImageFilenames);
	
		}while($isFileExists == true);
		
		$this->arrCacheImageFilenames[$saveFilename] = true;
		
		
		return($saveFilename);
	}
	
	
	/**
	 * process config image
	 * return processed image array or null
	 */
	private function processConfigImage($urlImage){
		
		$urlImage = HelperUC::URLtoFull($urlImage);
		
		$pathImage = HelperUC::urlToPath($urlImage);
		if(empty($pathImage))
			return null;
		
		if(file_exists($pathImage) == false || is_file($pathImage) == false)
			return null;
							
		$isUnderAssets = HelperUC::isPathUnderAssetsPath($pathImage);
		
		if($isUnderAssets == true)
			return null;
		
		$handlePath = HelperUC::convertTitleToHandle($pathImage);
		
		if(isset($this->arrExportImages[$handlePath])){
			$saveFilename = $this->arrExportImages[$handlePath]["save_filename"];
			
			return($saveFilename);
		}
		
		$saveFilename = $this->processConfigImage_getSaveFilename($pathImage);
		
		if(empty($saveFilename))
			return(null);
		
		$arrImage = array();
		$arrImage["save_filename"] = $saveFilename;
		$arrImage["path"] = $pathImage;
		
		$this->arrExportImages[$handlePath] = $arrImage;
		
		return($saveFilename);
	}
	
	
	
	/**
	 * prepare images fields for export
	 * modifyType - import / export
	 */
	private function modifyAddons_exportImport($addonData, $modifyType){
		
		$name = UniteFunctionsUC::getVal($addonData, "name");
		$addonType = $this->objLayout->getAddonType();
		
		$config = UniteFunctionsUC::getVal($addonData, "config");
		if(empty($config))
			$config = array();
		
		$items = UniteFunctionsUC::getVal($addonData, "items");
		
		$objAddon = new UniteCreatorAddon();
		$objAddon->initByMixed($name, $addonType);
		$objAddon->setParamsValues($config);
		
		if(!empty($items))
			$objAddon->setArrItems($items);
		
		//process config
		$arrConfigImages = $objAddon->getProcessedMainParamsImages();
		foreach($arrConfigImages as $key=>$urlImage){
			
			switch($modifyType){
				case "export":
					$localFilename = $this->processConfigImage($urlImage);
					if(!empty($localFilename))
						$arrConfigImages[$key] = self::KEY_LOCAL.$localFilename;
				break;
				case "import":
					
					$urlImage = $this->processConfigImage_import($urlImage);
					$arrConfigImages[$key] = $urlImage;
					
				break;
				default:
					UniteFunctionsUC::throwError("Wrong modify type: $modifyType");
				break;
			}
			
		}
		
		if(!empty($arrConfigImages)){
			$config = array_merge($config, $arrConfigImages);
			$addonData["config"] = $config;
		}
		
		if(empty($items))
			return($addonData);
		
		//process items
		$arrItemsImages = $objAddon->getProcessedItemsData(UniteCreatorParamsProcessor::PROCESS_TYPE_SAVE, false, "uc_image");
		
		foreach($arrItemsImages as $itemKey => $itemImage){
			
			if(empty($itemImage))
				continue;
			
			
			foreach($itemImage as $key=>$urlImage){
				
				switch($modifyType){
					case "export":
						
						$localFilename = $this->processConfigImage($urlImage);
						if(!empty($localFilename))
							$itemImage[$key] = self::KEY_LOCAL.$localFilename;
					break;	
					case "import":
						
						$urlImage = $this->processConfigImage_import($urlImage);
						$itemImage[$key] = $urlImage;
										
					break;
					default:
						UniteFunctionsUC::throwError("Wrong modify type: $modifyType");
					break;
				}
				
			}
			
			$items[$itemKey] = array_merge($items[$itemKey], $itemImage);
		}
		
		$addonData["items"] = $items;
		
		return($addonData);
	}
	
	
	/**
	 * modify addons - export images
	 */
	public function modifyAddons_exportImages($addonData){
		
		$addonData = $this->modifyAddons_exportImport($addonData, "export");
		
		return($addonData);
	}
	
	
	/**
	 * modify settings - export images
	 */
	public function modifySettings_exportImages($arrSettings, $type){
		
		$key = "bg_image_url";
		
		$urlImage = UniteFunctionsUC::getVal($arrSettings, $key);
		if(empty($urlImage))
			return($arrSettings);
		
		$localFilename = $this->processConfigImage($urlImage);
		if(empty($localFilename))
			return($arrSettings);
		
		$arrSettings[$key] = self::KEY_LOCAL.$localFilename;
		
		return($arrSettings);
	}
	
	
	/**
	 * 	 * locate local images, modify the url as "[local]name" prefix, change image filenames if same files
	 *   return array of image paths and names
	 */
	private function putLayoutImages(){
		
		$this->arrExportImages = array();
		$this->arrCacheImageFilenames = array();
		
		//modify addons data
		$exportFunc = array($this,"modifyAddons_exportImages");
		$this->objLayout->modifyGridDataAddons($exportFunc);
		
		//modify settings objects for BG images
		
		$exportFunc = array($this,"modifySettings_exportImages");
		$this->objLayout->modifyLayoutElementsSettings($exportFunc);
		
		//copy images
		foreach($this->arrExportImages as $arrImage){
			$sourceFilepath = $arrImage["path"];
			
			if(is_file($sourceFilepath) == false)
				UniteFunctionsUC::throwError("Image file: $sourceFilepath not found!");
			
			$filename = $arrImage["save_filename"];
			$destFilepath = $this->pathExportLayoutImages.$filename;
			
			copy($sourceFilepath, $destFilepath);
		}
		
	}
	
	
	private function a_________________IMPORT_____________(){}
		
	/**
	 * import layout by content
	 */
	private function importLayoutByContent($content, $layoutID = null){
		
		$objLayouts = new UniteCreatorLayouts();
		
		if(empty($this->objLayout))
			$this->objLayout = new UniteCreatorLayout();
		
		$arrLayout = @json_decode($content);
		
		if(empty($arrLayout))
			UniteFunctionsUC::throwError("Wrong file format");
		
		$arrLayout = UniteFunctionsUC::convertStdClassToArray($arrLayout);
		
		
		if(empty($layoutID)){	//new layout
			
			$title = UniteFunctionsUC::getVal($arrLayout, "title");
			$arrLayout["title"] = $objLayouts->getUniqueTitle($title);
			$this->lastImportID = $this->objLayout->createLayoutInDB($arrLayout);
			
		}else{  //existing layout
			
			unset($arrLayout["title"]);
			
			$this->objLayout->initByID($layoutID);
			$this->objLayout->updateLayoutInDB($arrLayout);
			
			$this->lastImportID = $layoutID;
			
		}
		
	}
	
	
	/**
	 * import layout txt file
	 */
	private function importTxtFile($filepath, $layoutID = null){
		
		$content = file_get_contents($filepath);
		
		if(empty($content))
			UniteFunctionsUC::throwError("layout file content don't found");
		
		$this->importLayoutByContent($content, $layoutID);
	}
	
	
	/**
	 * prepare import folders for unzipping
	 */
	private function prepareImportFolders(){
		
		//prepare import folder
		$this->prepareImportFolders_globalImport();		
		
		//prepare base folder
		$pathImportBase = $this->pathImport."single_layout/";
		UniteFunctionsUC::mkdirValidate($pathImportBase, "import layout base");
		
		UniteFunctionsUC::deleteDir($pathImportBase, false);
		
		//create index.html
		UniteFunctionsUC::writeFile("", $pathImportBase."index.html");
		
		
		//create layout path
		
		self::$serial++;
		
		$this->pathImportLayout = $pathImportBase."layout_".self::$serial."_".UniteFunctionsUC::getRandomString(10)."/";
		UniteFunctionsUC::mkdirValidate($this->pathImportLayout, "import layout");
		
		//don't create those paths
		$this->pathImportLayoutAddons = $this->pathImportLayout."addons/";
		$this->pathImportLayoutImages = $this->pathImportLayout."images/";
		
		
	}
	
	
	/**
	 * unpack import addon from temp file
	 */
	private function extractImportLayoutFile($filepath){
		
		$zip = new UniteZipUC();
		$extracted = $zip->extract($filepath, $this->pathImportLayout);
		
		if($extracted == false)
			UniteFunctionsUC::throwError("The import layout zip didn't extracted");
	
	}
	
	/**
	 * import layout txt
	 * $layoutID = existign layotu to import
	 */
	protected function importLayoutTxtFromZip($layoutID = null){
		
		$filepathLayout = $this->pathImportLayout."layout.txt";
		UniteFunctionsUC::validateFilepath($filepathLayout,"layout.txt");
		
		$content = file_get_contents($filepathLayout);
		
		$this->importLayoutByContent($content, $layoutID);
		
		$this->objLayout->initByID($this->lastImportID);
	}
	
	
	/**
	 * import layout addons
	 */
	private function importLayutAddons($overwriteAddons = false){
		
		$exporterAddons = new UniteCreatorExporter();
		
		//crate temp cat
		$exporterAddons->setMustImportAddonType($this->addonsType);
		$logText = $exporterAddons->importAddonsFromFolder($this->pathImportLayoutAddons, null, $overwriteAddons);
		
		return($logText);
	}
	
	
	/**
	 * import zip file
	 * $layoutID - import to layout
	 */
	protected function importZipFile($filepath, $layoutID = null, $overwriteAddons = false){
	
		try{
						
			$this->objLayout = new UniteCreatorLayout();
	
			$this->prepareImportFolders();
			$this->extractImportLayoutFile($filepath);
			$this->importLayoutTxtFromZip($layoutID);
			$logText = $this->importLayutAddons($overwriteAddons);
			$this->importLayoutImages();
			
		}catch(Exception $e){
	
			$isLayoutInited = $this->objLayout->isInited();
			if($isLayoutInited)
				$this->objLayout->delete();
	
			throw $e;
		}
	
	}
	
	
	/**
	 * import layout
	 * layoutID to import to
	 */
	public function import($arrFile, $layoutID=null, $overwriteAddons = false){
		
		if(is_string($arrFile))
			$filepath = $arrFile;
		else
			$filepath = UniteFunctionsUC::getVal($arrFile, "tmp_name");
		
		if(empty($filepath))
			UniteFunctionsUC::throwError("layout filepath not found");
		
		//get extension
		if(is_array($arrFile)){
			$filename = UniteFunctionsUC::getVal($arrFile, "name");
			$info = pathinfo($filename);
		}else{
			$info = pathinfo($filepath);
		}
		
		$ext = UniteFunctionsUC::getVal($info, "extension");
		$ext = strtolower($ext);
		
		
		switch($ext){
			case "txt":
				$this->importTxtFile($filepath, $layoutID);
				break;
			case "zip":
				$this->importZipFile($filepath, $layoutID, $overwriteAddons);
				break;
			default:
				UniteFunctionsUC::throwError("Wrong file: $filename");
			break;
		}
		
		return($this->lastImportID);
	}
	
	
	private function a_____________IMPORT_IMAGES_____________(){}
	
	
	/**
	 * get arr images to import
	 */
	private function importLayoutImages_getSourceImages(){
		
		$arrImages = array();
		
		$arrFiles = scandir($this->pathImportLayoutImages);
		foreach($arrFiles as $file){
			if($file == ".." || $file == ".")
				continue;
		
			//filter only images
			$info = pathinfo($file);
			$ext = UniteFunctionsUC::getVal($info, "extension");
			$ext = strtolower($ext);
			switch($ext){
				case "jpg":
				case "png":
				case "jpeg":
				case "gif":
				case "svg":
					break;
				default:
					continue(2);
				break;
			}
		
			$filepath = $this->pathImportLayoutImages.$file;
		
			$arrImages[$file] = array(
								 "filename"=>$file,
								 "source"=>$filepath
							);
		}
		
		$this->arrImportImages = $arrImages;
	}
	
	
	/**
	 * get destanation image filepath
	 */
	private function importLayoutImages_getDestFilepath($path, $filename, $filepathSource){
		
		$filepathDest = $path.$filename;
		
		if(!file_exists($filepathDest))
			return($filepathDest);
		
		//sizes not the same - find name
		$newFilename = UniteFunctionsUC::findFreeFilepath($path, $filename, $filepathSource);
		
		$destFilepath = $path.$newFilename;
		
		return($destFilepath);
	}
	
	
	/**
	 * copy images
	 */
	protected function importLayoutImages_copyImages(){
		
		$pathDest = GlobalsUC::$path_images.self::IMAGES_IMPORT_FOLDER."/";
		UniteFunctionsUC::checkCreateDir($pathDest);
		
		//copy images, if not exists, change names
		foreach($this->arrImportImages as $key => $arrImage){
			
			$filename = $key;
			$source = $arrImage["source"];
			$destFilepath = $this->importLayoutImages_getDestFilepath($pathDest, $filename, $source);
			
			if(!file_exists($destFilepath)){
				$success = copy($source, $destFilepath);
				if($success == false)
					UniteFunctionsUC::throwError("file: $filename could not be copied to path: $pathDest");
			}
			
			$arrImage["dest"] = $destFilepath;
			
			$arrImage["url"] = HelperUC::pathToRelativeUrl($destFilepath);
			$arrImage["urlfull"] = HelperUC::pathToFullUrl($destFilepath);
			
			$this->arrImportImages[$key] = $arrImage;
		}
		
	}
	
	
	/**
	 * make some provider related actions after copied images
	 * function for override
	 */
	protected function importLayoutImages_processCopiedImages(){}
	
	
	/**
	 * get local image data, or null
	 */
	protected function getImportedImageData($urlImage){
		
		$pos = strpos($urlImage, self::KEY_LOCAL);
		$isLocal = ($pos !== false);
		if($isLocal == false)
			return(null);
		
		//get filename
		$localEnd = $pos + strlen(self::KEY_LOCAL);
		
		$filename = substr($urlImage, $localEnd);
		$filename = trim($filename);
		
		$arrImage = UniteFunctionsUC::getVal($this->arrImportImages, $filename);
		
		return($arrImage);
	}
	
	
	/**
	 * replace local image to url image
	 */
	protected function processConfigImage_import($urlImage){
		
		$pos = strpos($urlImage, self::KEY_LOCAL);
		$isLocal = ($pos !== false);
		if($isLocal == false)
			return($urlImage);
		
		//get filename
		$localEnd = $pos + strlen(self::KEY_LOCAL);
		
		$filename = substr($urlImage, $localEnd);
		$filename = trim($filename);
		
		$arrImage = UniteFunctionsUC::getVal($this->arrImportImages, $filename);
		
		if(empty($arrImage))
			UniteFunctionsUC::throwError("Local image: $filename not found");
		
		//check if exists image id
		$imageID = UniteFunctionsUC::getVal($arrImage, "imageid");
		
		if(!empty($imageID))
			return($imageID);
		
		$url = UniteFunctionsUC::getVal($arrImage, "url");
		
		UniteFunctionsUC::validateNotEmpty($url,"url for image: $filename");
		
		return($url);
	}
	
	
	/**
	 * import images in layout settings
	 */
	public function modifySettings_importImages($arrSettings, $type){
		
		$key = "bg_image_url";
		
		$urlImage = UniteFunctionsUC::getVal($arrSettings, $key);
		if(empty($urlImage))
			return($arrSettings);
		
		$urlImage = $this->processConfigImage_import($urlImage);
		$arrSettings[$key] = $urlImage;
		
		return($arrSettings);
	}
	
	
	/**
	 * modify addons - export images
	 */
	public function modifyAddons_importImages($addonData){
	
		$addonData = $this->modifyAddons_exportImport($addonData, "import");
		
		return($addonData);
	}
	
	
	/**
	 * update layout after images copy
	 */
	protected function importLayoutImages_updateLayout(){
		
		$this->objLayout->validateInited();
		
		$importFunc = array($this,"modifyAddons_importImages");
		
		$importFuncSettings = array($this,"modifySettings_importImages");
		
		//modify addon data
		$this->objLayout->modifyGridDataAddons($importFunc);
		$this->objLayout->modifyLayoutElementsSettings($importFuncSettings);
		$this->objLayout->updateGridData();
		
	}
	
	
	/**
	 * import images
	 */
	protected function importLayoutImages(){
		
		$this->arrImportImages = array();
		
		//get source images array
		$this->importLayoutImages_getSourceImages();
		
				
		if(empty($this->arrImportImages))
			return(false);
		
		//copy images to dest folder
		$this->importLayoutImages_copyImages();
		$this->importLayoutImages_processCopiedImages();
		
		//update layout
		$this->importLayoutImages_updateLayout();
		
	}
	
	
	
	
}