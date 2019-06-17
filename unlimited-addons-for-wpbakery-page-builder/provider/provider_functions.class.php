<?php

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

define("UNLIMITED_ADDONS_TEXTDOMAIN","unlimited_addons");
define("ADDONLIBRARY_TEXTDOMAIN","addon_library");


class UniteProviderFunctionsUC{
	
	private static $arrScripts = array();
	private static $arrStyles = array();
	private static $arrInlineHtml = array();
	public static $tablePrefix = null;
	public static $tablePosts = null;
	public static $tablePostMeta = null;
	
	
	
	/**
	 * init base variables of the globals
	 */
	public static function initGlobalsBase(){
		global $wpdb;
		
		$tablePrefix = $wpdb->prefix;
		
		self::$tablePrefix = $tablePrefix;
		self::$tablePosts = $tablePrefix."posts";
		self::$tablePostMeta = $tablePrefix."postmeta";
				
		GlobalsUC::$table_addons = $tablePrefix.GlobalsUC::TABLE_ADDONS_NAME;
		GlobalsUC::$table_categories = $tablePrefix.GlobalsUC::TABLE_CATEGORIES_NAME;
		
		$pluginUrlAdminBase = "unitecreator";
		
		GlobalsUC::$pathPlugin = realpath(dirname(__FILE__)."/../")."/";
		
		$pluginName = basename(GlobalsUC::$pathPlugin);
		
		GlobalsUC::$path_base = ABSPATH;

		$arrUploadDir = wp_upload_dir();
		
		$uploadPath = $arrUploadDir["basedir"]."/";
		
		GlobalsUC::$path_images = $arrUploadDir["basedir"]."/";
		
		GlobalsUC::$path_cache = GlobalsUC::$pathPlugin."cache/";
		
		GlobalsUC::$url_base = site_url()."/";
		GlobalsUC::$urlPlugin = plugins_url($pluginName)."/";
		
		GlobalsUC::$url_component_client = "";
		GlobalsUC::$url_component_admin = admin_url()."admin.php?page=$pluginUrlAdminBase";
			
		GlobalsUC::$url_images = $arrUploadDir["baseurl"]."/";
		
		GlobalsUC::$url_ajax = admin_url('admin-ajax.php', "relative");
		GlobalsUC::$url_ajax_front = GlobalsUC::$url_ajax;
		
		GlobalsUC::$is_admin = self::isAdmin();
		
		GlobalsUC::$url_provider = GlobalsUC::$urlPlugin."provider/";
		
		GlobalsUC::$url_default_addon_icon = GlobalsUC::$url_provider."assets/images/icon_default_addon.png";
		
		self::setAssetsPath();
		
		GlobalsUC::$url_assets_libraries = GlobalsUC::$urlPlugin."assets_libraries/";
		
		//GlobalsUC::$view_default set in admin class
		
		GlobalsUC::$url_assets_internal = GlobalsUC::$urlPlugin."assets_internal/";
		
		GlobalsUC::$layoutShortcodeName = "uc_layout";
		GlobalsUC::$layoutsAddonType = "wp";
		
		GlobalsUC::$is_ssl = is_ssl();
				
		GlobalsUC::$enableWebCatalog = false;
	}
	
	
	/**
	 * set assets path
	*/
	private static function setAssetsPath(){
		
		//set assets path
		$pathBase = WP_CONTENT_DIR.'/';		
		
		$pathRelative = "uploads/";
		
		$urlBase = WP_CONTENT_URL;
		
		$pathUploads = $pathBase."uploads/";
		
		if(is_dir($pathUploads))
			$pathBase = $pathUploads;
		
		//take base path from multisite array if exists
		$arrUploads = wp_upload_dir();
		$uploadsBaseDir = UniteFunctionsUC::getVal($arrUploads, "basedir");
		$uploadsBaseUrl = UniteFunctionsUC::getVal($arrUploads, "baseurl");
		
		$dirAssets = "ac_assets";
		
		$urlBase = null;
		if(is_dir($uploadsBaseDir)){
			$pathBase = UniteFunctionsUC::addPathEndingSlash($uploadsBaseDir);
			$urlBase = UniteFunctionsUC::addPathEndingSlash($uploadsBaseUrl);
		}
		
		//make base path
		$pathAssets = $pathBase.$dirAssets."/";
		if(is_dir($pathAssets) == false)
			@mkdir($pathAssets);
		
		if(is_dir($pathAssets) == false)
			UniteFunctionsUC::throwError("Can't create folder: {$pathAssets}");
		
		//--- make url assets
		
		if(!empty($urlBase)){
			$urlAssets = $urlBase.$dirAssets."/";
		}else{
			$pathAssetsRelative = str_replace(WP_CONTENT_DIR,"",$pathAssets);
			$urlContent = site_url()."/wp-content";
			$urlAssets = $urlContent.$pathAssetsRelative;
		}
		
			
		GlobalsUC::$pathAssets = $pathAssets;
		GlobalsUC::$url_assets = $urlAssets;
	}
	
	
	
