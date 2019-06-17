<?php

/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

class UniteSettingsOutputUC extends UniteSettingsOutputUCWork{

	
	
	/**
	 * draw editor input
	 */
	protected function drawEditorInput($setting){
		
		$settingsID = UniteFunctionsUC::getVal($setting, "id");
		$name = UniteFunctionsUC::getVal($setting, "name");
		$class = self::getInputClassAttr($setting,"","",false);
		
		$editorParams = array();
		$editorParams['media_buttons'] = true;
		$editorParams['wpautop'] = false;
		$editorParams['editor_height'] = 200;
		$editorParams['textarea_name'] = $name;
		
		if(!empty($class))
			$editorParams['editor_class'] = $class;
		
		$addHtml = $this->getDefaultAddHtml($setting);
		
		$class = $this->getInputClassAttr($setting);
		
		$value = UniteFunctionsUC::getVal($setting, "value");
		
		?>
		<div class="unite-editor-setting-wrapper unite-editor-wp" <?php echo $addHtml?>>
		<?php 
			wp_editor($value, $settingsID, $editorParams);
		?>
		</div>
		<?php 
	}
	
	
}