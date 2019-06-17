<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');
	
	class UniteVCCustomParams{
		
		private static $settings;
		
		
		/**
		 * decode content
		 */
		public static function decodeContent($content){
						
			if(empty($content))
				return($content);
			
			$content = rawurldecode(base64_decode($content));
			
			$arr = @json_decode($content);
			$arr = UniteFunctionsUC::convertStdClassToArray($arr);
			
			return $arr;
		}
		
		
		/**
		 * add special param
		 */
		public static function addSpecialParam($param, $value){
			
			//ini_set("display_errors","on");
			try{
				
				//decode content
				$type = UniteFunctionsUC::getVal($param, "type");
				switch($type){
					case "uc_editor":
					case "uc_textarea":
						$value = UniteFunctionsUC::maybeDecodeTextContent($value);
					break;
				} 
				
				$urlAssets = UniteFunctionsUC::getVal($param, "url_assets");
				if(!empty($urlAssets))
					$value = HelperUC::convertFromUrlAssets($value, $urlAssets);
				
				$name = UniteFunctionsUC::getVal($param, "name");
				
				if(empty(self::$settings))
					self::$settings = new UniteCreatorSettings();
				
				self::$settings->addByCreatorParam($param, $value);
				
				$output = new UniteSettingsOutputVC_UC();
				$output->init(self::$settings);
				
				$html = $output->VCgetSettingHtmlByName($name);
				
				return($html);
				
			}catch(Exception $e){
				HelperHtmlUC::outputException($e);
			}
							
		}

				
		
		/**
		 * add items editor
		 */
		public static function addItemsEditor($param, $value){
			
			try{
				
				$arrItems = self::decodeContent($value);
				
				$paramName = UniteFunctionsUC::getVal($param, "name");
				
				$name = UniteFunctionsUC::getVal($param, "addon_name");
				
				$addon = new UniteCreatorAddon();
				$addon->initByName($name);
				$addon->setArrItems($arrItems);
				
				ob_start();

					//put debug and errors messages divs
					$globalDivs = HelperHtmlUC::getGlobalDebugDivs();
					echo $globalDivs;
					
					//put the items manager
					$objManager = new UniteCreatorManagerInline();
					$objManager->setStartAddon($addon);
					$objManager->outputHtml();
					
					//put init items function
					?>
					
					<input type="hidden" name="<?php echo $paramName?>" class="wpb_vc_param_value">
					
					<script type="text/javascript">
						
					g_ucObjGeneralSettings.initVCItems();
						
					</script>
					<?php
				
				$contents = ob_get_contents();
				ob_clean();
				
				return $contents;
				
			}catch(Exception $e){
				HelperHtmlUC::outputException($e);
			}
		}
		
		
		/**
		 * add fonts panel
		 */
		public static function addFontsPanel($param, $value){
			
			$arrFonts = self::decodeContent($value);
			
			if(is_array($arrFonts) == false)
				$arrFonts = null;
			
			$paramName = UniteFunctionsUC::getVal($param, "name");
			
			$name = UniteFunctionsUC::getVal($param, "addon_name");
			
			$addon = new UniteCreatorAddon();
			$addon->initByName($name);
			
			if(!empty($arrFonts))
				$addon->setArrFonts($arrFonts);
			
			$objProcessor = $addon->getObjProcessor();
			$arrParamNames = $objProcessor->getAllParamsNamesForFonts();
			
			
			if(empty(self::$settings))
				self::$settings = new UniteCreatorSettings();
			
			self::$settings->addFontPanel($arrParamNames, $arrFonts);
			
			$output = new UniteSettingsOutputVC_UC();
			$output->init(self::$settings);
			
			$htmlFontsPanel = $output->VCgetSettingHtmlByName("uc_fonts_panel");
						
			
			$wrapperID = "uc_vc_fonts_panel_".UniteFunctionsUC::getRandomString(5);
			
			$html = "";
			$html = "<div id='{$wrapperID}'>";
			$html .= "<input type=\"hidden\" name=\"{$paramName}\" class=\"wpb_vc_param_value\">";
			$html .= $htmlFontsPanel;
			$html .= "</div>";
			
			$html .= "
					<script type=\"text/javascript\">
						
						g_ucObjGeneralSettings.initVCFontsPanel(\"{$wrapperID}\");
						
					</script>
			";
			
			return($html);
		}
		
		
		/**
		 * add init settings param, 
		 * param that inits the settings that being output by visual composer
		 */
		public static function addInitSettingsParam(){
			
			ob_start();
			
			?>
			
			<span id="unite_settings_init_base"></span>
			
			<script type="text/javascript">
				
				var ucObjSettingsInnerDiv = jQuery("#unite_settings_init_base");
				g_ucObjGeneralSettings.initVCSettings(ucObjSettingsInnerDiv);
				
			</script>
			<?php 
			
			$contents = ob_get_contents();
			
			ob_clean();
			
			return $contents;
			
		}
		
		
		
		/**
		 * generate vc dependency
		 */
		private static function generateDependency($settings){
			
			$dependency = vc_generate_dependencies_attributes($settings);
			
			return($dependency);
		}
		
		
		/**
		 * add param
		 */
		private static function addParam($type, $functionName){
			
			if(function_exists("vc_add_shortcode_param"))
				vc_add_shortcode_param($type , array("UniteVCCustomParams", $functionName ));
			else
				vc_add_shortcode_param($type , array("UniteVCCustomParams", $functionName ));
		}
		
		
		/**
		 * create all custom params
		 */
		public static function createCustomParams(){
			
			if(function_exists("add_shortcode_param") == false && function_exists("vc_add_shortcode_param") == false)
				return(false);
			
			$objSettings = new UniteCreatorSettings();
			$arrParamTypes = $objSettings->getArrUCSettingTypes();
			
				
			//add simple param
			foreach($arrParamTypes as $type){
				self::addParam($type, "addSpecialParam");
			}
			
			//add items params
			self::addParam("uc_items", "addItemsEditor");
			self::addParam("uc_fonts", "addFontsPanel");
			self::addParam("uc_init_settings","addInitSettingsParam");
		}
		
	}

?>