	/**
	 * is admin function
	 */
	public static function isAdmin(){
		
		$isAdmin = is_admin();
		
		return($isAdmin);
	}
	
	public static function a_____________SCRIPTS___________(){}
	
	/**
	 * add scripts and styles framework
	 * $specialSettings - (nojqueryui)
	 */
	public static function addScriptsFramework($specialSettings = ""){
		
		UniteFunctionsWPUC::addMediaUploadIncludes();
		
		//add jquery
		self::addAdminJQueryInclude();
		
		//add jquery ui
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-ui-widget");
		wp_enqueue_script("jquery-ui-dialog");
		
		//no jquery ui style
		if($specialSettings != "nojqueryui"){
			HelperUC::addStyle("jquery-ui.structure.min","jui-smoothness-structure","css/jui/new");
			HelperUC::addStyle("jquery-ui.theme.min","jui-smoothness-theme","css/jui/new");
		}
		
		
		if(function_exists("wp_enqueue_media"))
			wp_enqueue_media();
		
	}
	
	
	/**
	 * add jquery include
	 */
	public static function addAdminJQueryInclude(){
		
		wp_enqueue_script("jquery");
		
	}
	
	
	
	/**
	 *
	 * register script
	 */
	public static function addScript($handle, $url, $inFooter = false){
	
		if(empty($url))
			UniteFunctionsUC::throwError("empty script url, handle: $handle");
		
		wp_register_script($handle , $url, array(), false, $inFooter);
		wp_enqueue_script($handle);
	}
	
	
	/**
	 *
	 * register script
	 */
	public static function addStyle($handle, $url){
	
		if(empty($url))
			UniteFunctionsUC::throwError("empty style url, handle: $handle");
	
		wp_register_style($handle , $url);
		wp_enqueue_style($handle);
			
	}
	
	
	/**
	 * print some script at some place in the page
	 */
	public static function printCustomScript($script, $hardCoded = false){
		
		if($hardCoded == false)
			self::$arrScripts[] = $script;
		else
			echo "<script type='text/javascript'>{$script}</script>";
	
	}
	
	
	/**
	 * print custom style
	 */
	public static function printCustomStyle($style, $hardCoded = false){
		
		if($hardCoded == false)
			self::$arrStyles[] = $style;
		else
			echo "<style type='text/css'>{$style}</style>";
		
	}
	
	
	/**
	* get all custom scrips
	*/
	public static function getCustomScripts(){
		
		return(self::$arrScripts);
	}
	
	/**
	 * get custom styles
	 */
	public static function getCustomStyles(){
		
		return(self::$arrStyles);
	}
	
	
	public static function a_____________SANITIZE___________(){}
	
	
	/**
	 * filter variable
	 */
	public static function sanitizeVar($var, $type){
	
		switch($type){
			case UniteFunctionsUC::SANITIZE_ID:
				if(empty($var))
					return("");
		
				$var = (int)$var;
				$var = abs($var);
	
				if($var == 0)
					return("");
			
			break;
			case UniteFunctionsUC::SANITIZE_KEY:
				$var = sanitize_key($var);
			break;
			case UniteFunctionsUC::SANITIZE_TEXT_FIELD:
				$var = sanitize_text_field($var);
			break;
			case UniteFunctionsUC::SANITIZE_NOTHING:
			break;
			default:
				UniteFunctionsUC::throwError("Wrong sanitize type: " . $type);
			break;
		}
	
		return($var);
	}
	
	
	
