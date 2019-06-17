function UCManagerActionsPages(){
	
	var g_objCats = new UCManagerAdminCats(), g_objItems;
	var g_manager = new UCManagerAdmin();
	var g_options, g_addonsType = "", g_emptyAddonsWrapper;
	var g_objTooltip, g_objListWrapper,g_objWrapper, g_name;
	var g_objSettingsCategory, g_settingsPageProps;
	
	var g_temp = {
		hasCats: true
	};
	
	if(!g_ucAdmin){
		var g_ucAdmin = new UniteAdminUC();
	}
	
	
	/**
	 * run action function, return if found true/false
	 */
	function runActionFunctions(action,data){
		
		var arrActionFunctions = g_manager.getActionFunctions();
		if(!arrActionFunctions)
			return(false);
				
		if(arrActionFunctions.length == 0)
			return(false);
		
		var isFound = false;
		
		jQuery.each(arrActionFunctions, function(index, func){
			if(typeof func != "function")
				throw new Error(func+" is not a function");
			
			isFound = func(action, data);
			if(isFound == true)
				return(true);
		});
		
		
		return(isFound);
	}
	
	/**
	 * add common ajax data
	 */
	function addCommonAjaxData(data){
		
		data["addontype"] = g_addonsType;
		data["manager_name"] = g_manager.getManagerName();
		
		var passData = g_manager.getManagerPassData();
		if(passData)
			data["manager_passdata"] = passData;
		
		
		return(data);
	}
	
	/**
	 * get category items
	 */
	function getSelectedCatPages(){
		
		var catID = 0;
		
		if(g_objCats){
			catID = g_objCats.getSelectedCatID();
			if(catID == -1)
				return(false);
		}
				
		jQuery("#items_loader").show();
		jQuery("#uc_list_items").hide();
		g_objItems.hideNoAddonsText();
		
		
		var data = {};
		data["catID"] = catID;
		data["response_combo"] = false;
		data = addCommonAjaxData(data);	
		
		g_ucAdmin.ajaxRequest("get_manager_cat_pages", data, function(response){
			
			var htmlItems = response["html_items"];
			g_objItems.setHtmlListItems(htmlItems);
			
			g_objItems.checkSelectRelatedItems();
			
			//patch for showing no addons html if not items on all
			if(g_emptyAddonsWrapper){
				var numItems = jQuery("#uc_list_items li").length;
				if(numItems == 0){
					jQuery("#no_items_text").hide();
					g_emptyAddonsWrapper.show();
					g_manager.updateGlobalHeight(null, 390);
				}
			}
			
		});
	}
	

	
	/**
	 * on item button click
	 */
	this.runItemAction = function(action, data){
		
		switch(action){
			case "update_order":
				updateItemsOrder();
			break;
			case "get_cat_items":
				getSelectedCatPages(data);				
			break;
			case "page_props":
				openPagePropertiesDialog();
			break;
			
			default:
				
				var isFound = runActionFunctions(action, data);
						
				if(isFound == false)
					trace("wrong addon action: " + action);
				
			break;
		}
		
	}
	
	
	/**
	 * init items
	 */
	function initItems(){
		
		//init items related functions
	
	}

	
	/**
	 * set combo lists from response
	 */
	function setHtmlListCombo(response){
		var htmlItems = response["html_items"];
		
		var htmlCats = response.htmlCats;
		
		g_objItems.setHtmlListItems(htmlItems);
		
		if(g_objCats)
			g_objCats.setHtmlListCats(htmlCats);
	}

	function ___________PAGE_PROPERTIES________________(){}	//sap for outline	
	
	/**
	 * open page properties dialaog
	 */
	function openPagePropertiesDialog(){
		
		var selectedItem = g_objItems.getSelectedItem();
		var pageTitle = selectedItem.data("title");
		var pageID = selectedItem.data("id");
		
		var options = {
				minWidth: 900,
				title:"Edit Page: "+pageTitle
		};
		
		var objDialog = jQuery("#uc_dialog_page_properties");
		g_ucAdmin.validateDomElement(objDialog, "dialog properties");
		
		var objLoader = objDialog.find(".unite-dialog-loader");
		
		var objContent = objDialog.find(".unite-dialog-content");
		
		objContent.html("").hide();
		objLoader.show();
		
		g_ucAdmin.openCommonDialog("#uc_dialog_page_properties", function(){
			
			var data = {"pageid":pageID};
			
			data = addCommonAjaxData(data);	
			
			g_ucAdmin.ajaxRequest("get_manager_page_settings_html", data, function(response){
				
				objLoader.hide();
				objContent.show().html(response.html);
				
				//init settings
				var objSettingsWrapper = objContent.find(".unite_settings_wrapper");
				g_ucAdmin.validateDomElement(objSettingsWrapper, "page properties settings wrapper");
				
				g_settingsPageProps = new UniteSettingsUC();
				g_settingsPageProps.init(objSettingsWrapper);
				
			});
						
		} ,options);
		
	}
	
	
	/**
	 * init page props dialog
	 */
	function initPagePropertiesDialog(){
			
		// set update title onenter function
		jQuery("#uc_dialog_page_properties_action").click(updatePageProperties);
		
	}
	
	
	/**
	 * update page properties
	 */
	function updatePageProperties(){
		
		var selectedItem = g_objItems.getSelectedItem();
		var pageID = selectedItem.data("id");
		
		var objPageProps = g_settingsPageProps.getSettingsValues();
		
		var data = {
				layoutid: pageID,
				params: objPageProps
			};
		
		data = addCommonAjaxData(data);
		
		g_ucAdmin.dialogAjaxRequest("uc_dialog_page_properties", "update_manager_page_params", data, function(response){
			
			var htmlItem = response["html_item"];
			g_objItems.replaceItemHtml(selectedItem, htmlItem);
			
		});
		
	}
	
	
	function ___________ADDONS_RELATED_OPERATIONS________________(){}	//sap for outline	

	
	
	/**
	 * get category items
	 */
	function getSelectedCatLayouts(){
		
		var catID = 0;
		
		dmp("get cat layouts");
		return(false);
		
		if(g_objCats){
			catID = g_objCats.getSelectedCatID();
			if(catID == -1)
				return(false);
		}
		
		var catData = g_objCats.getSelectedCatData();
		var catTitle = catData["title"];
		
		jQuery("#items_loader").show();
		jQuery("#uc_list_items").hide();
		g_objItems.hideNoAddonsText();
		
		var activeFilter = getFitlerActive();
		var catalogFilter = getFilterCatalog();
		
		var data = {};
		data["catID"] = catID;
		data["filter_active"] = activeFilter;
		
		if(catalogFilter)
			data["filter_catalog"] = catalogFilter;
		
		data["response_combo"] = true;
		data["addontype"] = g_addonsType;
		data["title"] = catTitle;
		data["isweb"] = catData["isweb"];
		
		g_ucAdmin.ajaxRequest("get_cat_addons", data, function(response){
			
			setHtmlListCombo(response);
			
			g_objItems.checkSelectRelatedItems();
			
			//patch for showing no addons html if not items on all
			if(catID == "all" && activeFilter == "all" && g_emptyAddonsWrapper){
				var numItems = jQuery("#uc_list_items li").length;
				if(numItems == 0){
					jQuery("#no_items_text").hide();
					g_emptyAddonsWrapper.show();
					g_manager.updateGlobalHeight(null, 390);
				}
			}
			
			
		});
	}
	
	
	/**
	 * refresh categories list
	 */
	function refreshCatList(){
		
		if(!g_objCats)
			throw new Error("The categories don't exists");
		
		var selectedCatID = g_objCats.getSelectedCatID();
		var data = {};
		data["selected_catid"] = selectedCatID;
		data["filter_active"] = getFitlerActive();
		data["filter_catalog"] = getFilterCatalog();
		
		g_manager.ajaxRequestManager("get_catlist",data , "", function(response){
						
			var htmlCats = response.htmlCats;
			
			g_objCats.setHtmlListCats(htmlCats);
			
		});
		
	}
	
	/**
	 * remove items
	 */
	function removeAddons(arrIDs){
		
		var data = {};
		data.arrAddonsIDs = arrIDs;
		
		data.catid = 0;
		
		if(g_objCats)
			data.catid = g_objCats.getSelectedCatID();
		
		g_manager.ajaxRequestManager("remove_addons",data, g_uctext.removing_addons, function(response){
			setHtmlListCombo(response);
		});
		
	}
	
	
    /**
     * remove selected items
     */
    function removeSelectedAddons(){
		if(g_ucAdmin.isButtonEnabled(this) == false)
			return(false);
		
		if(confirm(g_uctext.confirm_remove_addons) == false)
			return(false);
		
		var arrIDs = g_objItems.getSelectedItemIDs();
		
		removeAddons(arrIDs);
    }
	
	
	/**
	 * update items order in server
	 */
	function updateItemsOrder(){
		
		var arrIDs = g_objItems.getArrItemIDs(false, true);
		
		var data = {layouts_order:arrIDs};
		g_manager.ajaxRequestManager("update_layouts_order",data,g_uctext.updating_addons_order);
	}
	
	
	function ___________EVENTS________________(){}	
	
	
	/**
	 * on hide empty text, hide no addons message as well
	 */
	function onItemHideEmptyText(){
		if(g_emptyAddonsWrapper)
			g_emptyAddonsWrapper.hide();
	}
	
				
	
	/**
	 * init events
	 */
	function initEvents(){
		g_manager.onEvent(g_manager.events.ITEM_HIDE_EMPTY_TEXT, onItemHideEmptyText);
		
	}
	
	
	/**
	 * init the actions
	 */
	this.init = function(objManager){
		
		g_manager = objManager;
		
		g_name = g_manager.getManagerName();
		
		//set addons type
		g_objWrapper = g_manager.getObjWrapper();
		g_addonsType = g_objWrapper.data("addonstype");
		if(!g_addonsType)
			g_addonsType = "";
			
		g_manager.setObjAjaxAddData({addontype: g_addonsType});
		
		//init empty addons wrapper
		g_emptyAddonsWrapper = jQuery("#uc_empty_addons_wrapper");
		if(g_emptyAddonsWrapper.length == 0)
			g_emptyAddonsWrapper = null;
		
		g_temp.hasCats = g_manager.isHasCats();
		
		
		//init cats
		if(g_temp.hasCats == true){
			g_objCats = g_manager.getObjCats();
			g_objCats.setObjAjaxAddData({type: g_addonsType});
		}else{
			g_objCats = null;
		}
		
		
		//init items
		g_objItems = g_manager.getObjItems();
		g_objItems.setSpacesBetween(15,15);
		
		g_manager.initItems();
		
		initItems();
		
		initPagePropertiesDialog();
		
		initEvents();
	};
	
	
}