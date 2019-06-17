<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


/**
 * 
 * creator helper functions class
 *
 */
	class HelperHtmlUC extends UniteHelperBaseUC{
		
		private static $isGlobalJSPut = false;

		
		protected function __________GETTERS__________(){}

		
		/**
		 *
		 * get link html
		 */
		public static function getHtmlLink($link,$text,$id="",$class="", $isNewWindow = false){
		
			if(!empty($class))
				$class = " class='$class'";
		
			if(!empty($id))
				$id = " id='$id'";
		
			$htmlAdd = "";
			if($isNewWindow == true)
				$htmlAdd = ' target="_blank"';
		
			$html = "<a href=\"$link\"".$id.$class.$htmlAdd.">$text</a>";
			return($html);
		}

		
		/**
		 *
		 * get select from array
		 */
		public static function getHTMLSelect($arr,$default="",$htmlParams="",$assoc = false, $addData = null, $addDataText = null){
		
			$html = "<select $htmlParams>";
			//add first item
			if($addData == "not_chosen"){
				$selected = "";
				$default = trim($default);
				if(empty($default))
					$selected = " selected ";
					
				$itemText = $addDataText;
				if(empty($itemText))
					$itemText = "[".__("not chosen", ADDONLIBRARY_TEXTDOMAIN)."]";
					
				$html .= "<option $selected value=''>{$itemText}</option>";
			}
			
			foreach($arr as $key=>$item){
				$selected = "";
		
				if($assoc == false){
					if($item == $default) 
						$selected = " selected ";
				}
				else{
					if(trim($key) == trim($default))
						$selected = " selected ";
				}
				
				$addHtml = "";
				if(strpos($key, "html_select_sap") !== false)
					$addHtml = " disabled";
				
				if($assoc == true)
					$html .= "<option $selected value='$key' $addHtml>$item</option>";
				else
					$html .= "<option $selected value='$item' $addHtml>$item</option>";
			}
			$html.= "</select>";
			return($html);
		}
		
		
		/**
		 * get row of addons table
		 */
		public static function getTableAddonsRow($addonID, $title){
			
			$editLink = HelperUC::getViewUrl_EditAddon($addonID);
			
			$htmlTitle = htmlspecialchars($title);
			
			$html = "<tr>\n";
			$html.= "<td><a href='{$editLink}'>{$title}</a></td>\n";
			$html.= "	<td>\n";
			$html.= " 	  <a href='{$editLink}' class='unite-button-secondary float_left mleft_15'>". __("Edit",ADDONLIBRARY_TEXTDOMAIN) . "</a>\n";
			$html.= "		<a href='javascript:void(0)' data-addonid='{$addonID}' class='uc-button-delete unite-button-secondary float_left mleft_15'>".__("Delete",ADDONLIBRARY_TEXTDOMAIN)."</a>";
			$html.= "		<span class='loader_text uc-loader-delete mleft_10' style='display:none'>" . __("Deleting", ADDONLIBRARY_TEXTDOMAIN) . "</span>";
			$html.= "		<a href='javascript:void(0)' data-addonid='{$addonID}' class='uc-button-duplicate unite-button-secondary float_left mleft_15'>" . __("Duplicate",ADDONLIBRARY_TEXTDOMAIN)."</a>\n";
			$html.= "		<span class='loader_text uc-loader-duplicate mleft_10' style='display:none'>" . __("Duplicating", ADDONLIBRARY_TEXTDOMAIN) . "</span>";
			$html.= "		<a href='javascript:void(0)' data-addonid='{$addonID}' data-title='{$htmlTitle}' class='uc-button-savelibrary unite-button-secondary float_left mleft_15'>" . __("Save To Library",ADDONLIBRARY_TEXTDOMAIN)."</a>\n";
			$html.= "		<span class='loader_text uc-loader-save mleft_10' style='display:none'>" . __("Saving to library", ADDONLIBRARY_TEXTDOMAIN) . "</span>";
			$html.= "	</td>\n";
			$html.= "	</tr>\n";
			
			return($html);
		}

		
		
		
		
		/**
		 * get global js output for plugin pages
		 */
		public static function getGlobalJsOutput(){
			
			//insure that this function run only once
			if(self::$isGlobalJSPut == true)
				return("");
			
			self::$isGlobalJSPut = true;
			
			$jsArrayText = UniteFunctionsUC::phpArrayToJsArrayText(GlobalsUC::$arrClientSideText,"				");
			
			//prepare assets path
			$pathAssets = HelperUC::pathToRelative(GlobalsUC::$pathAssets, false);
			$pathAssets = urlencode($pathAssets);
			
			//check catalog
			$objWebAPI = new UniteCreatorWebAPI();
			$isNeedCheckCatalog = $objWebAPI->isTimeToCheckCatalog();
			
			$arrGeneralSettings = array();
			$arrGeneralSettings = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_CLIENTSIDE_GENERAL_SETTINGS, $arrGeneralSettings);
			
			$strGeneralSettings = UniteFunctionsUC::jsonEncodeForClientSide($arrGeneralSettings);
			
			$js = "";
			$js .= self::TAB2.'var g_pluginNameUC = "'.GlobalsUC::PLUGIN_NAME.'";'.self::BR;
			$js .= self::TAB2.'var g_pathAssetsUC = decodeURIComponent("'.$pathAssets.'");'.self::BR;
			$js .= self::TAB2.'var g_urlAjaxActionsUC = "'.GlobalsUC::$url_ajax.'";'.self::BR;
			$js .= self::TAB2.'var g_urlViewBaseUC = "'.GlobalsUC::$url_component_admin.'";'.self::BR;
			$js .= self::TAB2.'var g_urlViewBaseNowindowUC = "'.GlobalsUC::$url_component_admin_nowindow.'";'.self::BR;
			$js .= self::TAB2.'var g_urlBaseUC = "'.GlobalsUC::$url_base.'";'.self::BR;
			$js .= self::TAB2.'var g_urlAssetsUC = "'.GlobalsUC::$url_assets.'";'.self::BR;
			$js .= self::TAB2.'var g_settingsObjUC = {};'.self::BR;
			$js .= self::TAB2.'var g_ucAdmin;'.self::BR;
			
			$js .= self::TAB2."var g_ucGeneralSettings = {$strGeneralSettings};".self::BR;
			
			if(GlobalsUC::$enableWebCatalog == true){
				if($isNeedCheckCatalog)
					$js .= self::TAB2.'var g_ucCheckCatalog = true;'.self::BR;
				
				$js .= self::TAB2.'var g_ucCatalogAddonType="'.GlobalsUC::$defaultAddonType.'";'.self::BR;
			}
			
			$jsonFaIcons = UniteFontManagerUC::fa_getJsonIcons();
			$js .= self::TAB2.'var g_ucFaIcons = '.$jsonFaIcons.';'.self::BR;
			
			//get nonce
			if(method_exists("UniteProviderFunctionsUC", "getNonce"))
				$js .= self::TAB2 . "var g_ucNonce='".UniteProviderFunctionsUC::getNonce()."';";
			
			$js .= self::TAB2.'var g_uctext = {'.self::BR;
			$js .= self::TAB3.$jsArrayText.self::BR;
			$js .= self::TAB2.'};'.self::BR;
						
			return($js);
		}
		
		
		/**
		 * get flobal debug divs
		 */
		public static function getGlobalDebugDivs(){
			$html = "";
			
			$html .= self::TAB2.'<div id="div_debug" class="unite-div-debug"></div>'.self::BR;
			$html .= self::TAB2.'<div id="debug_line" style="display:none"></div>'.self::BR;
			$html .= self::TAB2.'<div id="debug_side" style="display:none"></div>'.self::BR;
			$html .= self::TAB2.'<div class="unite_error_message" id="error_message" style="display:none;"></div>'.self::BR;
			$html .= self::TAB2.'<div class="unite_success_message" id="success_message" style="display:none;"></div>'.self::BR;
			
			return($html);
		}
		
		
		
		
		/**
		 * get version text
		 */
		public static function getVersionText(){
			$filepath = GlobalsUC::$pathPlugin."release_log.txt";
			$content = file_get_contents($filepath);
			
			return($content);
			
		}
		
		/**
		 * get error message html
		 */
		public static function getErrorMessageHtml($message, $trace = ""){
		
			$html = '<div style="width:90%;min-width:400px;height:300px;margin-bottom:10px;border:1px solid black;margin:0px auto;overflow:auto;">';
			$html .= '<div style="padding-left:20px;padding-right:20px;line-height:1.5;padding-top:40px;color:red;font-size:16px;text-align:left;">';
			$html .= $message;
		
			if(!empty($trace)){
				$html .= '<div style="text-align:left;padding-left:20px;padding-top:20px;">';
				$html .= "<pre>{$trace}</pre>";
				$html .= "</div>";
			}
		
			$html .= '</div></div>';
		
			return($html);
		}
		
		/**
		 * get settings html
		 */
		public static function getHtmlSettings($filename, $formID, $arrValues = array()){
			
			ob_start();
			
			$html = self::putHtmlSettings($filename, $formID, $arrValues);
			$html = ob_get_contents();
			
			ob_clean();
			ob_end_clean();
		
			return($html);			
		}
		
		
		/**
		 * get custom scripts from array of scripts
		 */
		public static function getHtmlCustomScripts($arrScripts){
			
			if(empty($arrScripts))
				return("");
			
			if(is_array($arrScripts) == false){
				UniteFunctionsUC::throwError("arrScripts should be array");
			}
			
			if(count($arrScripts) == 1 && empty($arrScripts[0]))
				return("");
			
			$html = "<script type='text/javascript'>".self::BR;
			
			foreach($arrScripts as $script){
				$html .= $script.self::BR;
			}
			
			$html .= "</script>".self::BR;
			
			return($html);
		}
		
		/**
		 * get custom html styles
		 */
		public static function getHtmlCustomStyles($arrStyles, $wrapInTag = true){
			
			if(empty($arrStyles))
				return("");
			
			$css = "";
			
			if(is_array($arrStyles) == false)
				$css = $arrStyles;
			else{	//if array
				if(count($arrStyles) == 1 && empty($arrStyles[0]))
					return("");				
								
				foreach($arrStyles as $style)
					$css .= $style.self::BR;
				
			}
			
			if($wrapInTag == false)
				return($css);
			
			$html = "<style type='text/css'>".self::BR;
			$html .= $css;
			$html .= "</style>".self::BR;
			
			return($html);
		}
		
		/**
		 * get css include
		 */
		public static function getHtmlCssInclude($url){
			
			$html = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$url}\">";
			
			return($html);
		}
		
		
		/**
		 * get css include
		 */
		public static function getHtmlJsInclude($url){
						
			$html = "<script type=\"text/javascript\" src=\"{$url}\"></script>";
			
			return($html);
		}
		
		
		/**
		 * 
		 * get html of some includes
		 */
		private static function getHtmlIncludes($type, $arrIncludes, $tab = null){
						
			if(empty($arrIncludes))
				return("");
			
			$html = "";
			foreach($arrIncludes as $urlInclude){
				if($tab !== null)
					$html .= $tab;
					
				if($type == "css")
					$html .= self::getHtmlCssInclude($urlInclude).self::BR;
				else 
					$html .= self::getHtmlJsInclude($urlInclude).self::BR;
				
			}
			
			return($html);
			
		}
		
		/**
		 * get html includes from array includes
		 */
		public static function getHtmlCssIncludes($arrCssIncludes, $tab = null){
			
			return self::getHtmlIncludes("css", $arrCssIncludes, $tab);
		}
		
		/**
		 * get html includes from array includes
		 */
		public static function getHtmlJsIncludes($arrJsIncludes, $tab = null){
			
			return self::getHtmlIncludes("js", $arrJsIncludes, $tab);
		}
		
		
		protected function __________PUTTERS__________(){}
		
		
		/**
		 * put global framework
		 */
		public static function putGlobalsHtmlOutput(){
			
			if(self::$isGlobalJSPut == true)
				return(false);
			
			$jsOutput = self::getGlobalJsOutput();
			
			?>
			<script type="text/javascript">
				
				<?php echo $jsOutput?>
				
				
			</script>
			
			<?php 
				
				$debugDivs = self::getGlobalDebugDivs();
				echo $debugDivs;
				
				if(method_exists("UniteProviderFunctionsUC", "putMasterHTML"))
					UniteProviderFunctionsUC::putMasterHTML() 
			?>			
			
			<?php 			
		}

		
		/**
		 * put control fields notice to dialogs that use it
		 */
		public static function putDialogControlFieldsNotice(){
			?>
				<div class="unite-inputs-sap"></div> 
			
				<div class="unite-inputs-label unite-italic">
					* <?php _e("only dropdown and radio boolean field types are used for conditional inputs", ADDONLIBRARY_TEXTDOMAIN)?>.
				</div>
			
			<?php 
		}
		
		
		/**
		 * put dialog actions
		 */
		public static function putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle, $buttonClass="primary"){
			?>
				<div id="<?php echo $prefix?>_actions_wrapper" class="unite-dialog-actions">
					
					<a id="<?php echo $prefix?>_action" href="javascript:void(0)" class="unite-button-<?php echo $buttonClass?>"><?php echo $buttonTitle?></a>
					<div id="<?php echo $prefix?>_loader" class="loader_text" style="display:none"><?php echo $loaderTitle?></div>
					<div id="<?php echo $prefix?>_error" class="unite-dialog-error"  style="display:none"></div>
					<div id="<?php echo $prefix?>_success" class="unite-dialog-success" style="display:none"><?php echo $successTitle?></div>
					
				</div>
			<?php 
		}
		
		/**
		 * put plugin version html
		 */
		public static function putPluginVersionHtml(){
			
			$objPlugins = new UniteCreatorPlugins();
			
			$arrPlugins = $objPlugins->getArrPlugins();
			
			if(empty($arrPlugins))
				return(false);
						
			foreach($arrPlugins as $plugin){
				
				$name = UniteFunctionsUC::getVal($plugin, "name");
				$title = UniteFunctionsUC::getVal($plugin, "title");
				$version = UniteFunctionsUC::getVal($plugin, "version");
				$silentMode = UniteFunctionsUC::getVal($plugin, "silent_mode");
				$silentMode = UniteFunctionsUC::strToBool($silentMode);
				
				if($silentMode == true)
					continue;
				
				switch($name){
					case "create_addons":
						$title = "Create addons plugin {$version}";
					break;
					default:
						$title = "$title {$version}";
					break;
				}
				
				echo ", ";
				
				echo $title;
			}
			
		}
		
		
		/**
		 * output exception
		 */
		public static function outputException(Exception $e, $prefix=""){
			
			if(empty($prefix))
				$prefix = HelperUC::getText("addon_library")." Error: ";
			
			$message = $prefix.$e->getMessage();
			$trace = $e->getTraceAsString();
			
			dmp($message);
			if(GlobalsUC::SHOW_TRACE == true)
				dmp($trace);
		}
		
		
		
		
		/**
		 * output exception in a box
		 */
		public static function outputExceptionBox($e, $prefix=""){
			
			$message = $e->getMessage();
			
			if(!empty($prefix))
				$message = $prefix.":  ".$message;
			
			$trace = "";
			
			$showTrace = GlobalsUC::SHOW_TRACE_FRONT;
			if(UniteProviderFunctionsUC::isAdmin() == true)
				$showTrace = GlobalsUC::SHOW_TRACE;
				
			if($showTrace)
				$trace = $e->getTraceAsString();
			
			$html = self::getErrorMessageHtml($message, $trace);
			
			echo $html;
		}
		
		/**
		 * get hidden input field
		 */
		public static function getHiddenInputField($name, $value){
			$value = htmlspecialchars($value);
			
			$html = '<input type="hidden" name="'.$name.'" value="'.$value.'">';
			
			return($html);
		}
		
		
		/**
		 * put settings html from filepath
		 */
		public static function putHtmlSettings($filename, $formID, $arrValues = array(), $pathSettings = null){
			
			if($pathSettings === null)
				$pathSettings = GlobalsUC::$pathSettings;
			
			$filepathSettings = $pathSettings."{$filename}.xml";
			
			UniteFunctionsUC::validateFilepath($filepathSettings, "settings file - {$filename}.xml");
			
			$settings = new UniteSettingsAdvancedUC();
			$settings->loadXMLFile($filepathSettings);
			
			if(!empty($arrValues))
				$settings->setStoredValues($arrValues);
			
			$output = new UniteSettingsOutputWideUC();
			$output->init($settings);
			$output->draw($formID);
			
		}
		
		
		/**
		 * draw settings and get html output
		 */
		public static function drawSettingsGetHtml($settings, $formName){
			
			$output = new UniteSettingsOutputWideUC();
			$output->init($settings);
			
			ob_start();
			$output->draw($formName);
			
			$htmlSettings = ob_get_contents();
			
			ob_end_clean();
					
			return($htmlSettings);
		}
		
		
		
		/**
		 * output memory log html
		 */
		public static function outputMemoryUsageLog(){
			$arrLog = HelperUC::getLastMemoryUsage();
			
			if(empty($arrLog)){
				echo "no memory log found";
				return(false);
			}
			
			$timestamp = $arrLog[0]["time"];
			$date = UniteFunctionsUC::timestamp2DateTime($timestamp);
			
			$urlPage = UniteFunctionsUC::getVal($arrLog[0], "current_page");
			
			?>
			<div class="unite-title1">Last log from: <b><?php echo $date?></b></div>
			<div>Page: <b><?php echo $urlPage?></b></div>
			<br>
			
			<table class="unite-table">
				<tr>
					<th>
						Operation
					</th>
					<th>
						Usage
					</th>
					<th>
						Diff
					</th>
				</tr>
			
			<?php 
			
			foreach($arrLog as $item):
				$operation  = $item["oper"];
				$usage = $item["usage"];
				$diff = $item["diff"];
				
				$usage = number_format($usage);
				$diff = number_format($diff);
				
				?>
				<tr>
					<td>
						<?php echo $operation?>
					</td>
					<td>
						<?php echo $usage?>
					</td>
					<td>
						<?php echo $diff?>
					</td>
				</tr>
				<?php 
							
			endforeach;
			?>
			</table>
			<?php 
		}

		
		/**
		 * put admin notices html
		 */
		public static function putHtmlAdminNotices(){
			
			$arrNotices = HelperUC::getAdminNotices();
			if(empty($arrNotices))
				return(false);
				
			$html = "";
			foreach($arrNotices as $notice){
				$html .= "\n<div class='unite-admin-notice'>{$notice}</div>\n";
			}
			
			echo $html;
		}
		
		
		/**
		 * put admin notices
		 */
		public static function putInternalAdminNotices(){
			
			$masterNotice = null;
			
			if(strpos(GlobalsUC::URL_API, "http://localhost") !== false)
				$masterNotice = "Dear developer, Please remove local API url in globals";
			
			if(empty($masterNotice))
				return(false);
						
			?>
			
			<div class="unite-admin-notice">
				<?php echo $masterNotice?>
			</div>
			<?php 
		}
		
	
		
		/**
		 * wrap in media query
		 */
		public static function wrapCssMobile($css, $isTablet = false){
			
			if(empty($css))
				return($css);
			
			if(is_string($isTablet))
				$isTablet = ($isTablet == "tablet");
				
			if($isTablet == true){
				$output = "@media (max-width:768px){{$css}}";
			
			}else{
				$output = "@media (max-width:480px){{$css}}";
			}
			
			return($output);
		}
		
		
	} //end class
	
	
	