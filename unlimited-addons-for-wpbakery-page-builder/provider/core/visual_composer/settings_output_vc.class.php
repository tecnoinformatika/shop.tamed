<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');



/**
 * settings output for visual composer
 */

	class UniteSettingsOutputVC_UC extends UniteCreatorSettingsOutput{
		

		public function __construct(){
			
			$this->isParent = true;
			
		}
		
		
		/**
		 * get setting html by name name
		 */
		public function VCgetSettingHtmlByName($name){
			
			$this->validateInited();
			$setting = $this->settings->getSettingByName($name);
			
			
			$type = $setting["type"];
			switch($type){
				case UniteSettingsUC::TYPE_HR:
				case UniteSettingsUC::TYPE_STATIC_TEXT:
					return("");
				break;
			}
			
			$prefix = $this->settings->getIDPrefix();
			
			try{
				
				ob_start();
				?>
				<div id="uc_vc_setting_wrapper_<?php echo $name?>" class="uc_vc_setting_wrapper unite-settings unite_settings_wide unite-inputs" data-idprefix="<?php echo $prefix?>">
				<?php 
				
				$this->drawInputs($setting);
				$this->drawInputAdditions($setting, false);
				
				?>
				</div>
				<?php
				
				$contents = ob_get_contents();
				ob_clean();
				
			}catch(Exception $e){
				ob_clean();
				$message = $e->getMessage();
				$contents = "Error: ".$message;
			}
			return($contents);
		}
		
		
	}