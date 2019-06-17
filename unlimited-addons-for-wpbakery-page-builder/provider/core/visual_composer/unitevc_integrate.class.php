<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

/**
 * visual composer integration class
 *
 */
	class UniteVcIntegrateUC extends UniteCreatorPluginBase{
		
		const ADDONTYPE_VC = "vc";
		private static $arrCats = null;
		private $lastPostID = null;
		
		private $isMetaboxEnabled = false;
		private $isAddPreviewImage = true;
		private $previewThumbsType = "";
		private static $numPreviewImages = 0;
		private static $numAddons = 0;
		private static $isFrontEditor = false;
		private static $isRunInitVCOnce = false;
		
		const ACTION_ADD_METABOXES = "add_meta_boxes";
		const ACTION_ADMIN_FOOTER = "admin_footer";
		
		private $vcFolder;
		private $arrParentParams;	//params that brake from parent / child
		private static $isLogMemoryUsage = false;
		
		
		/**
		 * integrate visual composer
		 */
		public static function integrateVisualComposer(){
			
			try{
				self::$isLogMemoryUsage = HelperUC::getGeneralSetting("enable_memory_usage_test");
				self::$isLogMemoryUsage = UniteFunctionsUC::strToBool(self::$isLogMemoryUsage);
				
				//map addons
				$VCIntegrate = new UniteVcIntegrateUC();
				$VCIntegrate->initVCIntegration();
							
			}catch(Exception $e){
		
				HelperHtmlUC::outputException($e);
			}
			
		}
		
		
		/**
		 * return if visual composer functions exists
		 */
		public static function isVCExists(){
			
			$isExists = function_exists('vc_map');
						
			if($isExists)
				return(true);
			
			return(false);
		}
		
		/**
		 * get array of vc post types. get all post types available, 
		 * because some plugins could change it
		 */
		private static function getVcOriginalPostTypes(){
			
			if(function_exists("vc_user_access") == false)
				return(array());
			
			$arrOriginalTypes = array_keys( vc_user_access()->part( 'post_types' )->getAllCaps() );
			if(empty($arrOriginalTypes))
				$arrOriginalTypes = array();
			
			return($arrOriginalTypes);
		}
		
		
		/**
		 * check if visual composer in on current page
		 */
		public static function isVcOnAdminPage(){
			
			$screen = @get_current_screen();
			if(empty($screen))
				return(false);
			
			$includeInPostTypes = HelperUC::getGeneralSetting("vc_post_types");
			
			if($includeInPostTypes == "all_admin")
				return(true);
			
			$baseType = $screen->base;
			if($baseType != "post")
				return(false);
			
			
			//check if can be included in all pages
			if($includeInPostTypes == "all")
				return(true);
			
			$vcAction = UniteFunctionsUC::getGetVar("vc_action", "", UniteFunctionsUC::SANITIZE_KEY);
			if($vcAction == "vc_inline")
				return(true);
			
			if(function_exists("vc_editor_post_types") == false)
				return(false);
			
			$arrVcPostTypes = vc_editor_post_types();
			$arrVcPostTypes[] = "templatera";
			
			$found = in_array( get_post_type(), $arrVcPostTypes);
			
			//if not found, check in original post types
			if($found == false){
				$arrOriginalTypes = self::getVcOriginalPostTypes();
				$found = in_array( get_post_type(), $arrOriginalTypes);
			}
			
			
			return($found);	
		}
		
		
		/**
		 * return if vc addons enabled by general setting
		 */
		public static function isVCAddonsEnabled(){
			
			$isEnabled = HelperUC::getGeneralSetting("vc_enable");
			$isEnabled = UniteFunctionsUC::strToBool($isEnabled);
			
			return($isEnabled);
		}
		
		
		/**
		 * on add outside scripts
		 */
		public static function onAddOutsideScripts(){
			
			$isVCPage = self::isVcOnAdminPage();
			
			if($isVCPage == false)
				return(false);
			
			$isVCAddonsEnabled = self::isVCAddonsEnabled();
			
			if($isVCAddonsEnabled == false)
				return(false);
			
			
			self::onVCPagesScripts();
		}
		
		
		/**
		 * get client options script
		 */
		private static function getClientOptionsScript(){
			
			$options = array();
			$options["thumbs_type"] = HelperUC::getGeneralSetting("vc_page_thumbs_type");
			
			//if not enought preview images, don't show the thumbs mode at all
			if(self::$numPreviewImages < self::$numAddons / 2)
				$options["thumbs_type"] = "none";
			
			$options["is_front_editor"] = self::$isFrontEditor;
			
			$strOptions = UniteFunctionsUC::jsonEncodeForClientSide($options);
			
			$strClient = "		var g_ucVCOptions = $strOptions;";
			
			return($strClient);
		}
		
		
		/**
		 * put scripts on visual composer pages
		 */
		public static function onVCPagesScripts(){
			
			UniteCreatorAdmin::addScripts_settingsBase("nojqueryui");
			
			$globalJsOutput = HelperHtmlUC::getGlobalJsOutput();
			
			$jsVCOptions = self::getClientOptionsScript();
			$globalJsOutput .= "\n".$jsVCOptions;
			
			
			UniteProviderFunctionsUC::printCustomScript($globalJsOutput);
			
			HelperUC::addScriptAbsoluteUrl(GlobalsUC::$url_provider."assets/uc_general_settings.js", "uc_general_settings");
			HelperUC::addStyleAbsoluteUrl( GlobalsUC::$url_provider."assets/jquery-ui-custom-vc.css", "jquery-ui-custom-vc");
			HelperUC::addStyleAbsoluteUrl( GlobalsUC::$url_provider."assets/provider_admin.css", "uc_provider_admin");
			
			//dmp("get scripts");exit();
			
		}
		
		
		private function a______MAP_ADDONS_PARAMS_____(){}
		
		
		/**
		 * get child params from parent param
		 * where there are multiple params from one
		 */
		private function getChildParams($param){
			
			$objSettings = new UniteCreatorSettings();
			$objSettings->addByCreatorParam($param);
			
			$arrParams = $objSettings->getSettingsAsCreatorParams();
			
			return($arrParams);
		}
		
		
		/**
		 * get vc param from creator param
		 */
		private function getVCParam($param){
						
			$vcParam = $param;
			$vcParam["param_name"] = $vcParam["name"];
			$vcParam["heading"] = $vcParam["title"];
			
			$vcParam["value"] = UniteFunctionsUC::getVal($vcParam, "default_value");
						
			//validation
			if(empty($vcParam["type"]))
				return(null);
			
			if(empty($vcParam["param_name"]))
				return(null);
			
			if(isset($vcParam["admin_label"]))
				$vcParam["admin_label"] = UniteFunctionsUC::strToBool($vcParam["admin_label"]);
			
			switch($vcParam["type"]){
				case "uc_hr":
					$vcParam["heading"] = "<hr>";
				break;
			}
				
			return($vcParam);
		}
		
		
		/**
		 * convert params object to vc params object
		 */
		private function mapParams($arrParams){
			
			$vcParams = array();
			
			
			foreach($arrParams as $param){
				
				$paramType = $param["type"];
				$isMultipleSettingType = UniteCreatorSettings::isMultipleUCSettingType($paramType);
				
				
				//--- multiple type
				
				if($isMultipleSettingType){
					
					$arrChildParams = $this->getChildParams($param);
					
					foreach($arrChildParams as $childParam){
												
						$vcParamChild = $this->getVCParam($childParam);
						if(!empty($vcParamChild))
							$vcParams[] = $vcParamChild;
							
					}
										
					continue;
				}
				
				//--- normal type
				
				$vcParam = $this->getVCParam($param);
					
				if(!empty($vcParam))
					$vcParams[] = $vcParam;
				
			}
			
			
			
			return($vcParams);
		}
		
		
		/**
		 * map addon
		 */
		private function mapAddon($data){
			
			$name = UniteFunctionsUC::getVal($data, "name");
			$addonName = UniteFunctionsUC::getVal($data, "addon_name");
			$shortcode = "ucaddon_".$addonName;
			
			$category = UniteFunctionsUC::getVal($data, "category");
			$description = UniteFunctionsUC::getVal($data, "description");
			$class = "uc-addon";
			$params = UniteFunctionsUC::getVal($data, "params", array());
			$params = $this->mapParams($params);
			
			//exit();
			
			$settings = array();
			$settings["name"] = __($name, UNLIMITED_ADDONS_TEXTDOMAIN);
			$settings["base"] = $shortcode;
			$settings["addon_name"] = $addonName;
			
			$settings["class"] = $class;
			$settings["icon"] = UniteFunctionsUC::getVal($data, "icon");
			
			if($this->isAddPreviewImage == true)
				$settings["preview"] = UniteFunctionsUC::getVal($data, "preview");
			
			$settings["category"] = $category;
			$settings["description"] = __($description, UNLIMITED_ADDONS_TEXTDOMAIN);
			$settings["params"] = $params;
			 
			$settings["content_element"] = true;
			$settings["controls"] = "full";
			$settings["show_settings_on_create"] = true;
			
			
			//$settings["as_parent"] = array('except' => $shortcode);
			//$settings["is_container"] = true;
			//$settings["js_view"] = "VcColumnView";
			
			//create new class (validate alpha numeric shortcode
			$isAlphaNumeric = UniteFunctionsUC::isAlphaNumeric($shortcode);
			if($isAlphaNumeric == false)
				return(false);
			
			$code = "class WPBakeryShortCode_{$shortcode} extends UCVCAddonBase{};";
			eval($code);
			
			
			vc_map($settings);
			
		}
		
		
		/**
		 * get addon params
		 * 
		 */
		private function getAddonParams(UniteCreatorAddon $addon){
			
			$params = $addon->getProcessedMainParams();
			
			
			if(empty($params))
				$params = array();
			
			$hasSettings = !empty($params);
			$hasItems = $addon->isHasItems();
			$hasFonts = $addon->isFontsPanelEnabled();
			
			$addonName = $addon->getName();
			
			//add init setting param
			if($hasSettings == true){
				$paramInit = array();
				$paramInit["type"] = "uc_init_settings";
				$paramInit["title"] = "";
				$paramInit["name"] = "uc_init_settings";
				$paramInit["addon_name"] = $addonName;
				$params[] = $paramInit;
			}else{		//no settings - add static text
				
				$paramText = array();
				$paramText["type"] = "uc_statictext";
				$paramText["title"] = __("No Settings Available", UNLIMITED_ADDONS_TEXTDOMAIN);
				$paramText["name"] = "uc_text_nosettings";
				$paramText["addon_name"] = $addonName;
				$params[] = $paramText;
			}
			
			
			//add items param
			if($hasItems == true){
				$paramItems = array();
				$paramItems["type"] = "uc_items";
				$paramItems["title"] = "";
				$paramItems["name"] = "uc_items_data";
				$paramItems["group"] = __("Items", UNLIMITED_ADDONS_TEXTDOMAIN);
				$paramItems["addon_name"] = $addonName;
				
				$params[] = $paramItems;
			}
			
			//add fonts param
			if($hasFonts == true){
				$paramFonts = array();
				$paramFonts["type"] = "uc_fonts";
				$paramFonts["title"] = "";
				$paramFonts["name"] = "uc_fonts_data";
				$paramFonts["group"] = __("Fonts", UNLIMITED_ADDONS_TEXTDOMAIN);
				$paramFonts["addon_name"] = $addonName;
				
				$params[] = $paramFonts;
			}
			
			
			//add assets url to all params
			$urlAssets = $addon->getUrlAssets();
			foreach($params as $key=>$param){
				$param["url_assets"] = $urlAssets;
				$params[$key] = $param;
			}
			
			
			return($params);
		}
		
			
		
		/**
		 * map addon from onject
		 */
		private function mapAddonFromObject(UniteCreatorAddon $addon, $forceTitle = null){
			
			if(!empty($forceTitle))
				$catTitle = $forceTitle;
			else{
				$catTitle = $addon->getCatTitle();				
			}
			
			if(empty($catTitle))
				$catTitle = $this->vcFolder;
						
			$addonAlias = $addon->getAlias();
			
			//skip addon without alias
			if(empty($addonAlias))
				return(false);
			
			self::$numAddons++;
				
			$data = array();
			$data["name"] = $addon->getTitle();
			$data["addon_name"] = $addonAlias;
			$data["category"] = $catTitle;
			$data["description"] = $addon->getDescription();
			$data["icon"] = $addon->getUrlIcon();
			
			$urlPreview = $addon->getUrlPreview();
			
			$data["preview"] = $urlPreview;
			if(!empty($urlPreview))
				self::$numPreviewImages++;
			
			$params = $this->getAddonParams($addon);
			
			$data["params"] = $params;
			
			
			$this->mapAddon($data);
		}
		
		
		/**
		 * create custom params
		 */
		private function createCustomParams(){
			UniteVCCustomParams::createCustomParams();
		}
		
		/**
		 * put previews array
		 */
		private function putArrPreviews(){
			dmp("get previews");
			exit();
		}
				
		
		/**
		 * map all addons
		 */
		private function mapAllAddons(){
			
			if(self::$isLogMemoryUsage)
				HelperUC::logMemoryUsage("start mapping vc addons");
			
			$objAddons = new UniteCreatorAddons();
			
			$arrAddons = $objAddons->getCatAddons(null, false, "active", self::ADDONTYPE_VC);
						
			$forceTitle = null;
			$showAddonCats = HelperUC::getGeneralSetting("vc_group_cats");
			$showAddonCats = UniteFunctionsUC::strToBool($showAddonCats);
			
			if($showAddonCats == false)
				$forceTitle = $this->vcFolder;
			
			$isCheckMemory = HelperUC::getGeneralSetting("vc_check_memory");
			$isCheckMemory = UniteFunctionsUC::strToBool($isCheckMemory);
			
			$memoryReserve = HelperUC::getGeneralSetting("vc_memory_reserve");
			if(is_numeric($memoryReserve) == false)
				$memoryReserve = 32;
				
			$arrNames = array();
			$arrNotLoaded = array();
			foreach($arrAddons as $addon){
				
				$name = $addon->getName();
								
				if(isset($arrNames[$name]))
					continue;
					
				$arrNames[$name] = true;
				
				//check memory on register
				$isEnoughMemory = true;
				if($isCheckMemory == true)
					$isEnoughMemory = UniteFunctionsUC::isEnoughtPHPMemory($memoryReserve);
				
				if($isEnoughMemory == false){
					$arrNotLoaded[] = $name;
					
					if(self::$isLogMemoryUsage)
						HelperUC::logMemoryUsage("low memory, skip mapping vc addon: ".$name, true);
					
				}else{
					$this->mapAddonFromObject($addon, $forceTitle);
										
					if(self::$isLogMemoryUsage)
						HelperUC::logMemoryUsage("map vc addon: ".$name);
				}
			}
			
			
			//make notice of not loaded addons
			if(!empty($arrNotLoaded)){
				$numAddons = count($arrNotLoaded);
				$message = "Not enought memory to load all the addons to wpbakery page builder";
				$message .= "{$numAddons} addons not mapped. ";
				$message .= "Please deactivate or uninstall addons that unused or increase php memory limit in php.ini";
				HelperUC::addAdminNotice($message);
			}
			
			if(self::$isLogMemoryUsage){
				$numTotal = count($arrAddons);
				$numNotLoaded = count($arrNotLoaded);
				$numLoaded = $numTotal-$numNotLoaded;
				
				$message = "stop mapping vc addons. ";
				$message .= " loaded: $numLoaded";
				if(!empty($numNotLoaded))
					$message .= ", not loaded: $numNotLoaded";
				
				HelperUC::logMemoryUsage($message, true);
			}
			
		}
		
		private function a______IMPORT_LAYOUT_____(){}
		
		/**
		 * put import vc layout html
		 */
		private function putDialogImportVCLayoutHtml(){
			
			$dialogTitle = __("Import Layout to Visual Composer",UNLIMITED_ADDONS_TEXTDOMAIN);
			
			
			?>
			<div id="uc_dialog_import_layouts" class="unite-inputs" title="<?php echo $dialogTitle?>" style="display:none;">
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-inputs-label">
					<?php _e("Select vc layout export file (zip)", UNLIMITED_ADDONS_TEXTDOMAIN)?>:
				</div>
				
				<div class="unite-inputs-sap-small"></div>
				
				<form id="dialog_import_layouts_form" name="form_import_layouts">
					<input id="dialog_import_layouts_file" type="file" name="import_layout">
							
				</form>	
				
				<div class="unite-inputs-sap-double"></div>
				
				<div class="unite-inputs-label" >
					<label for="dialog_import_layouts_file_overwrite">
						<?php _e("Overwrite Addons", UNLIMITED_ADDONS_TEXTDOMAIN)?>:
					</label>
					<input type="checkbox" id="dialog_import_layouts_file_overwrite"></input>
				</div>
				
				
				<div class="unite-clear"></div>
				
				<?php 
					$prefix = "uc_dialog_import_layouts";
					$buttonTitle = __("Import VC Layout", UNLIMITED_ADDONS_TEXTDOMAIN);
					$loaderTitle = __("Uploading layout file...", UNLIMITED_ADDONS_TEXTDOMAIN);
					$successTitle = __("Layout Imported Successfully", UNLIMITED_ADDONS_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
				<div id="div_debug"></div>
				 
			</div>		
			
			<?php 
		}
				
		
		/**
		 * put metabox html
		 */
		public function putMegaboxImportHtml(){
			
			//init post title
			$initTitle = UniteFunctionsUC::getGetVar("uc_init_title", "", UniteFunctionsUC::SANITIZE_TEXT_FIELD);
			
			$addHtml = "";
			if(!empty($initTitle)){
				$initTitle = esc_attr($initTitle);
				$addHtml = " data-init_post_title=\"$initTitle\"";
			}
			
			?>
				<div class="uc-metabox-import-layout">
					<a id="uc_button_import_layout" <?php echo $addHtml?> href="javascript:void(0)" class="unite-button-secondary button-disabled unite-float-left mleft_20"><?php _e("Import Layout to Visual Composer", UNLIMITED_ADDONS_TEXTDOMAIN)?></a>
				</div>
			<?php
			
		}
		
		
		/**
		 * add meta boxes
		 */
		public function addPostImportMetabox(){
			
			$isVcOnPosts = $this->isVcOnAdminPage();
			if($isVcOnPosts == false){
				return(false);
			}
			
			add_meta_box("addon_library_import_metabox", __("Unlimited Addons", UNLIMITED_ADDONS_TEXTDOMAIN), array($this,"putMegaboxImportHtml"), null, "side");
			
		}
		
		/**
		 * add dialog import if needed
		 */
		public function onAdminFooter(){
			
			//put the footer content only on vc pages			
			
			$isVcOnPosts = $this->isVcOnAdminPage();
			if($isVcOnPosts == false)
				return(false);
			
			if($this->isMetaboxEnabled)
				$this->putDialogImportVCLayoutHtml();
			
			//remove me
			//$htmlDebug = HelperHtmlUC::getGlobalDebugDivs();
			//echo $htmlDebug;
		}
		
		
		/**
		 * update post
		 */
		private function updatePostWithLayoutContent($postID, $content){
			
			$arrPost = array();
			$arrPost["ID"] = $postID;
			$arrPost["post_content"] = $content;
			//$arrPost["post_title"] = $title;
			
			wp_update_post($arrPost);
		}
		
		
		/**
		 * import vc layout zip
		 */
		private function importVCLayout($data){
			
			$postID = UniteFunctionsUC::getVal($data, "postid");
			$postID = (int)$postID;
			
			$arrTempFile = UniteFunctionsUC::getVal($_FILES, "import_layout");
			
			$exporter = new UniteCreatorLayoutsExporterVC();
			
			$isOverwriteAddons = UniteFunctionsUC::getVal($data, "overwrite_addons");
			$isOverwriteAddons = UniteFunctionsUC::strToBool($isOverwriteAddons);
						
			$content = $exporter->importVCLayout($arrTempFile, $isOverwriteAddons);
			$importedTitle = $exporter->getLayoutTitle();
			
			$urlEditPost = "";
			
			if(!empty($postID)){
				
				//get title
				$title = UniteFunctionsUC::getVal($data, "title");
				
				$this->updatePostWithLayoutContent($postID, $content);
				$urlEditPost = get_edit_post_link($postID);
				if(empty($title))
					$urlEditPost .= "&uc_init_title=".$importedTitle;
			}
			
			
			$response = array();
			$response["url_reload"] = $urlEditPost;
			if(empty($urlEditPost))
				$response["content"] = $content;
			
			return($response);
		}
		
		
		/**
		 * on ajax action
		 */
		public function onAjaxAction($found, $action, $data){
			
			if($found == true)
				return(true);
			
			$webAPI = new UniteCreatorWebAPI();
			
			switch($action){
				case "import_vc_layout":
					$response = $this->importVCLayout($data);
					HelperUC::ajaxResponseData($response);
				break;
				case "activate_catalog_codecanyon":
					$data["type"] = "codecanyon";
					$webAPI->activateProductFromData($data);
					
					HelperUC::ajaxResponseSuccess("Product Activated");
				break;
				default:
					return(false);
				break;
			}
		
			return(true);
		}
		
		
		private function a______INIT_____(){}
		
		/**
		 * init some options
		 */
		private function initOptions(){
			
			//init vc folder
			$this->vcFolder = HelperUC::getGeneralSetting("vc_folder");
			if(empty($this->vcFolder))
				$this->vcFolder = "Unlimited Addons";
			
			$thumbsType = HelperUC::getGeneralSetting("vc_page_thumbs_type");
			$this->isAddPreviewImage = ($thumbsType != "none");
			$this->previewThumbsType = $thumbsType;
			
			//set if it's front editor by GET var
			$vcAction = UniteFunctionsUC::getGetVar("vc_action", "", UniteFunctionsUC::SANITIZE_KEY);
			if($vcAction == "vc_inline")
				self::$isFrontEditor = true;
			
		}
		
		
		/**
		 * init vc integration
		 */
		public function initVCIntegration(){
			
						
			if(self::$isRunInitVCOnce == true)
				return(false);
			
			self::$isRunInitVCOnce = true;
			
			if(self::isVCExists() == false)
				return(false);
			
			if(self::isVCAddonsEnabled() == false)
				return(false);
			
			
			$this->initOptions();
			
			//include vc related files
			require_once GlobalsUC::$pathProvider . 'core/visual_composer/unitevc_addon_shortcode.class.php';
			require_once GlobalsUC::$pathProvider . 'core/visual_composer/unitevc_custom_params.class.php';
			
			//add_action( 'wp_ajax_wpb_show_edit_form', array( &$this, 'build' ) );
			
			$objSettings = new UniteCreatorSettings();
			
			$this->createCustomParams();
			$this->mapAllAddons();
			
			
			//add metabox
			$addMetabox = HelperUC::getGeneralSetting("vc_put_import_metabox");
			$addMetabox = UniteFunctionsUC::strToBool($addMetabox);
			
			if($addMetabox == true){
				$this->isMetaboxEnabled = true;
				add_action( self::ACTION_ADD_METABOXES, array($this, "addPostImportMetabox") );
				add_action(self::ACTION_ADMIN_FOOTER, array($this,"onAdminFooter"));
			}
			
			$this->addFilter(self::FILTER_ADMIN_AJAX_ACTION, "onAjaxAction",10,3);
			
		}
		
	}

?>