<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorManagerInline extends UniteCreatorManager{

	private $startAddon;
	private $itemsType;
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		
		$this->type = self::TYPE_ITEMS_INLINE;
		
		$this->init();
	}
	
	/**
	 * validate that the start addon exists
	 */
	private function validateStartAddon(){
		
		if(empty($this->startAddon))
			UniteFunctionsUC::throwError("The start addon not given");
		
	}
	
	
	/**
	 * init the data from start addon
	 */
	private function initStartAddonData(){
		
		$this->itemsType = $this->startAddon->getItemsType();
		
		//set init data
		$arrItems = $this->startAddon->getArrItemsForConfig();
		
		$strItems = "";
		if(!empty($arrItems)){
			$strItems = json_encode($arrItems);
			$strItems = htmlspecialchars($strItems);
		}
		
		$addHtml = " data-init-items=\"{$strItems}\" ";
		
		$this->setManagerAddHtml($addHtml);
		
	}
	
	
	/**
	 * set start addon
	 */
	public function setStartAddon($addon){
		$this->startAddon = new UniteCreatorAddon();	//just for code completion
		$this->startAddon = $addon;
		
		$this->initStartAddonData();
				
	}
	
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){
		
		$arrMenuItem = array();
		$arrMenuItem["edit_item"] = __("Edit Item",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["remove_items"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["duplicate_items"] = __("Duplicate",ADDONLIBRARY_TEXTDOMAIN);
		
		return($arrMenuItem);
	}

	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		$arrMenuItemMultiple["remove_items"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItemMultiple["duplicate_items"] = __("Duplicate",ADDONLIBRARY_TEXTDOMAIN);
		return($arrMenuItemMultiple);
	}
	
	
	/**
	 * get item field menu
	 */
	protected function getMenuField(){
		$arrMenuField = array();
		$arrMenuField["add_item"] = __("Add Item",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuField["select_all"] = __("Select All",ADDONLIBRARY_TEXTDOMAIN);
		
		return($arrMenuField);
	}
	
	
	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){
		
		$this->validateStartAddon();
		
		$itemType = $this->startAddon->getItemsType();
		
		$buttonClass = "unite-button-primary button-disabled uc-button-item uc-button-add";
		
		//put add item button according the type
		switch($itemType){
			default:
			case UniteCreatorAddon::ITEMS_TYPE_DEFAULT:
			?>
 				<a data-action="add_item" type="button" class="<?php echo $buttonClass?>"><?php _e("Add Item",ADDONLIBRARY_TEXTDOMAIN)?></a>
			<?php 
			break;
			case UniteCreatorAddon::ITEMS_TYPE_IMAGE:
			?>
 				<a data-action="add_images" type="button" class="<?php echo $buttonClass?>"><?php _e("Add Images",ADDONLIBRARY_TEXTDOMAIN)?></a>
			<?php 
			break;
			case UniteCreatorAddon::ITEMS_TYPE_FORM:
				?>
 				<a data-action="add_form_item" type="button" class="<?php echo $buttonClass?>"><?php _e("Add Form Item",ADDONLIBRARY_TEXTDOMAIN)?></a>
 				<?php
			break;
		}
		
		?>
	 		<a data-action="select_all_items" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-select" data-textselect="<?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?>" data-textunselect="<?php _e("Unselect All",ADDONLIBRARY_TEXTDOMAIN)?>"><?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="duplicate_items" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php _e("Duplicate",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="remove_items" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php _e("Delete",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="edit_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php _e("Edit Item",ADDONLIBRARY_TEXTDOMAIN)?> </a>
		<?php 
	}
	
	
	/**
	 * put add edit item dialog
	 */
	private function putAddEditDialog(){
		
		$isLoadByAjax = $this->startAddon->isEditorItemsAttributeExists();
		
		
		$addHtml = "";
		if($isLoadByAjax == true){
			
			$addonID = $this->startAddon->getID();
			$addHtml = "data-initbyaddon=\"{$addonID}\"";
		}
		
		?>
			<div title="<?php _e("Edit Item",ADDONLIBRARY_TEXTDOMAIN)?>" class="uc-dialog-edit-item" style="display:none">
				<div class="uc-item-config-settings" autofocus="true" <?php echo $addHtml?>>
					
					<?php if($isLoadByAjax == false): 
						
						if($this->startAddon)
						$this->startAddon->putHtmlItemConfig();
					?>
					<?php else:	 //load by ajax?>
						
						<div class="unite-dialog-loader-wrapper">
							<div class="unite-dialog-loader"><?php _e("Loading Settings", ADDONLIBRARY_TEXTDOMAIN)?>...</div>
						</div>
						
					<?php endif?>
					
				</div>
			</div>
		<?php 
	}
	
	
	/**
	 * put form dialog
	 */
	protected function putFormItemsDialog(){
		
		$objDialogParam = new UniteCreatorDialogParam();
		$objDialogParam->init(UniteCreatorDialogParam::TYPE_FORM_ITEM, $this->startAddon);
		$objDialogParam->outputHtml();
		
	}
	
	
	/**
	 * put additional html here
	 */
	protected function putAddHtml(){
				
		if($this->itemsType == UniteCreatorAddon::ITEMS_TYPE_FORM)
			$this->putFormItemsDialog();
		else
			$this->putAddEditDialog();
		
	}
	
	
	/**
	 * init the addons manager
	 */
	protected function init(){
		
		$this->hasCats = false;
				
		parent::init();
	}
	
	
}