
/**
 * browser object
 */
function UniteCreatorBrowser(){
	
	var g_objWrapper, g_objTabsWrapper, g_objBackButton;
	var g_objLoader;
	var g_objConfigWrapper, g_objCatalog, g_objHeaderMenu, g_objDialogConfig;
	var g_dialogActivation, g_objSearchInput;
		
	var g_objCache = {};
	
	//return events to the caller with g_temp.funcResponse
	this.events = {
			LOADING_ADDON: "loading_addon",
			ADDON_DATA: "addon_data"
	};
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	
	//temp vars
	var g_temp = {
			funcResponse: null,
			addonType: "",
			isPages:false,
			isDialogInited:false
	};
	
	var t = this;
	
	function _______________TABS__________(){}
	
	
	/**
	 * return if tab selected or not
	 */
	function isTabSelected(objTab){
		if(objTab.hasClass("uc-tab-selected"))
			return(true);
		
		return(false);
	}
	
	
	/**
	 * select some tab
	 */
	function selectTab(objTab){
		
		if(objTab.hasClass("uc-browser-tab") == false)
			throw new Error("Wrong tab object");
			
		var objOtherTabs = getObjTabs(objTab);
		
		objOtherTabs.removeClass("uc-tab-selected");
		objTab.addClass("uc-tab-selected");
		
		//show content, hide others
		var catID = objTab.data("catid");
		
		showContentCategory(catID);
	}
	
	/**
	 * select first visible tab
	 */
	function selectFirstVisibleTab(){
		
		var objTabItems = g_objWrapper.find(".uc-browser-tabs-wrapper .uc-tab-item:visible");
		if(objTabItems.length == 0)
			return(false);
		
		var objTab = jQuery(objTabItems[0]).children("a");
		
		selectTab(objTab);
		
	}
	
	
	/**
	 * on tab click function
	 */
	function onTabClick(){
		var objTab = jQuery(this);
		if(isTabSelected(objTab))
			return(true);
		
		selectTab(objTab);
		
	}
	
	
	/**
	 * get obj all tabs without some tab
	 */
	function getObjTabs(objWithout){
		var objTabs = g_objWrapper.find(".uc-browser-tabs-wrapper .uc-browser-tab");
		
		if(objWithout)
			objTabs = objTabs.not(objWithout);
		
		return(objTabs);
	}
	
	/**
	 * init tabs
	 */
	function initTabs(){
		
		var objTabs = getObjTabs();
		
		objTabs.click(onTabClick);
	}
	
	
	
	function ________CATALOG_HEADER_MENU__________(){}
	
	/**
	 * on header menu item click
	 */
	function onHeaderMenuClick(){
		
		var objItem = jQuery(this);
		g_objHeaderMenu.find("a").not(objItem).removeClass("uc-menu-active");
		objItem.addClass("uc-menu-active");
		
		var state = objItem.data("state");
		
		trace(state);
	}
	
	
	/**
	 * init header menu
	 */
	function initHeaderMenu(){
		
		g_objHeaderMenu = g_objCatalog.find(".uc-catalog-header-menu");
		g_ucAdmin.validateDomElement(g_objHeaderMenu, "header menu");
		
		g_objHeaderMenu.find("a").click(onHeaderMenuClick);
		
	}
	
	
	function __________CATALOG_RELATED__________(){}
		
	/**
	 * install addon
	 */
	this.installAddon = function(objAddon, catTitle, onInstalledFunc){
		
		var addonName = objAddon.data("name");
			
		if(!catTitle){
			var objContent = objAddon.parents(".uc-browser-content");
			var catTitle = objContent.data("cattitle");
		}
		
		var objInstalled = objAddon.find(".uc-installed-success");
		if(objInstalled.length == 0)
			objInstalled = null;
		
		//set loader
		objAddon.find(".uc-hover-free").hide();
		objAddon.find(".uc-installing").show();
		
		var data = {};
		data["name"] = addonName;
		data["cat"] = catTitle;
		
		
		g_ucAdmin.setErrorMessageID(function(message){
			
			objAddon.find(".uc-installing div").hide();
			objAddon.find(".uc-installing i").hide();
			objAddon.find(".uc-installing span").hide();
			objAddon.find("h3").show().html(message);
		});
		
		var action = "install_catalog_addon";
		if(g_temp.isPages == true)
			action = "install_catalog_page";
		
		g_ucAdmin.ajaxRequest(action, data, function(response){
						
			objAddon.find(".uc-installing").hide();
			
			if(g_temp.isPages == false){
				objAddon.find(".uc-state-label").hide();
				objAddon.data("state","installed");
				
			}else{		//on pages call response function
				
				objAddon.find(".uc-hover-free").show();
				
				g_temp.funcResponse(response);
				
				if(objInstalled)
					objInstalled.show();
			}
			
			if(onInstalledFunc)
				onInstalledFunc(response);
			
		});
		
		
		return(false);
	};
	
	
	/**
	 * on addon click
	 */
	function onAddonClick(event){
		
		//view page click
		var target = event.target;
		var objTarget = jQuery(target);
		if(objTarget.hasClass("uc-hover-label-preview"))
			return(true);
		
		
		var objAddon = jQuery(this);
		var state = objAddon.data("state");
		
		switch(state){
			case "free":
				t.installAddon(objAddon);
				return(false);
			break;
			case "pro":
				
				return(true);
			break;
		}
		
		var addonName = objAddon.data("name");
		var addonTitle = objAddon.data("title");
		var addonID = objAddon.data("id");
		
		//load put new addon data, close the catalog first
		
		var objData = {
				"name":addonName,
				"title":addonTitle,
				"id":addonID,
				"addontype":g_temp.addonType
		};
		
		g_temp.funcResponse(objData);
		
		closeCatalog();
	}
	    
    
    /**
     * on addon hover
     */
    this.onAddonHover = function(event, objAddon) {
    	
    	if(!objAddon)
    		var objAddon = jQuery(this);
    	    		
    	var objLabel = objAddon.find(".uc-hover-label")
    	if(objLabel.length == 0)
    		return(true);
    	    	
        if(objLabel.attr('installing') === 'true' || objLabel.attr('installed') === 'true') {
            return false;
        }
    	
        if(event.type === "mouseenter" || event.type == "item_mouseover") {
        	objLabel.removeClass('hidden');
        } else {
        	objLabel.addClass('hidden');
        }
    }
	
	
	/**
	 * close the catalog
	 */
	function closeCatalog(){
		
		jQuery("body").removeClass("uc-catalog-open");
		
		g_objWrapper.hide();
		g_objCatalog.hide();
	}
	
	
	/**
	 * open catalog
	 */
	function openCatalog(){
				
		g_objWrapper.show();
				
		g_objCatalog.show();
		
		jQuery("body").addClass("uc-catalog-open");		
		
		jQuery("#uc_catalog_search_input").focus();
		
	}
	
	
	/**
	 * init catalog events
	 */
	function initCatalogEvents(){
		
		//close button
		g_objCatalog.find(".uc-catalog-button-close").click(closeCatalog);
		
		jQuery("#uc_button_catalog_update").click(openDialogCatalogUpdate);
				
	}
	
	/**
	 * get category addons
	 */
	function getCatAddons(catID){
		var selector = "#uc_browser_content_"+catID+" .uc-browser-addon";
		
		var objAddons = jQuery(selector);
		
		return(objAddons);
	}
	
	
	
	
	/**
	 * init the catalog
	 */
	function initCatalog(){
        
		g_objCatalog = g_objWrapper.find(".uc-catalog");
		
		g_ucAdmin.validateDomElement(g_objCatalog, "addon browser catalog");
				
		g_objTabsWrapper = g_objWrapper.find(".uc-browser-tabs-wrapper");
		
		initTabs();
		
		initHeaderMenu();
		
		initCatalogSearch();
		
		initCatalogEvents();
	}
	
	function _______________SEARCH__________(){}
	
	
	/**
	 * set categories titles according number of items
	 * only on visible items
	 */
	function setCategoriesTitles(){
		
		var objTabItems = g_objWrapper.find(".uc-browser-tabs-wrapper .uc-tab-item:visible");
		
		objTabItems.each(function(index, tabItem){
			var objItem = jQuery(tabItem);
			var title = objItem.data("title");
			var catID = objItem.data("catid");
			var objAddons = getCatAddons(catID);
			var numAddons = objAddons.not(".uc-item-hidden").length;
			var showTitle = title+" ("+numAddons+")";
			objItem.children("a").html(showTitle);
		});
		
	}

	
	/**
	 * show all addons and cats that been hidden by search
	 */
	function search_showAll(){
		
		g_objWrapper.find(".uc-item-hidden").removeClass("uc-item-hidden").show();
		
		setCategoriesTitles();
	}
	
	
	/**
	 * do search
	 */
	function doCatalogSearch(searchValue){
		
		searchValue = jQuery.trim(searchValue);
		
		if(!searchValue){
			search_showAll();
			return(true);
		}
		
		searchValue = searchValue.toLowerCase();
		
		var objTabItems = g_objWrapper.find(".uc-browser-tabs-wrapper .uc-tab-item");
		
		objTabItems.each(function(index, item){
			var objItem = jQuery(this);
			var title = objItem.data("title");
			title = title.toLowerCase();
			
			var pos = title.indexOf(searchValue);
			var isCatFound = (pos !== -1);
			
			var catID = objItem.data('catid');
			var objAddons = getCatAddons(catID);
			
			var isSomeAddonFound = false;
			
			//if category found, all addons will be visible
			if(isCatFound == true){
				
				objAddons.removeClass("uc-item-hidden").show();
				
			}else{	//if cat not found, check addons
				
				jQuery.each(objAddons, function(index, addon){
					
					var objAddon = jQuery(addon);
					
					var addonTitle = objAddon.data("title");
					addonTitle = addonTitle.toLowerCase();
					
					var posAddon = addonTitle.indexOf(searchValue);
					var isAddonFound = (posAddon !== -1);
					if(isAddonFound == true){
						isSomeAddonFound = true;
						objAddon.removeClass("uc-item-hidden").show();
						
					}else{
						objAddon.addClass("uc-item-hidden").hide();
					}
					
				});	//end foreach addons
				
			}
			
			
			if(isCatFound == true || isSomeAddonFound == true){
				objItem.removeClass("uc-item-hidden").show();
			}else
				objItem.addClass("uc-item-hidden").hide();
			
		});
		
		//select first cat
		setCategoriesTitles();
		selectFirstVisibleTab();
		
	}
	
	
	/**
	 * init search in catalog
	 */
	function initCatalogSearch(){
		
		g_objSearchInput = jQuery("#uc_catalog_search_input");
		
		//-- search input
		
		g_ucAdmin.onChangeInputValue(g_objSearchInput, function(){
			
			var objClearButton = jQuery("#uc_catalog_search_clear");
			
			var value = g_objSearchInput.val();
			value = jQuery.trim(value);
			
			if(value)
				objClearButton.fadeTo(500, 1).removeClass("button-disabled");
			else
				objClearButton.fadeTo(500,0).addClass("button-disabled");
			
			doCatalogSearch(value);
		});
		
		//--clear button
		
		jQuery("#uc_catalog_search_clear").click(function(){
			
			var objButton = jQuery(this);
			if(objButton.hasClass("button-disabled"))
				return(false);
			
			//hide button
			objButton.fadeTo(500,0).addClass("button-disabled");
			
			g_objSearchInput.val("");
			search_showAll();
		});
		
	}
	
	
	function _______________GENERAL__________(){}

		
	/**
	 * show content category
	 */
	function showContentCategory(catID){
		
		var objContent = jQuery("#uc_browser_content_"+catID);
		g_objWrapper.find(".uc-browser-content").not(objContent).hide();
		objContent.show();
	}
	
	
	/**
	 * cache addon
	 */
	function cacheAddonData(name, addonData){
		g_objCache[name] = addonData;
	}
	
	
	/**
	 * get cached addon settings
	 */
	function getCachedAddonData(name){
		
		if(g_objCache.hasOwnProperty(name) == false)
			return(null);
		
		return(g_objCache[name]);
	}
	
	
	/**
	 * open addons browser, for column - add new, for addon - update
	 */
	this.openAddonsBrowser = function(currentAddonData, funcResponse){
		
		if(!funcResponse)
			throw new Error("There should be response func");
		
		g_temp.funcResponse = funcResponse;
		
		openCatalog();
				
	};
	
	
	/**
	 * init update catalog
	 */
	function openDialogCatalogUpdate(){
		
		var options = {
				dialogClass:"uc-dialog-catalog-update unite-ui-black",
				height:300
		};
		
		g_ucAdmin.openCommonDialog("uc_dialog_catalog_update", function(){
			
			g_ucAdmin.setAjaxLoaderID("uc_dialog_catalog_update_loader");
			jQuery("#uc_dialog_catalog_update_message").html("").hide();
			
			g_ucAdmin.setErrorMessageID("uc_dialog_catalog_update_error");
			
			g_ucAdmin.ajaxRequest("check_catalog", {force:true}, function(response){
				
				var errorMessage = g_ucAdmin.getVal(response,"error_message");
				if(errorMessage)
					jQuery("#uc_dialog_catalog_update_error").show().html(errorMessage);
					
				jQuery("#uc_dialog_catalog_update_message").html(response.message).show();
				
			});
			
			
		}, options);
		
				
	}
	
	
	
	function _______________EVENTS__________(){}
	
	     
		
	
	/**
	 * actuvate pro init
	 */
    function activateProDialog() {
    	
    	g_dialogActivation.dialog({
			dialogClass:"uc-activation-dialog",
			width:700,
			height:500,
			modal:true,
			create:function () {
				g_dialogActivation.find('.popup-close').click(function() {jQuery('.activateProDialog').dialog('close');});    
		    },
            open: function () {
            	g_dialogActivation.find('.start').removeClass('hidden'); 
            },
            close: function () {
            	g_dialogActivation.find('.start').addClass('hidden'); 
            	g_dialogActivation.find('.fail').addClass('hidden'); 
            	g_dialogActivation.find('.activated').addClass('hidden'); 
            }
		});
        
        
    }
	
    
    /**
     * on activate pro button click
     */
    function onActivateButtonClick(){
    	
    	var code = jQuery("#uc_activate_pro_code").val();
    	code = jQuery.trim(code);
    	
    	g_ucAdmin.setAjaxLoaderID("uc_loader_activate_pro");
    	g_ucAdmin.setAjaxHideButtonID("uc_button_activate_pro");
    	g_ucAdmin.setErrorMessageID(function(message){
    			
    		g_dialogActivation.find('.start').addClass('hidden');
    		g_dialogActivation.find('.fail').removeClass('hidden');
    		g_dialogActivation.find('.popup-error').show().html(message);
    		
    	});
    	
    	var data = {};
    	data.code = code;
    	
    	g_ucAdmin.ajaxRequest("activate_product", data, function(response){
    		
    		g_dialogActivation.find('.start').addClass('hidden');
    		g_dialogActivation.find('.fail').addClass('hidden');
    		g_dialogActivation.find('.activated').removeClass('hidden');
    		
    		var activateDays = response["expire_days"];
    		
    		g_dialogActivation.find(".activated .days").html(activateDays);
    		
    	});
    	
    }
	
    /**
     * on try again click
     */
    function onActivateTryAgainClick(){
    	
    	g_dialogActivation.find('.start').removeClass('hidden'); 
    	g_dialogActivation.find('.fail').addClass('hidden'); 
    	g_dialogActivation.find('.activated').addClass('hidden'); 
    	
    	jQuery("#uc_activate_pro_code").focus();
    	
    }
    
    
    /**
     * on deactivate click
     */
    function onDeactivateProductClick(){
    	
    	g_ucAdmin.setErrorMessageID(function(message){
			
    		alert("Error: "+message);
    		
    	});
    	
    	g_ucAdmin.ajaxRequest("deactivate_product", {}, function(response){
    		
    		confirm(response.message);
    		
    	});
    	
    }
    
    
    /**
     * init activation dialog
     */
    function initActivationDialog(){
    	
    	g_dialogActivation = jQuery('.activateProDialog');
		
    	g_objWrapper.find("#link_activate_pro").click(activateProDialog);
    	g_objWrapper.find("#uc_link_deactivate").click(onDeactivateProductClick);
    	
		jQuery("#uc_button_activate_pro").click(onActivateButtonClick);
    	jQuery("#activation_link_try_again").click(onActivateTryAgainClick);
    	
    }
	
	function _______________INIT__________(){}
	
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		g_objWrapper.find(".uc-browser-addon").click(onAddonClick);
		
		//g_objWrapper.find(".buttons-addon").click(onAddonButtonClick);
        g_objWrapper.find(".uc-browser-addon").hover(t.onAddonHover);
		
		if(g_objBackButton)
			g_objBackButton.click(onBackButtonClick);
				
	}	
	
	/**
	 * close catalog
	 */
	this.closeCatalog = function(){
		closeCatalog();
	};
	
	/**
	 * init browser object
	 */
	this.init = function(objWrapper){
		
		g_objWrapper = objWrapper;
		
		g_temp.addonType = g_objWrapper.data("addontype");
		
		var isPages = g_objWrapper.data("ispages");
		if(isPages)
			g_temp.isPages = true;
		
		initCatalog();
		
		initEvents();
		
		initActivationDialog();
	};
	
	
}



