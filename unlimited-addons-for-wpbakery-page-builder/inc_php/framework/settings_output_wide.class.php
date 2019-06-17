<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


	class UniteSettingsOutputWideUC extends UniteCreatorSettingsOutput{
		
		
		/**
		 * constuct function
		 */
		public function __construct(){
			$this->isParent = true;
			self::$serial++;
			$this->wrapperID = "unite_settings_wide_output_".self::$serial;
			$this->settingsMainClass = "unite_settings_wide";
		}
		
		
		/**
		 * draw settings row
		 * @param $setting
		 * modes: single_editor (only 1 setting, editor type)
		 */
		protected function drawSettingRow($setting, $mode = ""){
						
			//set cellstyle:
			$cellStyle = "";
			if(isset($setting[UniteSettingsUC::PARAM_CELLSTYLE])){
				$cellStyle .= $setting[UniteSettingsUC::PARAM_CELLSTYLE];
			}
			
			if($cellStyle != "")
				 $cellStyle = "style='".$cellStyle."'";
			
			$textStyle = $this->drawSettingRow_getTextStyle($setting);
			
			$rowStyle = $this->drawSettingRow_getRowStyle($setting);
			
			$rowClass = $this->drawSettingRow_getRowClass($setting);
			
			$text = $this->drawSettingRow_getText($setting);
			
			$description = UniteFunctionsUC::getVal($setting,"description");

			
			//set settings text width:
			$textWidth = "";
			if(isset($setting["textWidth"])) 
				$textWidth = 'width="'.$setting["textWidth"].'"';
			
			$addField = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_ADDFIELD);
			
			$drawTh = true;
			$tdHtmlAdd = "";			
			if($mode == "single_editor")
				$drawTh = false;
			
			if(empty($text))
				$drawTh = false;
				
			if($drawTh == false)
				$tdHtmlAdd = " colspan=2";
				
			?>
						
			<?php
			if(!empty($addField)):
				
				$addSetting = $this->settings->getSettingByName($addField);
				UniteFunctionsUC::validateNotEmpty($addSetting,"AddSetting {$addField}");
			
				//set hidden
				$rowStyleAdd = "";
				if(isset($addSetting["hidden"]))
					$rowStyleAdd = "display:none;";
				
				if(!empty($rowStyleAdd))
					$rowStyleAdd = "style='$rowStyleAdd'";
							
				$addSettingText = UniteFunctionsUC::getVal($addSetting,"text","");
				$addSettingText = str_replace(" ","&nbsp;", $addSettingText);
				$tdSettingAdd = "";
				if(!empty($addSetting)){
					$tdSettingAdd = ' class="unite-settings-onecell" colspan="2"';
				}
				
				?>
				<tr <?php echo $rowClass?> valign="top">
				
				<?php if(empty($addSettingText)):?>
					
					<th <?php echo $textStyle?> scope="row" <?php echo $textWidth ?>>
						<?php if($this->showDescAsTips == true): ?>
					    	<span class='setting_text' title="<?php echo $description?>"><?php echo $text?></span>
					    <?php else:?>
					    	<?php echo $text?>
					    <?php endif?>
					</th>
					
				<?php endif?>
				
				<td <?php echo $cellStyle?> <?php echo $tdSettingAdd?>>
					
					<span id="<?php echo $setting["id_row"]?>" <?php echo $rowStyle ?>>
						
						<?php if(!empty($addSettingText)):?>
						<span class='setting_onecell_text'><?php echo $text?></span>
						<?php endif?>
						
							<?php 
								$this->drawInputs($setting);
								$this->drawInputAdditions($setting);
							?>
							
						<?php if(!empty($addSettingText)):?>
							<span class="setting_onecell_horsap"></span>
						<?php endif?>
					</span>
					
					<span id="<?php echo $addSetting["id_row"]?>" <?php echo $rowStyleAdd ?>>
						<span class='setting_onecell_text'><?php echo $addSettingText?></span>				
						<?php
							$this->drawInputs($addSetting);
							$this->drawInputAdditions($addSetting);
						?>
					</span>
				</td>
				</tr>
				<?php
			?>
			<?php else:	?>
				<tr id="<?php echo $setting["id_row"]?>" <?php echo $rowStyle ?> <?php echo $rowClass?> valign="top">
					
					<?php if($drawTh == true):?>
					
					<th <?php echo $textStyle?> scope="row" <?php echo $textWidth ?>>
						<?php if($this->showDescAsTips == true): ?>
					    	<span class='setting_text' title="<?php echo $description?>"><?php echo $text?></span>
					    <?php else:?>
					    	<?php echo $text?>
					    <?php endif?>
					</th>
					
					<?php endif?>
					
					<td <?php echo $cellStyle?> <?php echo $tdHtmlAdd?>>
						<?php 
							$this->drawInputs($setting);
							$this->drawInputAdditions($setting);
						?>
					</td>
				</tr>
			<?php
			endif;
		}

		/**
		 * draw hr row
		 * @param $setting
		 */
		protected function drawHrRow($setting){

			//set hidden
			$rowStyle = "";
			if(isset($setting["hidden"])) $rowStyle = "style='display:none;'";
		
			$class = UniteFunctionsUC::getVal($setting, "class");
			if(!empty($class))
				$class = "class='$class'";
		
			?>
			<tr id="<?php echo $setting["id_row"]?>" <?php echo $rowStyle ?>>
				<td colspan="4" align="left" style="text-align:left;">
					 <hr <?php echo $class; ?> /> 
				</td>
			</tr>
			<?php 
		}
		
		
		
		/**
		 * draw text row
		 * @param unknown_type $setting
		 */
		protected function drawTextRow($setting){
		
			//set cell style
			$cellStyle = "";
			if(isset($setting["padding"]))
				$cellStyle .= "padding-left:".$setting["padding"].";";
		
			if(!empty($cellStyle))
				$cellStyle="style='$cellStyle'";
		
			//set style
			
			$tdHtmlAdd = 'colspan="2"'; 
			
			$label = UniteFunctionsUC::getVal($setting, "label");
			if(!empty($label))
				$tdHtmlAdd = "";
			
			$rowClass = $this->drawSettingRow_getRowClass($setting);
			
			$rowStyle = $this->drawSettingRow_getRowStyle($setting);
			
			$classAdd = UniteFunctionsUC::getVal($setting, UniteSettingsUC::PARAM_CLASSADD);
						
			if(!empty($classAdd))
				$classAdd = " ".$classAdd;
			
			?>
				<tr id="<?php echo $setting["id_row"]?>" <?php echo $rowStyle ?> <?php echo $rowClass?>  valign="top">
					<?php if(!empty($label)):?>
					<th>
						<?php echo $label?>
					</th>
					<?php endif?>
					<td <?php echo $tdHtmlAdd?> <?php echo $cellStyle?>>
						<span class="unite-settings-static-text<?php echo $classAdd?>"><?php echo $setting["text"]?></span>
					</td>
				</tr>
			<?php 
		}
		
		
		/**
		 * draw wrapper before settings
		 */
		protected function drawSettings_before(){
			?><table class='unite_table_settings_wide'><?php
		}
		
		
		/**
		 * draw wrapper end after settings
		 */
		protected function drawSettingsAfter(){
			
			?></table><?php
		}
		
		
	
	}
?>