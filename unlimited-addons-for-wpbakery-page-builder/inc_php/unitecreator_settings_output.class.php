<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');
	
	class UniteCreatorSettingsOutput extends UniteSettingsOutputUC{
		
		
		/**
		 * draw addon setting output
		 */
		private function drawImageAddonInput($setting){
			
			$previewStyle = "display:none";
			
			$urlBase = UniteFunctionsUC::getVal($setting, "url_base");
						
			$isError = false;
			
			$value = UniteFunctionsUC::getVal($setting, "value");
			
			if(empty($urlBase)){
				$isError = true;
				$value = "";
				$setting["value"] = "";
			}
						
			if(!empty($value)){
				
				$urlFull = $urlBase.$value;
								
				$previewStyle = "";
			
				$operations = new UCOperations();
				try{
					
					$urlThumb = $operations->createThumbs($urlFull);
					
				}catch(Exception $e){
					$urlThumb = $value;
				}
			
				$urlThumbFull = HelperUC::URLtoFull($urlThumb);
				if(!empty($previewStyle))
					$previewStyle .= ";";
				
				$previewStyle .= "background-image:url('{$urlThumbFull}');";
			}
			
			if(!empty($previewStyle))
				$previewStyle = "style=\"{$previewStyle}\"";
			
			
			$class = $this->getInputClassAttr($setting, "", "unite-setting-image-input unite-input-image");
			
			$addHtml = $this->getDefaultAddHtml($setting);
			
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if(!empty($source))
				$addHtml .= " data-source='{$source}'";
			
			//set error related
			
			$buttonAddClass = "";
			$errorStyle = "style='display:none'";
			if($isError == true){
				$buttonAddClass = " button-disabled";
				$errorStyle = "'";
			}
			
			?>
				<div class="unite-setting-image"> 
					<input type="text" id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" readonly data-baseurl="<?php echo $urlBase?>" <?php echo $class?> value="<?php echo $value?>" <?php echo $addHtml?> />
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-choose <?php echo $buttonAddClass?>"><?php _e("Choose", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-clear <?php echo $buttonAddClass?>"><?php _e("Clear", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<div class='unite-setting-image-preview' <?php echo $previewStyle?>></div>
					<div class='unite-setting-image-error' <?php echo $errorStyle?>><?php _e("Please select assets path", ADDONLIBRARY_TEXTDOMAIN)?></div>
				</div>
			<?php
		}
		
		
		/**
		 *
		 * draw imaeg input:
		 * @param $setting
		 */
		protected function drawMp3AddonInput($setting){
		
			$previewStyle = "display:none";
			
			$setting = $this->modifyImageSetting($setting);
		
			$value = UniteFunctionsUC::getVal($setting, "value");
			
			$urlBase = UniteFunctionsUC::getVal($setting, "url_base");
			
			$isError = false;
						
			if(empty($urlBase)){
				$isError = true;
				$value = "";
				$setting["value"] = "";
			}
			
			$class = $this->getInputClassAttr($setting, "", "unite-setting-mp3-input unite-input-image");
		
			$addHtml = $this->getDefaultAddHtml($setting);
		
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if(!empty($source))
				$addHtml .= " data-source='{$source}'";
			
			$buttonAddClass = "";
			$errorStyle = "style='display:none'";
			if($isError == true){
				$buttonAddClass = " button-disabled";
				$errorStyle = "'";
			}
			
			?>
				<div class="unite-setting-mp3">
					<input type="text" id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" <?php echo $class?> value="<?php echo $value?>" <?php echo $addHtml?> />
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-choose <?php echo $buttonAddClass?>"><?php _e("Choose", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<div class='unite-setting-mp3-error' <?php echo $errorStyle?>><?php _e("Please select assets path", ADDONLIBRARY_TEXTDOMAIN)?></div>
				</div>
			<?php
		}
		
		
		/**
		 * override setting
		 */
		protected function drawImageInput($setting){
			
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if($source == "addon")
				$this->drawImageAddonInput($setting);
			else
				parent::drawImageInput($setting);
			
		}
		
		
		/**
		 * draw mp3 input
		 */
		protected function drawMp3Input($setting){
			
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if($source == "addon")
				$this->drawMp3AddonInput($setting);
			else
				parent::drawMp3Input($setting);
		}

		private function a_______DRAW_FONTS_PANEL__________(){}
		
		
		/**
		 * get fonts panel html fields
		 */
		private function getFontsPanelHtmlFields($arrParams, $arrFontsData, $addTemplate = false){
			
			$arrData = HelperUC::getFontPanelData($addTemplate);
			
			if($addTemplate == true)
				$arrFontsTemplate = UniteCreatorPageBuilder::getPageFontNames(true);
			
			//get last param name
			end($arrParams);
			$lastName = key($arrParams);
						
			$html = "<div class='uc-fontspanel'>";
			
			$counter = 0;
			$random = UniteFunctionsUC::getRandomString(5);
			
			$br = "\n";
			foreach ($arrParams as $name => $title):
				
				 $counter++;
			     $sectionID = "uc_fontspanel_section_{$random}_{$counter}";
				 
			     $fontData = UniteFunctionsUC::getVal($arrFontsData, $name);
				 $isDataExists = !empty($fontData);
				 
				 if($addTemplate == true)
				 	$fontTemplate = UniteFunctionsUC::getVal($fontData, "template");
				 
				 $fontFamily = UniteFunctionsUC::getVal($fontData, "font-family");
				 $fontWeight = UniteFunctionsUC::getVal($fontData, "font-weight");
				 $fontSize = UniteFunctionsUC::getVal($fontData, "font-size");
				 $lineHeight = UniteFunctionsUC::getVal($fontData, "line-height");
				 $textDecoration = UniteFunctionsUC::getVal($fontData, "text-decoration");
				 $mobileSize = UniteFunctionsUC::getVal($fontData, "mobile-size");
				 $fontStyle = UniteFunctionsUC::getVal($fontData, "font-style");
				 
				 $color = UniteFunctionsUC::getVal($fontData, "color");
				 $color = htmlspecialchars($color);
				 
				 $customStyles = UniteFunctionsUC::getVal($fontData, "custom");
				 $customStyles = htmlspecialchars($customStyles);
				 
				 
				 $classInput = "uc-fontspanel-field";
				 
				 if($addTemplate == true)
				 	$selectFontTemplate = HelperHtmlUC::getHTMLSelect($arrFontsTemplate, $fontTemplate,"data-fieldname='template' class='{$classInput}'", true, "not_chosen", __("---- Select Page Font----", ADDONLIBRARY_TEXTDOMAIN));
				 
				 $selectFontFamily = HelperHtmlUC::getHTMLSelect($arrData["arrFontFamily"],$fontFamily,"data-fieldname='font-family' class='{$classInput}'", true, "not_chosen", __("Select Font Family", ADDONLIBRARY_TEXTDOMAIN));
				 
				 $selectFontWeight = HelperHtmlUC::getHTMLSelect($arrData["arrFontWeight"],$fontWeight,"data-fieldname='font-weight' class='{$classInput}'", false, "not_chosen", __("Select Font Weight", ADDONLIBRARY_TEXTDOMAIN));
				 $selectFontSize = HelperHtmlUC::getHTMLSelect($arrData["arrFontSize"],$fontSize,"data-fieldname='font-size' class='{$classInput}'", false, "not_chosen", __("Select Font Size", ADDONLIBRARY_TEXTDOMAIN));
				 $selectLineHeight = HelperHtmlUC::getHTMLSelect($arrData["arrLineHeight"],$lineHeight,"data-fieldname='line-height' class='{$classInput}'", false, "not_chosen", __("Select Line Height", ADDONLIBRARY_TEXTDOMAIN));
				 $selectTextDecoration = HelperHtmlUC::getHTMLSelect($arrData["arrTextDecoration"],$textDecoration,"data-fieldname='text-decoration' class='{$classInput}'", false, "not_chosen", __("Select Text Decoration", ADDONLIBRARY_TEXTDOMAIN));
				 $selectMobileSize = HelperHtmlUC::getHTMLSelect($arrData["arrMobileSize"],$mobileSize,"data-fieldname='mobile-size' class='{$classInput}'", false, "not_chosen", __("Select Mobile Size", ADDONLIBRARY_TEXTDOMAIN));
				 $selectFontStyle = HelperHtmlUC::getHTMLSelect($arrData["arrFontStyle"],$mobileSize,"data-fieldname='font-style' class='{$classInput}'", false, "not_chosen", __("Select Style", ADDONLIBRARY_TEXTDOMAIN));
				 
				 $classSection = "uc-fontspanel-details";			 
				 
				 $htmlChecked = "";
				 $contentAddHtml = "style='display:none'";
				 
				 if($isDataExists == true){
				 	$htmlChecked = "checked ";
				 	$contentAddHtml = "";
				 }
				 
				 $html .= "<label class=\"uc-fontspanel-title\">".$br;
				 $html .=    "<input data-target=\"{$sectionID}\" {$htmlChecked}data-sectionname=\"{$name}\" type=\"checkbox\" onfocus='this.blur()' class='uc-fontspanel-toggle uc-fontspanel-toggle-{$name}' /> {$title}".$br;
				 $html .= " </label>";
				 
			     $html .= "<div id='{$sectionID}' class='uc-fontspanel-section' {$contentAddHtml}>	".$br;
			    	
			     $html .= "<div class=\"uc-fontspanel-line\">".$br;
			     
			     if($addTemplate == true){
			     	
				     $html .= "<span class=\"{$classSection} uc-details-font-select\">".$br;
				     $html .= " 			".__("From Page Font", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
				     $html .= "		".$selectFontTemplate.$br;
				     $html .= "</span>".$br;
			     }
			     
			     $html .= "<span class=\"{$classSection}\">".$br;
			     $html .= " 			".__("Font Family", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
			     $html .= "		".$selectFontFamily.$br;
			     $html .= "</span>".$br;
			     
			     $html .= "<span class=\"{$classSection}\">".$br;
			     $html .= "			".__("Font Weight", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
			     $html .= "		".$selectFontWeight.$br;
			     $html .= "</span>".$br;
			      	
			     $html .= "<span class=\"{$classSection}\">".$br;
			     $html .= "			".__("Font Size", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
			     $html .= "		".$selectFontSize.$br;
			     $html .= "	</span>".$br;
			     
			     $html .= "<span class=\"{$classSection}\">".$br;
			     $html .= "		".__("Line Height", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
			     $html .= "		".$selectLineHeight.$br;
			     $html .= "</span>".$br;
			     
			     $html .= "</div>".$br;	//line
			     
			     $html .= "<div class=\"uc-fontspanel-line\">".$br;
			     		      			      		
		      	 $html .= "<span class=\"{$classSection}\">".$br;
		      	 $html .= "	".__("Text Decoration", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
		      	 $html .= $selectTextDecoration;
		      	 $html .= "</span>".$br;
			      	
		      	 $html .= "<span class=\"{$classSection}\">".$br;
		      	 $html .= "	".__("Color", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
		      	 $html .= "	<input type=\"text\" data-fieldname='color' value=\"{$color}\" class=\"unite-color-picker {$classInput}\">	".$br;
		      	 $html .= "</span>".$br;
			     
		      	 $html .= "<span class=\"{$classSection}\">".$br;
		      	 $html .= "	".__("Mobile Font Size", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
		      	 $html .= "	".$selectMobileSize.$br;
		      	 $html .= "</span>".$br;
		      	 
		      	 $html .= "<span class=\"{$classSection}\">".$br;
		      	 $html .= "	".__("Font Style", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
		      	 $html .= $selectFontStyle;
		      	 $html .= "</span>".$br;
		      	 
		      	 $html .= "<span class=\"{$classSection}\">".$br;
		      	 $html .= "	".__("Custom Styles", ADDONLIBRARY_TEXTDOMAIN)."<br>".$br;
		      	 $html .= "	<input type=\"text\" data-fieldname='custom' value=\"{$customStyles}\" class=\"{$classInput}\">	".$br;
		      	 $html .= "</span>".$br;
		      	 
			     $html .= "	</div>".$br;    				      
			     $html .= "</div>".$br;
			    
			    if($name != $lastName) 
			    	$html .= "<div class='uc-fontspanel-sap'></div>";
			    
			    $html .= "<div class='unite-clear'></div>".$br;
			    
			endforeach;
					
			$html .= "</div>".$br;
			
			$html .= "<div class='unite-clear'></div>".$br;
			
			return($html);
		}

		
		
		/**
		 * get param array
		 */
		private function getFontsParams_getArrParam($type, $fieldName, $name, $title, $value, $options = null, $addParams = null){
			
			$paramName = "ucfont_{$name}__".$fieldName;
			
			$param = array();
			$param["name"] = $paramName;
			$param["type"] = $type;
			$param["title"] = $title;
			$param["value"] = $value;
			
			if(!empty($options)){
				$options = array_flip($options);
				$param["options"] = $options;
			}
			
			if(!empty($addParams))
				$param = array_merge($param, $addParams);
			
			return($param);
		}
		
		
		
		/**
		 * get fonts params
		 */
		public function getFontsParams($arrFontNames, $arrFontsData){
			
			$arrData = HelperUC::getFontPanelData();
			$valueNotChosen = "not_chosen";
			
			$arrFontStyle = UniteFunctionsUC::arrayToAssoc($arrData["arrFontStyle"]);
			$arrFontWeight = UniteFunctionsUC::arrayToAssoc($arrData["arrFontWeight"]);
			$arrFontSize = UniteFunctionsUC::arrayToAssoc($arrData["arrFontSize"]);
			$arrMobileSize = UniteFunctionsUC::arrayToAssoc($arrData["arrMobileSize"]);
			$arrLineHeight = UniteFunctionsUC::arrayToAssoc($arrData["arrLineHeight"]);
			$arrTextDecoration = UniteFunctionsUC::arrayToAssoc($arrData["arrTextDecoration"]);
			
			
			$arrFontFamily = UniteFunctionsUC::addArrFirstValue($arrData["arrFontFamily"], "[Select Font Family]",$valueNotChosen);
			$arrFontStyle = UniteFunctionsUC::addArrFirstValue($arrFontStyle, "[Select Style]",$valueNotChosen);
			$arrFontWeight = UniteFunctionsUC::addArrFirstValue($arrFontWeight, "[Select Font Weight]",$valueNotChosen);
			$arrFontSize = UniteFunctionsUC::addArrFirstValue($arrFontSize, "[Select Font Size]",$valueNotChosen);
			$arrMobileSize = UniteFunctionsUC::addArrFirstValue($arrMobileSize, "[Select Mobile Size]",$valueNotChosen);
			$arrLineHeight = UniteFunctionsUC::addArrFirstValue($arrLineHeight, "[Select Line Height]",$valueNotChosen);
			$arrTextDecoration = UniteFunctionsUC::addArrFirstValue($arrTextDecoration, "[Select Text Decoration]",$valueNotChosen);
						
			
			$arrParams = array();
			
			foreach($arrFontNames as $name => $title){
								
				$fontData = UniteFunctionsUC::getVal($arrFontsData, $name);
				$isDataExists = !empty($fontData);
								
				$fontFamily = UniteFunctionsUC::getVal($fontData, "font-family",$valueNotChosen);
				$fontWeight = UniteFunctionsUC::getVal($fontData, "font-weight",$valueNotChosen);
				$fontSize = UniteFunctionsUC::getVal($fontData, "font-size",$valueNotChosen);
				$lineHeight = UniteFunctionsUC::getVal($fontData, "line-height",$valueNotChosen);
				$textDecoration = UniteFunctionsUC::getVal($fontData, "text-decoration",$valueNotChosen);
				$mobileSize = UniteFunctionsUC::getVal($fontData, "mobile-size",$valueNotChosen);
				$fontStyle = UniteFunctionsUC::getVal($fontData, "font-style",$valueNotChosen);
				$color = UniteFunctionsUC::getVal($fontData, "color");
				$customStyles = UniteFunctionsUC::getVal($fontData, "custom");
				
				
				$arrFields = array();
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_CHECKBOX, "fonts-enabled", $name, "Enable Styles",null, null, array("is_checked"=>$isDataExists));
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "font-family", 	$name, "Font Family", $fontFamily, $arrFontFamily);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_COLORPICKER, "color", $name, "Color", $color);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "font-style", 	$name, "Font Style", $fontStyle, $arrFontStyle);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "font-weight", 	$name, "Font Weight", $fontWeight, $arrFontWeight);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "font-size", 	$name, "Font Size", $fontSize, $arrFontSize);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "mobile-size", 	$name, "Mobile Size", $mobileSize, $arrMobileSize);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "line-height", 	$name, "Line Height", $lineHeight, $arrLineHeight);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_DROPDOWN, "text-decoration", 	$name, "Text Decoraiton", $textDecoration, $arrTextDecoration);
				$arrFields[] = $this->getFontsParams_getArrParam(UniteCreatorDialogParam::PARAM_TEXTFIELD, "custom", 	$name, "Custom Styles", $customStyles);
				
				$arrParams[$name] = $arrFields;
			}
			
			return($arrParams);
		}
		
		
		/**
		 * draw fonts panel - function for override
		 */
		protected function drawFontsPanel($setting){
			
									
			$name = $setting["name"];
			$id = $setting["id"];
			
			
			$arrParamsNames = $setting["font_param_names"];
			$arrFontsData = $setting["value"];
			
			$html = "<div id='{$id}' class='uc-setting-fonts-panel' data-name='{$name}'>";
			
			if(empty($arrParamsNames)){
				
				$html .= "<div class='uc-fontspanel-message'>";
				$html .= "Font overrides are disabled for this addon. If you would like to enable them please contact our support at <a href='https://unitecms.ticksy.com' target='_blank'>unitecms.ticksy.com</a>";
				$html .= "</div>";
				
			}else{
							
				$html .= self::TAB3."<div class='uc-addon-config-fonts'>".self::BR;
				$html .= "<h2>".__("Edit Fonts", ADDONLIBRARY_TEXTDOMAIN)."</h2>";
				
				$isInsideGrid = UniteFunctionsUC::getVal($setting, "inside_grid");
				$addGridTemplate = UniteFunctionsUC::strToBool($isInsideGrid);
				
				$html .= $this->getFontsPanelHtmlFields($arrParamsNames, $arrFontsData, $addGridTemplate);
				
				$html .= self::TAB3."</div>";
			}
			
			$html .= "</div>";
			
			echo $html;
		}
		
		
		private function a_______DRAW_ITEMS_PANEL__________(){}
		
		
		/**
		 * draw fonts panel - function for override
		 */
		protected function drawItemsPanel($setting){
			
			$name = $setting["name"];
			$id = $setting["id"];
			$value = UniteFunctionsUC::getVal($setting, "value");
			$idDialog = $id."_dialog";
			
			$objManager = $setting["items_manager"];
			
			?>
			<div id="<?php echo $id?>" class='uc-setting-items-panel'  data-name='<?php echo $name?>'>
			<?php 
				
				if($this->isSidebar == true): ?>
					<a href="javascript:void(0)" class="unite-button-secondary uc-setting-items-panel-button"><?php _e("Edit Addon Items", ADDONLIBRARY_TEXTDOMAIN)?></a>
					
					<div id='<?php echo $idDialog?>' class='uc-settings-items-panel-dialog' title="<?php _e("Edit Addon Items", ADDONLIBRARY_TEXTDOMAIN)?>" style='display:none'>
				<?php endif;
				
				$objManager->outputHtml();
				
				if($this->isSidebar == true):?>
					</div>
				<?php endif;
				
			?>
			</div>
			<?php 
		}
		
		
	}