	public static function a_____________GENERAL___________(){}
		
	
	
	/**
	 * get image url from image id
	 */
	public static function getImageUrlFromImageID($imageID){
		
		$urlImage = UniteFunctionsWPUC::getUrlAttachmentImage($imageID);
				
		return($urlImage);
	}
	
	
	/**
	 * get image url from image id
	 */
	public static function getThumbUrlFromImageID($imageID, $size = null){
		if($size == null)
			$size = UniteFunctionsWPUC::THUMB_MEDIUM;
		
		switch($size){
			case GlobalsUC::THUMB_SIZE_NORMAL:
				$size = UniteFunctionsWPUC::THUMB_MEDIUM;
			break;
			case GlobalsUC::THUMB_SIZE_LARGE:
				$size = UniteFunctionsWPUC::THUMB_LARGE;
			break;
		}
		
		$urlThumb = UniteFunctionsWPUC::getUrlAttachmentImage($imageID, $size);
		
		return($urlThumb);
	}
	
	/**
	 * get image id from url
	 * if not, return null or 0
	 */
	public static function getImageIDFromUrl($urlImage){
		
		$imageID = UniteFunctionsWPUC::getAttachmentIDFromImageUrl($urlImage);
		
		return($imageID);
	}
	
	
	/**
	 * strip slashes from ajax input data
	 */
	public static function normalizeAjaxInputData($arrData){
		
		if(!is_array($arrData))
			return($arrData);
		
		foreach($arrData as $key=>$item){
			
			if(is_string($item))
				$arrData[$key] = stripslashes($item);
			
			//second level
			if(is_array($item)){
				
				foreach($item as $subkey=>$subitem){
					if(is_string($subitem))
						$arrData[$key][$subkey] = stripslashes($subitem);
					
					//third level
					if(is_array($subitem)){

						foreach($subitem as $thirdkey=>$thirdItem){
							if(is_string($thirdItem))
								$arrData[$key][$subkey][$thirdkey] = stripslashes($thirdItem);
						}
					
					}
					
				}
			}
			
		}
		
		return($arrData);
	}
	
	
	/**
	 * put footer text line
	 */
	public static function putFooterTextLine(){
		?>
			&copy; <?php _e("All rights reserved",UNLIMITED_ADDONS_TEXTDOMAIN)?>, <a href="http://codecanyon.net/user/unitecms" target="_blank">Unite CMS</a>. &nbsp;&nbsp;		
		<?php
	}
	
	
	/**
	 * add jquery include
	 */
	public static function addjQueryInclude($app="", $urljQuery = null){
		wp_enqueue_script("jquery");
	}

		
	
	/**
	 * print some custom html to the page
	 */
	public static function printInlineHtml($html){
		self::$arrInlineHtml[] = $html;
	}
	
	
	
	/**
	 * get custom html
	 */
	public static function getInlineHtml(){
		
		return(self::$arrInlineHtml);
	}
	
	
	/**
	 * add system contsant data to template engine
	 */
	public static function addSystemConstantData($data){
				
		return($data);
	}
	
	
	/**
	 * get option
	 */
	public static function getOption($option, $default = false, $supportMultisite = false){
	
		if($supportMultisite == true && is_multisite())
			return(get_site_option($option, $default));
		else
			return get_option($option, $default);
	
	}
	
	/**
	 * get option
	 */
	public static function getTransient($option, $supportMultisite = false){
	    
		if($supportMultisite == true && is_multisite())
			return(get_site_transient($option));
		else
			return get_transient($option);
	
	}

