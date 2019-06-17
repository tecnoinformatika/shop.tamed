<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

	
	class UniteSettingsOutputUCWork extends HtmlOutputBaseUC{
		
		protected static $arrIDs = array();
		
		protected $arrSettings = array(); 
		protected $settings;
		protected $formID;
		
		protected static $serial = 0;
		
		protected $showDescAsTips = false;
		protected $wrapperID = "";
		protected $addCss = "";
		protected $settingsMainClass = "";
		protected $isParent = false;		//variable that this class is parent
		protected $isSidebar = false;
		
		const INPUT_CLASS_NORMAL = "unite-input-regular";
		const INPUT_CLASS_NUMBER = "unite-input-number";
		const INPUT_CLASS_ALIAS = "unite-input-alias";
		const INPUT_CLASS_LONG = "unite-input-long";
		const INPUT_CLASS_SMALL = "unite-input-small";
		
		//saps related variables
		
		protected $showSaps = false;
		protected $sapsType = null;
		protected $activeSap = 0;		
		
		const SAPS_TYPE_INLINE = "saps_type_inline";	//inline sapts type
		const SAPS_TYPE_CUSTOM = "saps_type_custom";	//custom saps tyle
	    const SAPS_TYPE_ACCORDION = "saps_type_accordion";
		
		/**
		 * 
		 * init the output settings
		 */
		public function init(UniteSettingsUC $settings){
			
			if($this->isParent == false)
				UniteFunctionsUC::throwError("The output class must be parent of some other class.");
				
			$this->settings = new UniteSettingsUC();
			$this->settings = $settings;
		}
		
		
		/**
		 * validate that the output class is inited with settings
		 */
		protected function validateInited(){
			if(empty($this->settings))
				UniteFunctionsUC::throwError("The output class not inited. Please call init() function with some settings class");
		}
		
		
		/**
		 * set add css. work with placeholder
		 * [wrapperid]
		 */
		public function setAddCss($css){
		
			$replace = "#".$this->wrapperID;
			$this->addCss = str_replace("[wrapperid]", $replace, $css);
		}
		
		/**
		 *
		 * set show descriptions as tips true / false
		 */
		public function setShowDescAsTips($show){
			$this->showDescAsTips = $show;
		}
		
		
		/**
		 *
		 * show saps true / false
		 */
		public function setShowSaps($show = true, $type = null){
		    //dmp($type);
		    //exit();
		    //if(empty($type))
		      //   UniteFunctionsUC::showTrace();
		        
			if($type === null)
				$type = self::SAPS_TYPE_INLINE;
			
			$this->showSaps = $show;
			
			switch($type){
				case self::SAPS_TYPE_CUSTOM:
				case self::SAPS_TYPE_INLINE:
				case self::SAPS_TYPE_ACCORDION:
				break;
				default:
					UniteFunctionsUC::throwError("Wrong saps type: $type ");
				break;
			}
			
			$this->sapsType = $type;
			
		}
		
		
		/**
		 * get default value add html
		 * @param $setting
		 */
		protected function getDefaultAddHtml($setting){
			
			$defaultValue = UniteFunctionsUC::getVal($setting, "default_value");
			$defaultValue = htmlspecialchars($defaultValue);
			
			//UniteFunctionsUC::showTrace();exit();
			
			$value = UniteFunctionsUC::getVal($setting, "value");
			if(is_array($value) || is_object($value))
				return("");
				
			$value = htmlspecialchars($value);
			
			$addHtml = " data-default=\"{$defaultValue}\" data-initval=\"{$value}\" ";
			
			$addParams = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_ADDPARAMS);
			if(!empty($addParams))
				$addHtml .= " ".$addParams;
			
			return($addHtml);
		}
		
		
		/**
		 * prepare draw setting text
		 */
		protected function drawSettingRow_getText($setting){
		
			//modify text:
			$text = UniteFunctionsUC::getVal($setting, "text", "");
			
			if(empty($text))
				return("");
				
			// prevent line break (convert spaces to nbsp)
			$text = str_replace(" ","&nbsp;",$text);
		
			switch($setting["type"]){
				case UniteSettingsUC::TYPE_CHECKBOX:
					$text = "<label for='".$setting["id"]."' style='cursor:pointer;'>$text</label>";
					break;
			}
		
			return($text);
		}
		
		
		/**
		 *
		 * get text style
		 */
		protected function drawSettingRow_getTextStyle($setting){
		
			//set text style:
			$textStyle = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_TEXTSTYLE);
		
			if($textStyle != "")
				$textStyle = "style='".$textStyle."'";
		
			return($textStyle);
		}
		
		
		/**
		 * get row style
		 */
		protected function drawSettingRow_getRowStyle($setting){
			
			//set hidden
			$rowStyle = "";
			
			$isHidden = isset($setting["hidden"]);
			
			//operate saps
			if($this->showSaps == true && $this->sapsType == self::SAPS_TYPE_INLINE){
				
				$sap = UniteFunctionsUC::getVal($setting, "sap");
				$sap = (int)$sap;
				
				if($sap != $this->activeSap)
					$isHidden = true;
			}
			
			
			if($isHidden)
				$rowStyle = "display:none;";
			
			if(!empty($rowStyle))
				$rowStyle = "style='$rowStyle'";
		
			return($rowStyle);
		}
		
		
		/**
		 *
		 * get row class
		 */
		protected function drawSettingRow_getRowClass($setting, $basClass = ""){
			
			//set text class:
			$class = $basClass;
			
			if(isset($setting["disabled"])){
				if(!empty($class))
					$class .= " ";
				
				$class .= "setting-disabled";
			}
			
			//add saps class
			if($this->showSaps && $this->sapsType == self::SAPS_TYPE_INLINE){
				
				$sap = UniteFunctionsUC::getVal($setting, "sap");
				$sap = (int)$sap;
				$sapClass = "unite-sap-element unite-sap-".$sap;
				
				if(!empty($class))
					$class .= " ";
				
				$class .= $sapClass;
			}
			
			if(!empty($class))
				$class = "class='{$class}'";
			
				
			return($class);
		}
		
		
		
		
		/**
		* draw after body additional settings accesories
		*/
		public function drawAfterBody(){
			$arrTypes = $this->settings->getArrTypes();
			foreach($arrTypes as $type){
				switch($type){
					case self::TYPE_COLOR:
						?>
							<div id='divPickerWrapper' style='position:absolute;display:none;'><div id='divColorPicker'></div></div>
						<?php
					break;
				}
			}
		}
				
		
		/**
		 * 
		 * do some operation before drawing the settings.
		 */
		protected function prepareToDraw(){
			
			$this->settings->setSettingsStateByControls();
			$this->settings->setPairedSettings();
		}


		/**
		 * get setting class attribute
		 */
		protected function getInputClassAttr($setting, $defaultClass="", $addClassParam="", $wrapClass = true){
			
			$class = UniteFunctionsUC::getVal($setting, "class", $defaultClass);
			$classAdd = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_CLASSADD);
			
			switch($class){
				case "alias":
					$class = self::INPUT_CLASS_ALIAS;
				break;
				case "long":
					$class = self::INPUT_CLASS_LONG;
				break;
				case "normal":
					$class = self::INPUT_CLASS_NORMAL;
				break;
				case "number":
					$class = self::INPUT_CLASS_NUMBER;
				break;
				case "small":
					$class = self::INPUT_CLASS_SMALL;
				break;
			}
			
			if(!empty($classAdd)){
				if(!empty($class))
					$class .= " ";
				$class .= $classAdd;
			}
			
			if(!empty($addClassParam)){
				if(!empty($class))
					$class .= " ";
				$class .= $addClassParam;
			}
			
			$isTransparent = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_MODE_TRANSPARENT);
			if(!empty($isTransparent)){
				if(!empty($class))
					$class .= " ";
				$class .= "unite-setting-transparent";
			}
			
			if(!empty($class) && $wrapClass == true)
				$class = "class='$class'";
			
			return($class);
		}
		
		/**
		 * draw text input
		 * @param $setting
		 */
		protected function drawTextInput($setting) {
			
			$disabled = "";
			$style="";
			$readonly = "";
			
			if(isset($setting["style"])) 
				$style = "style='".$setting["style"]."'";
			if(isset($setting["disabled"])) 
				$disabled = 'disabled="disabled"';
				
			if(isset($setting["readonly"])){
				$readonly = "readonly='readonly'";
			}
			
			$defaultClass = self::INPUT_CLASS_NORMAL;
			
			$unit = UniteFunctionsUC::getVal($setting, "unit");
			if(!empty($unit))
				$defaultClass = self::INPUT_CLASS_NUMBER;
			
			$class = $this->getInputClassAttr($setting, $defaultClass);
			
			$addHtml = $this->getDefaultAddHtml($setting);
						
			$placeholder = UniteFunctionsUC::getVal($setting, "placeholder", null);
			
			if($placeholder !== null){
				$placeholder = htmlspecialchars($placeholder);
				$addHtml .= " placeholder=\"$placeholder\"";
			}
			
			?>
				<input type="text" <?php echo $class?> <?php echo $style?> <?php echo $disabled?><?php echo $readonly?> id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" value="<?php echo $setting["value"]?>" <?php echo $addHtml?> />
			<?php
		}
		
		
		/**
		 * modify image setting values
		 */
		protected function modifyImageSetting($setting){
			
			$value = UniteFunctionsUC::getVal($setting, "value");
			$value = trim($value);
			
			$urlBase = UniteFunctionsUC::getVal($setting, "url_base", null);
			
			if(!empty($value) && is_numeric($value) == false)
				$value = HelperUC::URLtoFull($value, $urlBase);
			
			$defaultValue = UniteFunctionsUC::getVal($setting, "default_value");
			$defaultValue = trim($defaultValue);
			
			if(!empty($defaultValue) && is_numeric($defaultValue) == false)
				$defaultValue = HelperUC::URLtoFull($defaultValue, $urlBase);
			
			$setting["value"] = $value;
			$setting["default_value"] = $defaultValue;
			
			
			return($setting);
		}
	
		
		/**
		 * 
		 * draw imaeg input:
		 * @param $setting
		 */
		protected function drawImageInput($setting){
			
			$previewStyle = "display:none";
			
			$setting = $this->modifyImageSetting($setting);
						
			$value = UniteFunctionsUC::getVal($setting, "value");
			
			$imageID = null;
			$urlImage = $value;
			$urlThumb = $value;
			
			if(!empty($value) && is_numeric($value)){
				$imageID = $value;
				$urlImage = UniteProviderFunctionsUC::getImageUrlFromImageID($imageID);
				$urlThumb = UniteProviderFunctionsUC::getThumbUrlFromImageID($imageID);
				
				$urlImage = HelperUC::URLtoFull($urlImage);
				$urlThumb = HelperUC::URLtoFull($urlThumb);
				
				$setting["value"] = $urlImage;		//for initval
			}
			
			//try create thumb image
			if(empty($urlThumb) && !empty($urlImage)){
				
					try{
						$operations = new UCOperations();
						$urlThumb = $operations->getThumbURLFromImageUrl($value);
						$urlThumb = HelperUC::URLtoFull($urlThumb);
						
					}catch(Exception $e){
						$urlThumb = $urlImage;
					}
								
			}
			
			//get url preview
			$urlPreview = "";
			if(!empty($urlThumb))
				$urlPreview = $urlThumb;
			
			//get preview style
			if(empty($urlPreview) && !empty($urlImage))
				$urlPreview = $urlImage;
			
			$previewStyle = "";
			
			if(!empty($urlPreview))
				$previewStyle .= "background-image:url('{$urlPreview}');";
			
			$clearStyle = "style='display:none'";
			if(!empty($previewStyle)){
				$previewStyle = "style=\"{$previewStyle}\"";
				$clearStyle = "";
			}
			
			$class = $this->getInputClassAttr($setting, "", "unite-setting-image-input unite-input-image");
			
			$addHtml = $this->getDefaultAddHtml($setting);
			
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if(!empty($source))
				$addHtml .= " data-source='{$source}'";
			
			if(!empty($imageID))
					$addHtml .= " data-imageid='{$imageID}'";
			
			?>
				<div class="unite-setting-image"> 
					<input type="text" id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" <?php echo $class?> value="<?php echo $urlImage?>" <?php echo $addHtml?> />
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-choose"><?php _e("Choose", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-clear" <?php echo $clearStyle?>><?php _e("Clear", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<div class='unite-setting-image-preview' <?php echo $previewStyle?>></div>
				</div>
			<?php
		}

		
		/**
		 *
		 * draw image input:
		 * @param $setting
		 */
		protected function drawMp3Input($setting){
			
			$previewStyle = "display:none";
		
			$setting = $this->modifyImageSetting($setting);
			
			$value = UniteFunctionsUC::getVal($setting, "value");
		
			$class = $this->getInputClassAttr($setting, "", "unite-setting-mp3-input unite-input-image");
			
			$addHtml = $this->getDefaultAddHtml($setting);
		
			//add source param
			$source = UniteFunctionsUC::getVal($setting, "source");
			if(!empty($source))
				$addHtml .= " data-source='{$source}'";
		
			?>
				<div class="unite-setting-mp3">
					<input type="text" id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" <?php echo $class?> value="<?php echo $value?>" <?php echo $addHtml?> />
					<a href="javascript:void(0)" class="unite-button-secondary unite-button-choose"><?php _e("Choose", ADDONLIBRARY_TEXTDOMAIN)?></a>
				</div>
			<?php
		}
		
		/**
		 *
		 * draw icon picker input:
		 * @param $setting
		 */
		protected function drawIconPickerInput($setting){
			
			$previewStyle = "display:none";
			$value = UniteFunctionsUC::getVal($setting, "value");
			$class = $this->getInputClassAttr($setting, "", "unite-iconpicker-input");
			$addHtml = $this->getDefaultAddHtml($setting);
			
			$iconsType = UniteFunctionsUC::getVal($setting, "icons_type");
			if($iconsType)
				$addHtml .= " data-icons_type='$iconsType'";
			
			?>
		      <div class="unite-settings-iconpicker">
				<input type="text" id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" <?php echo $class?> value="<?php echo $value?>" <?php echo $addHtml?> />
		        <span class="unite-iconpicker-button"></span>
			  </div>
			<?php
		}
		
		
		/**
		 * special inputs
		 */
		private function a____SPECIAL_INPUTS_____(){}
		
		
		/**
		 * draw icon picker input:
		 * @param $setting
		 */
		protected function drawMapPickerInput($setting){
			
			$value = UniteFunctionsUC::getVal($setting, "value");
						
			$dialogTitle = __("Select Map",ADDONLIBRARY_TEXTDOMAIN);
			
			$filepathPickerObject = GlobalsUC::$pathViewsObjects."mappicker_view.class.php";
			require_once $filepathPickerObject;
			
			$objPicker = new UniteCreatorMappickerView();
			$objPicker->setData($value);
			
			$strMapData = UniteFunctionsUC::jsonEncodeForHtmlData($value, "mapdata");
			
			?>
		      <div id="<?php echo $setting["id"]?>" data-settingtype="map" <?php echo $strMapData?> class="unite-settings-mappicker unite-setting-input-object" data-name="<?php echo $setting["name"]?>" data-dialogtitle="<?php echo $dialogTitle?>" >
		      	 <?php $objPicker->putPickerInputHtml()?>
			  </div>
			<?php
		}
		
		
		/**
		 * draw icon picker input:
		 * @param $setting
		 */
		protected function drawPostPickerInput($setting){
			dmp("drawPostPickerInput: function for override");
			exit();
		}
		
		/**
		 * draw module picker input:
		 * @param $setting
		 */
		protected function drawModulePickerInput($setting){
			dmp("drawModulePickerInput: function for override");
			exit();
		}
		
		
		/**
		 * draw color picker
		 * @param $setting
		 */
		protected function drawColorPickerInput($setting){	
			$bgcolor = $setting["value"];
			$bgcolor = str_replace("0x","#",$bgcolor);			
			// set the forent color (by black and white value)
			$rgb = UniteFunctionsUC::html2rgb($bgcolor);
			$bw = UniteFunctionsUC::yiq($rgb[0],$rgb[1],$rgb[2]);
			$color = "#000000";
			if($bw<128) $color = "#ffffff";
			
			$disabled = "";
			if(isset($setting["disabled"])){
				$color = "";
				$disabled = 'disabled="disabled"';
			}
			
			$style="style='background-color:$bgcolor;color:$color'";
			
			$addHtml = $this->getDefaultAddHtml($setting);
			
			$class = $this->getInputClassAttr($setting, "", "unite-color-picker");
			
			?>
				<input type="text" <?php echo $class?> id="<?php echo $setting["id"]?>" <?php echo $style?> name="<?php echo $setting["name"]?>" value="<?php echo $bgcolor?>" <?php echo $disabled?> <?php echo $addHtml?>></input>
			<?php
		}
		
		
		/**
		 * draw the editor by provider
		 */
		protected function drawEditorInput($setting){
			
			dmp("provider settings output - function to override");
			exit();
		}
		
		/**
		 * draw fonts panel - function for override
		 */
		protected function drawFontsPanel($setting){
			
			dmp("draw fonts panel - function for override");
			exit();
		}
		
		/**
		 * draw fonts panel - function for override
		 */
		protected function drawItemsPanel($setting){
			
			dmp("draw items panel - function for override");
			exit();
		}
		
		
		/**
		 * draw setting input by type
		 */
		protected function drawInputs($setting){
			
			switch($setting["type"]){
				case UniteSettingsUC::TYPE_TEXT:
					$this->drawTextInput($setting);
				break;
				case UniteSettingsUC::TYPE_COLOR:
					$this->drawColorPickerInput($setting);
				break;
				case UniteSettingsUC::TYPE_SELECT:
					$this->drawSelectInput($setting);
				break;
				case UniteSettingsUC::TYPE_CHECKBOX:
					$this->drawCheckboxInput($setting);
				break;
				case UniteSettingsUC::TYPE_RADIO:
					$this->drawRadioInput($setting);
				break;
				case UniteSettingsUC::TYPE_TEXTAREA:
					$this->drawTextAreaInput($setting);
				break;
				case UniteSettingsUC::TYPE_IMAGE:
					$this->drawImageInput($setting);
				break;
				case UniteSettingsUC::TYPE_MP3:
					$this->drawMp3Input($setting);
				break;
				case UniteSettingsUC::TYPE_ICON:
					$this->drawIconPickerInput($setting);
				break;
				case UniteSettingsUC::TYPE_MAP:
					$this->drawMapPickerInput($setting);
				break;
				case UniteSettingsUC::TYPE_POST:
					$this->drawPostPickerInput($setting);
				break;
				case UniteSettingsUC::TYPE_EDITOR:
					$this->drawEditorInput($setting);
				break;
				case UniteCreatorSettings::TYPE_FONT_PANEL:
					$this->drawFontsPanel($setting);
				break;
				case UniteCreatorSettings::TYPE_ITEMS:
					$this->drawItemsPanel($setting);
				break;
				case UniteCreatorSettings::TYPE_BUTTON:
					$this->drawButtonInput($setting);
				break;
				case UniteSettingsUC::TYPE_CUSTOM:
					if(method_exists($this,"drawCustomInputs") == false){
						UniteFunctionsUC::throwError("Method don't exists: drawCustomInputs, please override the class");
					}
					$this->drawCustomInputs($setting);
				break;
				default:
					throw new Exception("drawInputs error: wrong setting type - ".$setting["type"]);
				break;
			}
			
		}		
		
		/**
		 * special inputs
		 */
		private function a____REGULAR_INPUTS_____(){}
		
		/**
		 * draw button input
		 */
		protected function drawButtonInput($setting){
			
			$name = $setting["name"];
			$id = $setting["id"];
			$value = $setting["value"];
			$href = "javascript:void(0)";
			$gotoView = UniteFunctionsUC::getVal($setting, "gotoview");
			
			if(!empty($gotoView))
				$href = HelperUC::getViewUrl($gotoView);
			
			?>
			<a id="<?php echo $id?>" href="<?php echo $href?>" name="<?php echo $name?>" class="unite-button-secondary"><?php echo $value?></a>
			<?php 
			
		}
		
		
		/**
		 * draw text area input
		 */
		protected function drawTextAreaInput($setting){
			
			$disabled = "";
			if (isset($setting["disabled"])) 
				$disabled = 'disabled="disabled"';
			
			$style = "";
			if(isset($setting["style"]))
				$style = "style='".$setting["style"]."'";
			
			$rows = UniteFunctionsUC::getVal($setting, "rows");
			if(!empty($rows))
				$rows = "rows='$rows'";
				
			$cols = UniteFunctionsUC::getVal($setting, "cols");
			if(!empty($cols))
				$cols = "cols='$cols'";
			
			$addHtml = $this->getDefaultAddHtml($setting);
			
			$class = $this->getInputClassAttr($setting);
			
			$value = $setting["value"];
			$value = htmlspecialchars($value);
			
			?>
				<textarea id="<?php echo $setting["id"]?>" <?php echo $class?> name="<?php echo $setting["name"]?>" <?php echo $style?> <?php echo $disabled?> <?php echo $rows?> <?php echo $cols?> <?php echo $addHtml?> ><?php echo $value?></textarea>
			<?php
			if(!empty($cols))
				echo "<br>";	//break line on big textareas.
		}		
		
		
		/**
		 * draw radio input
		 */
		protected function drawRadioInput($setting){
			
			$items = $setting["items"];
			$counter = 0;
			$settingID = $setting["id"];
			$isDisabled = UniteFunctionsUC::getVal($setting, "disabled");
			$isDisabled = UniteFunctionsUC::strToBool($isDisabled);
			$settingName = $setting["name"];
			$defaultValue = UniteFunctionsUC::getVal($setting, "default_value");
			$settingValue = UniteFunctionsUC::getVal($setting, "value");
			
			$class = $this->getInputClassAttr($setting);
			
			
			?>
			<span id="<?php echo $settingID ?>" class="radio_wrapper">
			<?php 
			foreach($items as $text=>$value):
				$counter++;
				$radioID = $settingID."_".$counter;
				
				$strChecked = "";				
				if($value == $settingValue) 
					$strChecked = " checked";
				
				$strDisabled = "";
				if($isDisabled)
					$strDisabled = 'disabled = "disabled"';
				
				$addHtml = "";
				if($value == $defaultValue)
					$addHtml .= " data-defaultchecked=\"true\"";
				
				if($value == $settingValue){
					$addHtml .= " data-initchecked=\"true\"";
				}
				
				$props = "style=\"cursor:pointer;\" {$strChecked} {$strDisabled} {$addHtml} {$class}";
				
				?>					
					<input type="radio" id="<?php echo $radioID?>" value="<?php echo $value?>" name="<?php echo $settingName?>" <?php echo $props?>/>
					<label for="<?php echo $radioID?>" ><?php echo $text?></label>
					&nbsp; &nbsp;
				<?php				
			endforeach;
			
			?>
			</span>
			<?php 
		}
		
		
		/**
		 * draw checkbox
		 */
		protected function drawCheckboxInput($setting){
			$checked = "";
						
			$value = UniteFunctionsUC::getVal($setting, "value");
			$value = UniteFunctionsUC::strToBool($value);
			
			if($value == true) 
				$checked = 'checked="checked"';
			
				$textNear = UniteFunctionsUC::getVal($setting, "text_near");
			
			$settingID = $setting["id"];
			
			if(!empty($textNear)){
				$textNearAddHtml = "";
				if($this->showDescAsTips == true){
					$description = UniteFunctionsUC::getVal($setting, "description");
					$description = htmlspecialchars($description);
					$textNearAddHtml = " title='$description' class='uc-tip'";
				}
				
				$textNear = "<label for=\"{$settingID}\"{$textNearAddHtml}>$textNear</label>";
			}
			
			$defaultValue = UniteFunctionsUC::getVal($setting, "default_value");
			$defaultValue = UniteFunctionsUC::strToBool($defaultValue);
			
			$addHtml = "";
			if($defaultValue == true)
				$addHtml .= " data-defaultchecked=\"true\"";
			
			if($value)
				$addHtml .= " data-initchecked=\"true\"";
			
			$class = $this->getInputClassAttr($setting);
			
			?>
				<input type="checkbox" id="<?php echo $settingID?>" <?php echo $class?> name="<?php echo $setting["name"]?>" <?php echo $checked?> <?php echo $addHtml?>/>
			<?php
			
			if(!empty($textNear))
				echo $textNear;
		}		
		
		
		/**
		 * draw select input
		 */
		protected function drawSelectInput($setting){
			
			$disabled = "";
			if(isset($setting["disabled"])) 
				$disabled = 'disabled="disabled"';
			
			$args = UniteFunctionsUC::getVal($setting, "args");
			
			$settingValue = $setting["value"];
			
			if(strpos($settingValue,",") !== false)
				$settingValue = explode(",", $settingValue);
			
			$addHtml = $this->getDefaultAddHtml($setting);
			
			$class = $this->getInputClassAttr($setting);
			
			$arrItems = UniteFunctionsUC::getVal($setting, "items",array());
			if(empty($arrItems))
				$arrItems = array();
			
			?>
			<select id="<?php echo $setting["id"]?>" name="<?php echo $setting["name"]?>" <?php echo $disabled?> <?php echo $class?> <?php echo $args?> <?php echo $addHtml?>>
			<?php
			foreach($arrItems as $text=>$value):
				//set selected
				$selected = "";
				$addition = "";
				
				if(is_array($settingValue)){
					if(array_search($value, $settingValue) !== false) 
						$selected = 'selected="selected"';
				}else{
					if($value == $settingValue) 
						$selected = 'selected="selected"';
				}
				
				?>
					<option <?php echo $addition?> value="<?php echo $value?>" <?php echo $selected?>><?php echo $text?></option>
				<?php
			endforeach
			?>
			</select>
			<?php
		}

		
		
		/**
		 * draw text row
		 * @param unknown_type $setting
		 */
		protected function drawTextRow($setting){
			echo "draw text row - override this function";
		}

		
		/**
		 * draw hr row - override
		 */
		protected function drawHrRow($setting){
			echo "draw hr row - override this function";
		}
		
		
		/**
		 * draw input additinos like unit / description etc
		 */
		protected function drawInputAdditions($setting,$showDescription = true){
			
			$description = UniteFunctionsUC::getVal($setting, "description");
			if($showDescription === false)
				$description = "";
			$unit = UniteFunctionsUC::getVal($setting, "unit");
			$required = UniteFunctionsUC::getVal($setting, "required");
			$addHtml = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_ADDTEXT);
			
			?>
			
			<?php if(!empty($unit)):?>
			<span class='setting_unit'><?php echo $unit?></span>
			<?php endif?>
			<?php if(!empty($required)):?>
			<span class='setting_required'>*</span>
			<?php endif?>
			<?php if(!empty($addHtml)):?>
			<span class="settings_addhtml"><?php echo $addHtml?></span>
			<?php endif?>					
			<?php if(!empty($description) && $this->showDescAsTips == false):?>
			<span class="description"><?php echo $description?></span>
			<?php endif?>
			
			<?php 
		}
		
				
		
		/**
		 * get options
		 */
		protected function getOptions(){
			
			$idPrefix = $this->settings->getIDPrefix();
			
			$options = array();
			$options["show_saps"] = $this->showSaps;
			$options["saps_type"] = $this->sapsType;
			$options["id_prefix"] = $idPrefix;
			
			return($options);
		}
		
		
		/**
		* set form id
		 */
		public function setFormID($formID){
			
			if(isset(self::$arrIDs[$formID]))
				UniteFunctionsUC::throwError("Can't output settings with the same ID: $formID");
			
			self::$arrIDs[$formID] = true;
			
			UniteFunctionsUC::validateNotEmpty($formID, "formID");
			
			$this->formID = $formID;
			
		}
		
		
		/**
		 *
		 * insert settings into saps array
		 */
		private function groupSettingsIntoSaps(){
		    
		    $arrSaps = $this->settings->getArrSaps();
		    $arrSettings = $this->settings->getArrSettings();
		    
		    //group settings by saps
		    foreach($arrSettings as $key=>$setting){
		        
		        $sapID = $setting["sap"];
		        
		        if(isset($arrSaps[$sapID]["settings"]))
		            $arrSaps[$sapID]["settings"][] = $setting;
		            else
		                $arrSaps[$sapID]["settings"] = array($setting);
		    }
		    		    
		    return($arrSaps);
		}
		
		
		private function a_______DRAW_GENENRAL______(){}
		
		
		/**
		 * get controls for client side
		 * eliminate only one setting in children
		 */
		private function getControlsForJS(){
			
			$controls = $this->settings->getArrControls(true);
			$arrChildren = $controls["children"];
			
			if(empty($arrChildren))
				return($controls);
			
			$arrChildrenNew = array();
			
			foreach($arrChildren as $name=>$arrChild){
				if(count($arrChild)>1)
					$arrChildrenNew[$name] = $arrChild;
			}
			
			$controls["children"] = $arrChildrenNew;
			
			return($controls);
		}
		
		
		/**
		 * draw wrapper start
		 */
		public function drawWrapperStart(){
			
			UniteFunctionsUC::validateNotEmpty($this->settingsMainClass, "settings main class not found, please use wide, inline or sidebar output");
			
			//get options
			$options = $this->getOptions();
			$strOptions = UniteFunctionsUC::jsonEncodeForHtmlData($options);
			
			//get controls
			$controls = $this->getControlsForJS();
			
			/*
			if(!empty($controls["children"])){
				dmp($controls);exit();
			}
			*/
			
			$addHtml = "";
			if(!empty($controls)){
				$strControls = UniteFunctionsUC::jsonEncodeForHtmlData($controls);
				$addHtml = " data-controls=\"{$strControls}\"";
			}
			
			
			if(!empty($this->addCss)):
			?>
				<!-- settings add css -->
				<style type="text/css">
					<?php echo $this->addCss?>
				</style>
			<?php
			endif;
			
			?>
			<div id="<?php echo $this->wrapperID?>" data-options="<?php echo $strOptions?>" <?php echo $addHtml?> autofocus="true" class="unite_settings_wrapper <?php echo $this->settingsMainClass?> unite-settings unite-inputs">
			
			<?php
		}
		
		
		/**
		 * draw wrapper end
		 */
		public function drawWrapperEnd(){
			
			?>
			
			</div>
			<?php 
		}
		
		
		/**
		 * function for override
		 */
		protected function setDrawOptions(){}
		
		/**
		 * 
		 * draw settings function
		 * @param $drawForm draw the form yes / no
		 * if filter sapid present, will be printed only current sap settings
		 */
		public function draw($formID, $drawForm = false){
			
			if(empty($this->settings))
				UniteFunctionsUC::throwError("No settings are inited. Please init the settings in output class");
			
			$this->setDrawOptions();
				
			$this->setFormID($formID);
			
			$this->drawWrapperStart();
			
			
			if($this->showSaps == true){
			     
			     switch($this->sapsType){
			         case self::SAPS_TYPE_INLINE:
			             $this->drawSapsTabs();
			         break;
			         case self::SAPS_TYPE_CUSTOM:
			             $this->drawSaps();
			         break;
			     }  
			     
			}
			
			
			if($drawForm == true){
				
				if(empty($formID))
					UniteFunctionsUC::throwError("The form ID can't be empty. you must provide it");
				
				?>
				<form name="<?php echo $formID?>" id="<?php echo $formID?>">
					<?php $this->drawSettings() ?>
				</form>
				<?php 				
			}else
				$this->drawSettings();
			
			?>
			
			<?php 
			
			$this->drawWrapperEnd();
			
		}

		
		/**
		 * draw wrapper before settings
		 */
		protected function drawSettings_before(){
		}
		
		
		/**
		* draw wrapper end after settings
		*/
		protected function drawSettingsAfter(){
		}
		

		/**
		 * draw single setting
		 */
		public function drawSingleSetting($name){
			
			$arrSetting = $this->settings->getSettingByName($name);
			
			$this->drawInputs($arrSetting);
			$this->drawInputAdditions($arrSetting);
		}
		
		
		/**
		 * function for override
		 */
		protected function drawSaps(){}
		
		
		/**
		 * draw saps tabs
		 */
		protected function drawSapsTabs(){
			
			$arrSaps = $this->settings->getArrSaps();
			
			?>
			<div class="unite-settings-tabs">
				
				<?php foreach($arrSaps as $key=>$sap){
					$text = $sap["text"];
					UniteFunctionsUC::validateNotEmpty($text,"sap $key text");
					
					$class = "";
					if($key == $this->activeSap)
						$class = "class='unite-tab-selected'";
					
					?>
					<a href="javascript:void(0)" <?php echo $class?> data-sapnum="<?php echo $key?>" onfocus="this.blur()"><?php echo $text?></a>
					<?php 
					
				}
				?>
				
			</div>
			<?php 
			
		}
		
		/**
		 * draw setting row by type
		 *
		 */
		private function drawSettingsRowByType($setting, $mode){
		    
		    switch($setting["type"]){
		        case UniteSettingsUC::TYPE_HR:
		            $this->drawHrRow($setting);
		            break;
		        case UniteSettingsUC::TYPE_STATIC_TEXT:
		            $this->drawTextRow($setting);
		            break;
		        default:
		            $this->drawSettingRow($setting, $mode);
		            break;
		    }
		    
		}
		
		
		/**
		 * draw settings - all together
		 */
		private function drawSettings_settings($filterSapID = null, $mode=null, $arrSettings = null){
		    
			if(is_null($arrSettings))
				$arrSettings = $this->arrSettings;
			
		    $this->drawSettings_before();
		    
		    foreach($arrSettings as $key=>$setting){
		            
		            if(isset($setting[UniteSettingsUC::PARAM_NODRAW]))
		                continue;
		                
		                if($filterSapID !== null){
		                    $sapID = UniteFunctionsUC::getVal($setting, "sap");
		                    if($sapID != $filterSapID)
		                        continue;
		                }
		                
		                $this->drawSettingsRowByType($setting, $mode);
		                
		        }
		        
		        $this->drawSettingsAfter();
		     
		}
		
		
		/**
		 * draw sap before override
		 * @param unknown $sap
		 */
		protected function drawSapBefore($sap, $key){
		    dmp("function for override");
		    
		}
		
		protected function drawSapAfter(){
		    dmp("function for override");
		}
		
		
		/**
		 * draw settings - all together
		 */
		private function drawSettings_saps($filterSapID = null, $mode){
		    
		    $arrSaps = $this->groupSettingsIntoSaps();
		    
		        //draw settings - advanced - with sections
		        foreach($arrSaps as $key=>$sap):
		        		
		        		$arrSettings = $sap["settings"];
		        
		                $this->drawSapBefore($sap, $key);
						
						$this->drawSettings_settings($filterSapID, $mode, $arrSettings);
						
						$this->drawSapAfter();
						
		        
		        endforeach;
		    
		}
		
		
		
		/**
		 * draw all settings
		 */
		public function drawSettings($filterSapID = null){
			
			$this->prepareToDraw();
			

			$arrSettings = $this->settings->getArrSettings();
			if(empty($arrSettings))
			    $arrSettings = array();
			    
			$this->arrSettings = $arrSettings;

			//set special mode
			$mode = "";
			if(count($arrSettings) == 1 && $arrSettings[0]["type"] == UniteSettingsUC::TYPE_EDITOR)
			    $mode = "single_editor";
			
			
			if($this->showSaps == true && $this->sapsType == self::SAPS_TYPE_ACCORDION)
			    $this->drawSettings_saps($filterSapID, $mode);
			else			     
			    $this->drawSettings_settings($filterSapID, $mode);
			
		  
		}
		
		
		
	}

?>