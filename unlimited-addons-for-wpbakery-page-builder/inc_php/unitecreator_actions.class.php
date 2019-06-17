<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorActions{

	
	/**
	 * on update layout response, function for override
	 */
	protected function onUpdateLayoutResponse($response){
		
		$isUpdate = $response["is_update"];
		
		//create
		if($isUpdate == false){
			
			HelperUC::ajaxResponseData($response);
			
		}else{
			//update
			
			$message = $response["message"];
			HelperUC::ajaxResponseSuccess($message);
		}
		
	}
	
	/**
	 * get data array from request
	 */
	private function getDataFromRequest(){
		
		$data = UniteFunctionsUC::getPostGetVariable("data","",UniteFunctionsUC::SANITIZE_NOTHING);
		if(empty($data))
			$data = $_REQUEST;
		
		if(is_string($data)){
						
			$arrData = (array)json_decode($data);
			
			if(empty($arrData)){
				$arrData = stripslashes(trim($data));
				$arrData = (array)json_decode($arrData);
			}
						
			$data = $arrData;
		}
		
		return($data);
	}
	
	
	/**
	 * on ajax action
	 */
	public function onAjaxAction(){
		
		$actionType = UniteFunctionsUC::getPostGetVariable("action","",UniteFunctionsUC::SANITIZE_KEY);
		
		if($actionType != "unitecreator_ajax_action")
			return(false);
		
		$operations = new UCOperations();
		$addons = new UniteCreatorAddons();
		$assets = new UniteCreatorAssetsWork();
		$categories = new UniteCreatorCategories();
		$layouts = new UniteCreatorLayouts();
		$webAPI = new UniteCreatorWebAPI();
		$manager = new UniteCreatorManagerAddons();
		$templates = new UniteCreatorTemplates();
		
		$action = UniteFunctionsUC::getPostGetVariable("client_action","",UniteFunctionsUC::SANITIZE_KEY);
		
		$data = $this->getDataFromRequest();
				
		$addonType = $addons->getAddonTypeFromData($data);
		if($addonType)
			$manager->setAddonType($addonType, $addonType);
		
		$data = UniteFunctionsUC::convertStdClassToArray($data);
		
		$data = UniteProviderFunctionsUC::normalizeAjaxInputData($data);
		
		//get pages manager
		$managerPages = new UniteCreatorManagerPages();
		$managerPages = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_GET_MANAGER_OBJECT_BYDATA, $managerPages, $data);
		
		
		try{
		
			if(method_exists("UniteProviderFunctionsUC", "verifyNonce")){
				$nonce = UniteFunctionsUC::getPostGetVariable("nonce","",UniteFunctionsUC::SANITIZE_NOTHING);
				UniteProviderFunctionsUC::verifyNonce($nonce);
			}
			switch($action){
				
				case "remove_category":
					$response = $categories->removeFromData($data);
				
					HelperUC::ajaxResponseSuccess(__("The category deleted successfully",ADDONLIBRARY_TEXTDOMAIN),$response);
					break;
				case "update_category":
					$categories->updateFromData($data);
					HelperUC::ajaxResponseSuccess(__("Category updated",ADDONLIBRARY_TEXTDOMAIN));
					break;
				case "update_cat_order":
					$categories->updateOrderFromData($data);
					HelperUC::ajaxResponseSuccess(__("Order updated",ADDONLIBRARY_TEXTDOMAIN));
				break;
				
				case "get_category_settings_html":
					$responeData = $manager->getCatSettingsHtmlFromData($data);
					HelperUC::ajaxResponseData($responeData);
				break;
				case "get_cat_addons":
					$responeData = $manager->getCatAddonsHtmlFromData($data);
					
					HelperUC::ajaxResponseData($responeData);
				break;
				case "get_manager_cat_pages":
					
					$managerPages->setManagerNameFromData($data);
					
					$responseData = $managerPages->getCatPagesHtmlFromData($data);
					
					HelperUC::ajaxResponseData($responseData);
					
				break;
				case "get_manager_page_settings_html":
					
					$managerPages->setManagerNameFromData($data);
					$responseData = $managerPages->getPagePropsHtmlFromData($data);
					
					HelperUC::ajaxResponseData($responseData);
					
				break;
				case "update_manager_page_params":
					$managerPages->setManagerNameFromData($data);
					$responseData = $managerPages->updatePageParamsFromData($data);
					
					HelperUC::ajaxResponseData($responseData);
				break;
				case "get_catlist":
					$responeData = $categories->getCatListFromData($data);
					
					HelperUC::ajaxResponseData($responeData);
				break;
				case "get_layouts_categories":
					$responeData = $categories->getLayoutsCatsListFromData($data);
					HelperUC::ajaxResponseData($responeData);
				break;
				case "update_addon":
					$response = $addons->updateAddonFromData($data);
					HelperUC::ajaxResponseSuccess(__("Updated.",ADDONLIBRARY_TEXTDOMAIN),$response);
				break;
				case "get_addon_bulk_dialog":
					$response = $operations->getAddonBulkDialogFromData($data);
					HelperUC::ajaxResponseData($response);
				break;
				case "update_addons_bulk":
					$addons->updateAddonsBulkFromData($data);
					$response = $operations->getAddonBulkDialogFromData($data);
					HelperUC::ajaxResponseData($response);
				break;
				case "delete_addon":
					$addons->deleteAddonFromData($data);
					HelperUC::ajaxResponseSuccess(__("The addon deleted successfully",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "add_category":
					$catData = $categories->addFromData($data);
					HelperUC::ajaxResponseData($catData);
				break;
				case "add_addon":
					
					if(GlobalsUC::$permisison_add == false)
						UniteFunctionsUC::throwError("Operation not permitted");
					
					$response = $addons->createFromManager($data);
					
					HelperUC::ajaxResponseSuccess(__("Addon added successfully",ADDONLIBRARY_TEXTDOMAIN), $response);
				break;
				case "update_addon_title":
					$addons->updateAddonTitleFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Addon updated successfully",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "update_addons_activation":
					$addons->activateAddonsFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Addons updated successfully",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "remove_addons":
					$response = $addons->removeAddonsFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Addons Removed",ADDONLIBRARY_TEXTDOMAIN), $response);
				break;
				case "update_addons_order":
					$addons->saveOrderFromData($data);

					HelperUC::ajaxResponseSuccess(__("Order Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "update_layouts_order":
					$layouts->updateOrderFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Order Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "move_addons":
					$response = $addons->moveAddonsFromData($data);
					HelperUC::ajaxResponseSuccess(__("Done Operation",ADDONLIBRARY_TEXTDOMAIN),$response);
				break;
				case "duplicate_addons":
					$response = $addons->duplicateAddonsFromData($data);
					HelperUC::ajaxResponseSuccess(__("Addons Duplicated",ADDONLIBRARY_TEXTDOMAIN),$response);
				break;
				case "get_addon_config_html":
					
					$response = $addons->getAddonConfigHTML($data);
					
					HelperUC::ajaxResponseData($response);
				break;
				case "get_addon_settings_html":
					
					$html = $addons->getAddonSettingsHTMLFromData($data);
					HelperUC::ajaxResponseData(array("html"=>$html));
				break;
				case "get_addon_item_settings_html":
				
					$html = $addons->getAddonItemsSettingsHTMLFromData($data);
					HelperUC::ajaxResponseData(array("html"=>$html));
				break;
				case "get_addon_editor_data":
					$response = $addons->getAddonEditorData($data);
					HelperUC::ajaxResponseData($response);
				break;
				case "get_addon_output_data":
					$response = $addons->getLayoutAddonOutputData($data);
					
					HelperUC::ajaxResponseData($response);
				break;
				case "show_preview":
					$addons->showAddonPreviewFromData($data);
					exit();
				break;
				case "save_addon_defaults":
					$addons->saveAddonDefaultsFromData($data);
					HelperUC::ajaxResponseSuccess(__("Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "save_test_addon":
					$addons->saveTestAddonData($data);
					HelperUC::ajaxResponseSuccess(__("Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "get_test_addon_data":
					$response = $addons->getTestAddonData($data);
					HelperUC::ajaxResponseData($response);
				break;
				case "delete_test_addon_data":
					$addons->deleteTestAddonData($data);
					HelperUC::ajaxResponseSuccess(__("Test data deleted",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "export_addon":
					$addons->exportAddon($data);
					exit();
				break;
				case "export_cat_addons":
					$addons->exportCatAddons($data);
				break;
				case "import_addons":
					$response = $addons->importAddons($data);
					
					HelperUC::ajaxResponseSuccess(__("Addons Imported",ADDONLIBRARY_TEXTDOMAIN),$response);
				break;
				case "import_layouts":
					$urlRedirect = $layouts->importLayouts($data);
					
					HelperUC::ajaxResponseSuccessRedirect(HelperUC::getText("layout_imported"), $urlRedirect);
				break;
				case "get_version_text":
					$content = HelperHtmlUC::getVersionText();
					HelperUC::ajaxResponseData(array("text"=>$content));
				break;
				case "update_plugin":
				
					if(method_exists("UniteProviderFunctionsUC", "updatePlugin"))
						UniteProviderFunctionsUC::updatePlugin();
					else{
						echo "Functionality Don't Exists";
						exit();
					}
				
				break;
				case "update_general_settings":
					$operations->updateGeneralSettingsFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Settings Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "update_global_layout_settings":
					
					UniteCreatorLayout::updateLayoutGlobalSettingsFromData($data);
					
					HelperUC::ajaxResponseSuccess(__("Settings Saved",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "update_layout":
					
					$response = $layouts->updateLayoutFromData($data);
					
					$this->onUpdateLayoutResponse($response);
				break;
				case "update_layout_category":
					$response = $layouts->updateLayoutCategoryFromData($data);
					HelperUC::ajaxResponseSuccess(__("Category Updated",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "update_layout_params":
					
					$response = $layouts->updateParamsFromData($data);
					HelperUC::ajaxResponseSuccess(__("Page Updated",ADDONLIBRARY_TEXTDOMAIN));
				break;
				case "delete_layout":
					
					$layouts->deleteLayoutFromData($data);
					$urlLayouts = HelperUC::getViewUrl_LayoutsList();
					
					HelperUC::ajaxResponseSuccessRedirect(HelperUC::getText("layout_deleted"), $urlLayouts);
					
				break;
				case "duplicate_layout":
					
					$layouts->duplicateLayoutFromData($data);
					
					$urlLayouts = HelperUC::getViewUrl_LayoutsList();
					
					HelperUC::ajaxResponseSuccessRedirect(HelperUC::getText("layout_duplicated"), $urlLayouts);
					
				break;
				case "export_layout":
					$layouts->exportLayout();
					exit();
				break;
				case "activate_product":
					
					$expireDays = $webAPI->activateProductFromData($data);
					
					HelperUC::ajaxResponseSuccess("Product Activated",array("expire_days"=>$expireDays));
				break;
				case "deactivate_product":
					
					$webAPI->deactivateProduct();
					
					HelperUC::ajaxResponseSuccess("Product Deactivated, please refresh the page");
				break;
				case "check_catalog":
					$isForce = UniteFunctionsUC::getVal($data, "force");
					$isForce = UniteFunctionsUC::strToBool($isForce);
					
					$response = $webAPI->checkUpdateCatalog();
					HelperUC::ajaxResponseData($response);
				break;
				case "install_catalog_addon":
					$addonID = $webAPI->installCatalogAddonFromData($data);
					HelperUC::ajaxResponseSuccess(__("Addon Installed", ADDONLIBRARY_TEXTDOMAIN),array("addonid"=>$addonID));
				break;
				case "install_catalog_page":
					$pageID = $webAPI->installCatalogPageFromData($data);
					HelperUC::ajaxResponseSuccess(__("Page Installed", ADDONLIBRARY_TEXTDOMAIN),array("layoutid"=>$pageID));
				break;
				case "update_addon_from_catalog":	//by id
					$urlRedirect = $addons->updateAddonFromCatalogFromData($data);
					HelperUC::ajaxResponseSuccessRedirect(__("Addon Updated",ADDONLIBRARY_TEXTDOMAIN), $urlRedirect);
				break;
				case "create_update_template":
					$templateID = $templates->createUpdateTemplateFromData($data);
					
					$urlRedirect = HelperUC::getViewUrl_Template($templateID);
					
					HelperUC::ajaxResponseSuccessRedirect(__("Template Created",ADDONLIBRARY_TEXTDOMAIN), $urlRedirect);
				break;
				case "delete_template":
					
					$templates->deleteTemplateFromData($data);
					$urlTemplates = HelperUC::getViewUrl_Templates_List();
					
					HelperUC::ajaxResponseSuccessRedirect(__("Template Deleted", ADDONLIBRARY_TEXTDOMAIN), $urlTemplates);
					
				break;
				
				default:
					
					//check assets
					$found = $assets->checkAjaxActions($action, $data);

					if(!$found)
						$found = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_ADMIN_AJAX_ACTION, $found, $action, $data);
										
					if(!$found)
						HelperUC::ajaxResponseError("wrong ajax action: <b>$action</b> ");
				break;
			}
		
		}
		catch(Exception $e){
			$message = $e->getMessage();
		
			$errorMessage = $message;
			if(GlobalsUC::SHOW_TRACE == true){
				$trace = $e->getTraceAsString();
				$errorMessage = $message."<pre>".$trace."</pre>";
			}
		
			HelperUC::ajaxResponseError($errorMessage);
		}
		
		//it's an ajax action, so exit
		HelperUC::ajaxResponseError("No response output on <b> $action </b> action. please check with the developer.");
		exit();
		
	}
	
	
	
	/**
	 * on ajax action
	 */
	public function onAjaxFrontAction(){
		
		$actionType = UniteFunctionsUC::getPostGetVariable("action","",UniteFunctionsUC::SANITIZE_KEY);
		
		if($actionType != "unitecreator_ajax_action")
			return(false);
		
		$action = UniteFunctionsUC::getPostGetVariable("client_action","",UniteFunctionsUC::SANITIZE_KEY);
		$data = $this->getDataFromRequest();
		
		try{
					
			switch($action){
				case "send_form":
					
					$objForm = new UniteCreatorForm();
					$objForm->sendFormFromData($data);
					
					HelperUC::ajaxResponseSuccess("Form Sent");
					
				break;
				default:
					
					HelperUC::ajaxResponseError("wrong ajax action: <b>$action</b> ");
				break;
			}
			
		}
		catch(Exception $e){
			$message = $e->getMessage();
		
			$errorMessage = $message;
			if(GlobalsUC::SHOW_TRACE == true){
				$trace = $e->getTraceAsString();
				$errorMessage = $message."<pre>".$trace."</pre>";
			}
		
			HelperUC::ajaxResponseError($errorMessage);
		}
		
		
		//it's an ajax action, so exit
		HelperUC::ajaxResponseError("No response output on <b> $action </b> action. please check with the developer.");
		exit();		
	}
	
	
	
}