	/**
	 * update option
	 */
	public static function updateOption($option, $value, $supportMultisite = false){
	
		if($supportMultisite == true && is_multisite()){
			update_site_option($option, $value);
		}else
			update_option($option, $value);
	
	}
	
	
	/**
	 * update option
	 */
	public static function setTransient($option, $value, $expiration, $supportMultisite = false){
	
		if($supportMultisite == true && is_multisite()){
			set_site_transient($transient, $value, $expiration);
		}else
			set_transient($option, $value, $expiration);
			
	}
	
	
	/**
	 * put addon view add html, put activate catalog button
	 */
	public static function putAddonViewAddHtml(){
		
		if(GlobalsUC::$enableWebCatalog == false)
			return(false);
		
		?>
		<div class="uc-view-addons-bar">
			
			<a id="uc_button_activate_catalog" href="javascript:void(0)" class="uc-button-activate"><?php _e("Activate Catalog", ADDONLIBRARY_TEXTDOMAIN)?></a>
			
		</div>
		
		<div id="uc_dialog_activate_catalog" title="<?php _e("Activate Catalog", ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;">
			
			<div class="unite-dialog-top"></div>
					
				<?php _e("Enter Purchase Code", ADDONLIBRARY_TEXTDOMAIN)?>: 
				<br>
				<input type="text" id="uc_dialog_activate_catalog_text" value="" class="unite-input-regular">
				
				<?php 
					$prefix = "uc_dialog_activate_catalog";
					$buttonTitle = __("Activate Catalog", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Activating...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Catalog Activated! refreshing the page...", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
		</div>
		
		<?php 
	}
	
	
	/**
	 * get nonce (for protection)
	 */
	public static function getNonce(){
	
		$nonce = wp_create_nonce("addonlibrary_actions");
	
		return($nonce);
	}
	
	/**
	 * veryfy nonce
	 */
	public static function verifyNonce($nonce){
	
		$verified = wp_verify_nonce($nonce, "addonlibrary_actions");
		if($verified == false)
			UniteFunctionsUC::throwError("Action security failed, please repeat action");
	
	}
	
	
	/**
	 * put helper editor to help init other editors that has put by ajax
	 */
	public static function putInitHelperHtmlEditor(){
		
		?>
		<div style="display:none">
			
			<?php 
				wp_editor("init helper editor","uc_editor_helper");
			?>
			
		</div>
		<?php 
		
	}
	
	
	private static function a__________UPDATE_PLUGIN_________(){}
	
	
	/**
	 * put update plugin button
	 */
	public static function putUpdatePluginHtml(){
		?>
		<!-- update plugin button -->
		
		<div class="uc-update-plugin-wrapper">
			<a id="uc_button_update_plugin" class="unite-button-primary" href="javascript:void(0)" ><?php _e("Update Plugin", UNLIMITED_ADDONS_TEXTDOMAIN)?></a>
		</div>
		
		<!-- dialog update -->
		
		<div id="dialog_update_plugin" title="<?php _e("Update Unlimited Addons Plugin",UNLIMITED_ADDONS_TEXTDOMAIN)?>" style="display:none;">	
			
			<div class="unite-dialog-title"><?php _e("Update Unlimited Addons Plugin",UNLIMITED_ADDONS_TEXTDOMAIN)?>:</div>	
			
			<div class="unite-dialog-desc">
				<?php _e("To update the plugin please select the plugin install package.",UNLIMITED_ADDONS_TEXTDOMAIN) ?>		
			
			<br>
		
			<?php _e("The files will be overwriten", UNLIMITED_ADDONS_TEXTDOMAIN)?>
		
			<br> <?php _e("File example: unlimited-addons-for-wpbakery-page-builder1.0.13.zip",UNLIMITED_ADDONS_TEXTDOMAIN)?>	</div>	
			
			<br>	
		
			<form action="<?php echo GlobalsUC::$url_ajax?>" enctype="multipart/form-data" method="post">
			
				<input type="hidden" name="action" value="unitecreator_ajax_action">		
				<input type="hidden" name="client_action" value="update_plugin">		
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce("addonlibrary_actions"); ?>">
				<?php _e("Choose the update file:",UNLIMITED_ADDONS_TEXTDOMAIN)?>
				<br><br>
				
				<input type="file" name="update_file" class="unite-dialog-fileinput">		
				
				<br><br>
			
				<input type="submit" class='unite-button-primary' value="<?php _e("Update Plugin",UNLIMITED_ADDONS_TEXTDOMAIN)?>">	
			</form>
		
		</div>

		<?php 
	}
	
	
	/**
	 * check that inner zip exists, and unpack it if do
	 	*/
	private static function updatePlugin_checkUnpackInnerZip($pathUpdate, $zipFilename){
	
		$arrFiles = UniteFunctionsUC::getFileList($pathUpdate);
	
		if(empty($arrFiles))
			return(false);
	
		//get inner file
		$filenameInner = null;
		foreach($arrFiles as $innerFile){
			if($innerFile != $zipFilename)
				$filenameInner = $innerFile;
		}
	
		if(empty($filenameInner))
			return(false);
	
		//check if internal file is zip
		$info = pathinfo($filenameInner);
		$ext = UniteFunctionsUC::getVal($info, "extension");
		if($ext != "zip")
			return(false);
	
		$filepathInner = $pathUpdate.$filenameInner;
	
		if(file_exists($filepathInner) == false)
			return(false);
	
		dmp("detected inner zip file. unpacking...");
	
		//check if zip exists
		$zip = new UniteZipUG();
	
		if(function_exists("unzip_file") == true){
			WP_Filesystem();
			$response = unzip_file($filepathInner, $pathUpdate);
		}
		else
			$zip->extract($filepathInner, $pathUpdate);
	
	}
	
	
	// --------- uploaded file code to message
/**
 * 
 * get message of upload file code
 */
  private static function uploadFileCodeToMessage($code) 
    { 
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break; 

            default: 
                $message = "Unknown upload error"; 
                break; 
        } 
        return $message; 
    } 	
	
	
	/**
	 *
	 * Update Plugin
	 */
	public static function updatePlugin(){
		
		try{
			
			//verify nonce:
			$nonce = UniteFunctionsUC::getPostVariable("nonce","",UniteFunctionsUC::SANITIZE_NOTHING);
			$isVerified = wp_verify_nonce($nonce, "addonlibrary_actions");
			
			if($isVerified == false)
				UniteFunctionsUC::throwError("Security error");
			
			$linkBack = HelperUC::getViewUrl_Default("provider_action=run_after_update");
			$htmlLinkBack = HelperHtmlUC::getHtmlLink($linkBack, "Go Back");
			
			//check if zip exists
			$zip = new UniteZipUC();
			
			if(function_exists("unzip_file") == false){
	
				if( UniteZipUG::isZipExists() == false)
					UniteFunctionsUC::throwError("The ZipArchive php extension not exists, can't extract the update file. Please turn it on in php ini.");
			}
						
			dmp("Update in progress...");
			
			$arrFiles = UniteFunctionsUC::getVal($_FILES, "update_file");
			
			if(empty($arrFiles))
				UniteFunctionsUC::throwError("Update file don't found.");
			
			$error = UniteFunctionsUC::getVal($arrFiles, "error");
			if(!empty($error)){
				$message = self::uploadFileCodeToMessage($error);
				UniteFunctionsUC::throwError($message);
			}
				
			$filename = UniteFunctionsUC::getVal($arrFiles, "name");
	
			if(empty($filename))
				UniteFunctionsIG::throwError("Update filename not found.");
	
			$fileType = UniteFunctionsUC::getVal($arrFiles, "type");
	
			$fileType = strtolower($fileType);
	
			$arrMimeTypes = array();
			$arrMimeTypes[] = "application/zip";
			$arrMimeTypes[] = "application/x-zip";
			$arrMimeTypes[] = "application/x-zip-compressed";
			$arrMimeTypes[] = "application/octet-stream";
			$arrMimeTypes[] = "application/x-compress";
			$arrMimeTypes[] = "application/x-compressed";
			$arrMimeTypes[] = "multipart/x-zip";
	
			if(in_array($fileType, $arrMimeTypes) == false)
				UniteFunctionsUC::throwError("The file uploaded is not zip.");
	
			$filepathTemp = UniteFunctionsUC::getVal($arrFiles, "tmp_name");
			if(file_exists($filepathTemp) == false)
				UniteFunctionsUC::throwError("Can't find the uploaded file.");
			
			
			//crate temp folder
			$pathTemp = GlobalsUC::$pathPlugin."temp/";
			UniteFunctionsUC::checkCreateDir($pathTemp);
			
			//create the update folder
			$pathUpdate = $pathTemp."update_extract/";
			UniteFunctionsUC::checkCreateDir($pathUpdate);
						
			if(!is_dir($pathUpdate))
				UniteFunctionsUC::throwError("Could not create temp extract path");
						
			//remove all files in the update folder
			$arrNotDeleted = UniteFunctionsUC::deleteDir($pathUpdate, false);
	
			if(!empty($arrNotDeleted)){
				$strNotDeleted = print_r($arrNotDeleted,true);
				UniteFunctionsUC::throwError("Could not delete those files from the update folder: $strNotDeleted");
			}
						
			//copy the zip file.
			$filepathZip = $pathUpdate.$filename;
	
			$success = move_uploaded_file($filepathTemp, $filepathZip);
			if($success == false)
				UniteFunctionsUC::throwError("Can't move the uploaded file here: ".$filepathZip.".");
						
			//extract files:
			if(function_exists("unzip_file") == true){
				WP_Filesystem();
				$response = unzip_file($filepathZip, $pathUpdate);
			}
			else
				$zip->extract($filepathZip, $pathUpdate);
				
			//check for internal zip in case that cocecanyon original zip was uploaded
			self::updatePlugin_checkUnpackInnerZip($pathUpdate, $filename);
						
			//get extracted folder
			$arrFolders = UniteFunctionsUC::getDirList($pathUpdate);
			if(empty($arrFolders))
				UniteFunctionsUC::throwError("The update folder is not extracted");
	
			//get product folder
			$productFolder = null;
	
			if(count($arrFolders) == 1)
				$productFolder = $arrFolders[0];
			else{
				foreach($arrFolders as $folder){
					if($folder != "documentation")
						$productFolder = $folder;
				}
			}
				
			if(empty($productFolder))
				UniteFunctionsUC::throwError("Wrong product folder.");
	
			$pathUpdateProduct = $pathUpdate.$productFolder."/";
			
			//check some file in folder to validate it's the real one:
			$checkFilepath = $pathUpdateProduct."unitecreator_admin.php";
			
			if(file_exists($checkFilepath) == false)
				UniteFunctionsUC::throwError("Wrong update extracted folder. The file: ".$checkFilepath." not found.");
	
			//copy the plugin without the captions file.
			$pathOriginalPlugin = GlobalsUC::$pathPlugin;
	
			$arrBlackList = array();
			UniteFunctionsUC::copyDir($pathUpdateProduct, $pathOriginalPlugin,"",$arrBlackList);
	
			//delete the update
			UniteFunctionsUC::deleteDir($pathUpdate);
			
			dmp("Updated Successfully, redirecting...");
			echo "<script>location.href='$linkBack'</script>";
	
	}catch(Exception $e){
	
		//remove all files in the update folder
		if(isset($pathUpdate) && !empty($pathUpdate))
			UniteFunctionsUC::deleteDir($pathUpdate);
		
		$message = $e->getMessage();
		$message .= " <br> Please update the plugin manually via the ftp";
		echo "<div style='color:#B80A0A;font-size:18px;'><b>Update Error: </b> $message</div><br>";
		echo $htmlLinkBack;
		exit();
	}
	
	}
	
	
	
	
	public static function a_________ACTIONS_FILTERS_____________(){}
	
	
	/**
	 * add filter
	 */
	public static function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1 ){
		add_filter($tag, $function_to_add, $priority, $accepted_args);
	}
	
	
	/**
	 * wrap shortcode
	 */
	public static function wrapShortcode($shortcode){
		$shortcode = "[".$shortcode."]";
		return($shortcode);
	}
	
	
	/**
	 * apply filters
	 */
	public static function applyFilters($func, $value){
		$args = func_get_args();
		
		return call_user_func_array("apply_filters",$args);
	}
	
	
	/**
	 * add action function
	 */
	public static function addAction($action, $func){
		$args = func_get_args();
		
		call_user_func_array("add_action", $args);
	}
		
	
	/**
	 * do action
	 */
	public static function doAction($tag){
		$args = func_get_args();
		
		call_user_func_array("do_action", $args);
	}
		
	
	/**
	 * validate addons type
	 */
	public static function validateDataAddonsType($type, $data){
				
		switch($type){
			case "vc":
			case "elementor":
			case "":
			break;
			default:
				dmp("no type found in data!");
				dmp($data);
				exit();
			break;
		}
				
	}		
	
}
?>