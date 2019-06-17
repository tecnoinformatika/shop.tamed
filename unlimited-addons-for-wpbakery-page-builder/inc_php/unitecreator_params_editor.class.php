<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorParamsEditor{
	
	const TYPE_MAIN = "main";
	const TYPE_ITEMS = "items";
	
	private $type = null;
	private $isHiddenAtStart = false;
	private $isItemsType = false;
	
	
	/**
	 * validate that the object is inited
	 */
	private function validateInited(){
		if(empty($this->type))
			UniteFunctionsUC::throwError("UniteCreatorParamsEditor error: editor not inited");
	}
	
	
	/**
	 * output html of the params editor
	 */
	public function outputHtmlTable(){
		
		$this->validateInited();
		
		$style="";
		if($this->isHiddenAtStart == true)
			$style = "style='display:none'";
				
		?>
			<div id="attr_wrapper_<?php echo $this->type ?>" class="uc-attr-wrapper" data-type="<?php echo $this->type?>" <?php echo $style?> >
				
				<table class="uc-table-params unite_table_items">
					<thead>
						<tr>
							<th width="50px">
							</th>
							<th width="200px">
								<?php _e("Title", ADDONLIBRARY_TEXTDOMAIN)?>
							</th>
							<th width="160px">
								<?php _e("Name", ADDONLIBRARY_TEXTDOMAIN)?>
							</th>
							<th width="100px">
								<?php _e("Type", ADDONLIBRARY_TEXTDOMAIN)?>
							</th>
							<th width="270px">
								<?php _e("Param", ADDONLIBRARY_TEXTDOMAIN)?>
							</th>
							<th width="200px">
								<?php _e("Operations", ADDONLIBRARY_TEXTDOMAIN)?>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				
				<div class="uc-text-empty-params mbottom_20" style="display:none">
						<?php _e("No Params Found", ADDONLIBRARY_TEXTDOMAIN)?>
				</div>
				
				<a class="uc-button-add-param unite-button-secondary" href="javascript:void(0)"><?php _e("Add Attribute", ADDONLIBRARY_TEXTDOMAIN);?></a>
				
				<?php if($this->isItemsType):?>
				
				<a class="uc-button-add-imagebase unite-button-secondary mleft_10" href="javascript:void(0)"><?php _e("Add Image Base Fields", ADDONLIBRARY_TEXTDOMAIN);?></a>
				
				<?php endif?>
			</div>
		
		<?php 
	}

	
	/**
	 * set hidden at start. must be run before init
	 */
	public function setHiddenAtStart(){
		$this->isHiddenAtStart = true;
	}
	
	
	/**
	 * 
	 * init editor by type
	 */
	public function init($type){
		
		switch($type){
			case self::TYPE_MAIN:
			break;
			case self::TYPE_ITEMS:
				$this->isItemsType = true;
			break;
			default:
				UniteFunctionsUC::throwError("Wrong editor type: {$type}");
			break;
		}
		
		
		$this->type = $type;
	}
	
	
}