<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorBrowser extends HtmlOutputBaseUC{
	
	private $selectedCatNum = "";
	private $isPages = false;
	
	private $addonType = "";
	private $webAPI;
	
	private static $isPutOnce = false;
	
	const STATE_INSTALLED = "installed";
	const STATE_FREE = "free";
	const STATE_PRO = "pro";
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->webAPI = new UniteCreatorWebAPI();
	}
	
	private function a____________GETTERS____________(){}
	
	/**
	 * get html tabs header
	 */
	private function getHtmlCatalogHeader(){
		
		$html = "";
		
		$textBlox = __("Blox", ADDONLIBRARY_TEXTDOMAIN);
		$textShowAll = __("Show All", ADDONLIBRARY_TEXTDOMAIN);
		$textInstalled = __("Installed", ADDONLIBRARY_TEXTDOMAIN);
		$textFree = __("Free", ADDONLIBRARY_TEXTDOMAIN);
		$textPro = __("Pro", ADDONLIBRARY_TEXTDOMAIN);
		$textBuy = __("Buy Blox PRO", ADDONLIBRARY_TEXTDOMAIN);
		$textAlreadyBought = __("Already bought Blox PRO?", ADDONLIBRARY_TEXTDOMAIN);
		$textTheProductActive = __("The product is Active!", ADDONLIBRARY_TEXTDOMAIN);
		$textDeactivate = __("Deactivate", ADDONLIBRARY_TEXTDOMAIN);
		$textCheckUpdate = __("Check Catalog Update", ADDONLIBRARY_TEXTDOMAIN);
		$textClear = __("Clear", ADDONLIBRARY_TEXTDOMAIN);
		
		
		$urlBuy = GlobalsUC::URL_BUY;
		
		$htmlAccount = "";
		if(GlobalsUC::$isProductActive == false){
			$htmlAccount = "
			 <div class='uc-header-gotopro'>
			      <a id='link_activate_pro' href='javascript:void(0)' class='uc-link-activate-pro'>{$textAlreadyBought}</a>
			      <a id='uc_link_top_buy_pro' href='{$urlBuy}' target='_blank' class='uc-button-buy-pro'>{$textBuy}</a>
			 </div>
		";
		}
		else{		//product is active
			$htmlAccount = "
			<div class='uc-header-gotopro'>
				<span class='uc-catalog-active-text'>{$textTheProductActive}</span>
				<a id='uc_link_deactivate' href='javascript:void(0)' class='uc-link-deactivate'>{$textDeactivate}</a>
			</div>
			";
		}
										
		
		$html .= "<div class='uc-catalog-header unite-inputs unite-clearfix'>

		 		<div class='uc-catalog-logo'></div>
	    		<div class='uc-catalog-search'>
					<i id='uc_catalog_search_icon' class='fa fa-search' aria-hidden='true'></i> &nbsp;
	    			<input id='uc_catalog_search_input' type='text'>
	    			<a id='uc_catalog_search_clear' href='javascript:void(0)' class='unite-button-secondary button-disabled' style='display:none;'>{$textClear}</a>
	    		</div>
	    		
	    		<div class='uc-catalog-header-menu'>
	     			<a href='javascript:void(0)' class='uc-menu-active' onfocus='this.blur()' data-state='all'>{$textShowAll}</a>
	      	  		<a href='javascript:void(0)' onfocus='this.blur()' data-state='installed'>{$textInstalled}</a>
	      	  		<a href='javascript:void(0)' onfocus='this.blur()' data-state='free'>{$textFree}</a>
	       	 		<a href='javascript:void(0)' onfocus='this.blur()' data-state='pro'>{$textPro}</a>
				</div>
								
		   	 	<a href='javascript:void(0)' onfocus='this.blur()' class='uc-catalog-button-close'>
		   	 		<i class='fa fa-times' aria-hidden='true'></i>
			 	</a>
				
				<a id='uc_button_catalog_update' class='uc-link-update-catalog' title='{$textCheckUpdate}' href='javascript:void(0)' onfocus='this.blur()'><i class='fa fa-download' aria-hidden='true'></i></a>
			 	
			 	{$htmlAccount}
			 	
		</div>";
		
		return($html);
	}
	
	
	
	/**
	 * get tabs html
	 */
	private function getHtmlTabs($arrCats){
		
		$html = "";
				
		$numCats = count($arrCats);
		
		$addHtml = "";
				
		$isFirst = true;
		
		$counter = 0;
		$totalItems = 0;
		$htmlTabs = "";
		foreach($arrCats as $catTitle=>$cat){
			
			if($this->isPages == false)
				$arrItems = UniteFunctionsUC::getVal($cat, "addons");
			else
				$arrItems = $cat;
							
			$numItems = 0;
			if(!empty($arrItems)){
				$numItems = count($arrItems);
				$totalItems += $numItems;
			}
						
			$counter++;
			
			if(empty($this->selectedCatNum) && $isFirst == true){
				$isFirst = false;
				$this->selectedCatNum = $counter;
			}
			
			$isSelected = false;
			if($this->selectedCatNum === $counter)
				$isSelected = true;
			
			if(empty($catTitle))
				$catTitle = UniteFunctionsUC::getVal($cat, "title");
			
			if(empty($catTitle)){
				$id = UniteFunctionsUC::getVal($cat, "id");
				if(empty($id))
					$id = $counter;
				
				$catTitle = __("Category", ADDONLIBRARY_TEXTDOMAIN)." {$id}";
			}
			
			$catShowTitle = $catTitle;
			
			if(!empty($numItems))
				$catShowTitle .= " ($numItems)";
			
			$catTitle = htmlspecialchars($catTitle);
			$catShowTitle = htmlspecialchars($catShowTitle);
			
			$addClass = "";
			if($isSelected == true)
				$addClass = " uc-tab-selected";
			
			$htmlTabs .= self::TAB5."<div class='uc-tab-item' data-catid='{$counter}' data-title='{$catTitle}'><a href=\"javascript:void(0)\" onfocus=\"this.blur()\" class=\"uc-browser-tab{$addClass}\" data-catid=\"{$counter}\">{$catShowTitle}</a></div>".self::BR;
		}

		$htmlTitleCategories = __("Categories", ADDONLIBRARY_TEXTDOMAIN);
		if(!empty($totalItems))
			$htmlTitleCategories .= " ($totalItems)";
		
		
		$html .= self::TAB3."<div class=\"uc-browser-tabs-wrapper\" {$addHtml}>".self::BR;
		
		$html .= self::TAB3."	<div class='uc-browser-tabs-heading'>{$htmlTitleCategories}</div>".self::BR;
				
		$html .= $htmlTabs;
		
		$html .= self::TAB3."<div class='unite-clear'></div>".self::BR;
		
		
		$html .= self::TAB3."</div>".self::BR;	//end tabs
				
		return($html);
	}

	
	
	
	/**
	 * get content html
	 */
	private function getHtmlContent($arrCats){
		
		$html = "";
				
		$numCats = count($arrCats);
		
		$addHtml = "";
		
		$html .= self::TAB2."<div class=\"uc-browser-content-wrapper\" {$addHtml}>".self::BR;
				
		//output addons
		$counter = 0;
		foreach($arrCats as $catTitle => $cat){
			
			$counter++;
			
			$title = UniteFunctionsUC::getVal($cat, "title");
			
			if($this->isPages == true)
				$title = $catTitle;
						
			$title = htmlspecialchars($title);
			
			$style = " style=\"display:none\"";
			if($counter === $this->selectedCatNum || $numCats <= 1)
				$style = "";
			
			if($this->isPages == true)
				$arrItems = $cat;
			else
				$arrItems = UniteFunctionsUC::getVal($cat, "addons");
			
			$html .= self::TAB3."<div id=\"uc_browser_content_{$counter}\" class=\"uc-browser-content\" data-cattitle='{$title}' {$style} >".self::BR;
			
			if(empty($arrItems)){
				
				if($this->isPages == false)
					$html .= __("No addons in this category", ADDONLIBRARY_TEXTDOMAIN);
				else 
					$html .= __("No pages in this category", ADDONLIBRARY_TEXTDOMAIN);
				
			}
			else{
				
				if(is_array($arrItems) == false)
					UniteFunctionsUC::throwError("The cat addons array should be array");
				
				foreach($arrItems as $name=>$item){
										
					if($this->isPages == true)
						$item["name"] = $name;
					
					$htmlItem = $this->getHtmlItem($item);
				
					$html .= $htmlItem;
				}
				
			}
		
			$html .= self::TAB3."</div>".self::BR;
		}
		
		$html .= self::TAB2."<div class='unite-clear'></div>".self::BR;
		
		$html .= self::TAB2."</div>".self::BR; //content wrapper
        $html .= "</div>";
		
		return($html);
	}
	
	/**
	 * check if the web addon is free
	 */
	public static function isWebAddonFree($addon){
				
		if(GlobalsUC::$isProductActive == true)
			return(true);
		
		$isFree = UniteFunctionsUC::getVal($addon, "isfree");
		$isFree = UniteFunctionsUC::strToBool($isFree);
		
		return($isFree);
	}
	
	
	/**
	 * get catalog addon state html
	 */
	public static function getCatalogAddonStateData($state, $isPage = false, $urlPreview = null){
        
		$addonHref = "javascript:void(0)";
        $linkAddHtml = "";
		
		$output = array();
		$output["html_state"] = "";
		$output["html_additions"] = "";
		$output["addon_href"] = "javascript:void(0)";
		$output["link_addhtml"] = "";
		$output["state"] = $state;
		
		$textItem = "addon";
		$textItemHigh = "Addon";
		
		if($isPage){
			$textItem = "page";
			$textItemHigh = "Page";
		}
		
        //installed
        switch($state){
        	case self::STATE_FREE:
        		$label = 'free';
        		$labelText = 'Free';
        		$hoverText = "This $textItemHigh Is Free<br>To use it click install";
        		$hoverIcon = '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
        		$action = "Install";
        		
        		if(GlobalsUC::$isProductActive){
        			$labelText = 'Web';
        			$hoverText = "You can install this $textItem <br>To use it click install";        			
        		}
        		        		
        	break;
        	case self::STATE_PRO:
        		$label = 'pro';
        		$labelText = 'Pro';
        		$hoverText = "This $textItem is available<br>for Blox PRO users only.";
        		$hoverIcon = '<i class="fa fa-lock" aria-hidden="true"></i>';
        		$action = __("Buy Blox PRO", ADDONLIBRARY_TEXTDOMAIN);
        		$addonHref = GlobalsUC::URL_BUY;
        		$linkAddHtml = " target='_blank'";
        	break;
        	default:
        		return($output);
        	break;
        }
		
        //add pages data
        if($isPage == true){
        	
        	if(!empty($urlPreview)){
        		$urlPreview = htmlspecialchars($urlPreview);
        		$hoverText .= " <br><a class='uc-hover-label-preview' href='{$urlPreview}' target='_blank' >View Page Demo</a>";
        	}
        }
        
		$htmlState = "<div class='uc-state-label uc-state-{$label}'>
			<div class='uc-state-label-text'>{$labelText}</div>
		</div>";			
        
		//make html additions
		
		$htmlAdditions = "";
        
		$urlBuy = GlobalsUC::URL_BUY;
        
		$htmlAdditions .= "<div class='uc-hover-label uc-hover-{$label} hidden'>
					{$hoverIcon}
					<div class='uc-hover-label-text'>{$hoverText}</div>
					<a href='{$urlBuy}' target='_blank' class=\"uc-addon-button uc-button-{$label}\">{$action}</a>
				</div>";
		
		$textInstalling = __("Installing", ADDONLIBRARY_TEXTDOMAIN);
		
		$htmlAdditions .= "<div class='uc-installing' style='display:none'>
					   <div class='uc-bar'></div>
					   <i class='fa fa-spinner fa-spin fa-3x fa-fw'></i>
					   <span>{$textInstalling}...</span>
					   <h3 style='display:none'></h3>
				  </div>";
		
		
		//add success message
		if($isPage){
			
			$textInstalled = "Page installed successfully<br> refreshing...";
			
			$htmlAdditions .= "<div class='uc-installed-success' style='display:none'>
						   <span>{$textInstalled}...</span>
					  </div>";
		}
		
		
		$output["html_state"] = $htmlState;
		$output["html_additions"] = $htmlAdditions;
		$output["addon_href"] = $addonHref;
		$output["link_addhtml"] = $linkAddHtml;
		
		return($output);
	}
	
	
	/**
	 * get addon html
	 * @param $addon
	 */
	private function getHtmlItem($arrItem){
		
		$html = "";
		
		if($this->isPages == true)
			$isFromWeb = true;
		else{
			$isFromWeb = UniteFunctionsUC::getVal($arrItem, "isweb");
			$isFromWeb = UniteFunctionsUC::strToBool($isFromWeb);
		}
		
		if($isFromWeb == true)
			$isFree = self::isWebAddonFree($arrItem);
		
		/*
		if($isFromWeb == false){
			dmp($arrItem);
			exit();
		}
		*/
		
		$name = UniteFunctionsUC::getVal($arrItem,"name");
		$name = UniteFunctionsUC::sanitizeAttr($name);
		
		$title = UniteFunctionsUC::getVal($arrItem, "title");
		$title = UniteFunctionsUC::sanitizeAttr($title);
		
		$paramImage = "preview";
		if($isFromWeb == true){
			$paramImage = "image";
		}
				
		$urlPreviewImage = UniteFunctionsUC::getVal($arrItem, $paramImage);
		$urlPreviewImage = UniteFunctionsUC::sanitizeAttr($urlPreviewImage);
		
		$id = UniteFunctionsUC::getVal($arrItem, "id");
		
		//get state
		$state = self::STATE_INSTALLED;
		
		if($isFromWeb){
					
			if($isFree == true)
				$state = self::STATE_FREE;
			else
				$state = self::STATE_PRO;
		}
        
		$urlItemPreview = null;
		
		if($this->isPages == true){
			
			$urlItemPreview = UniteFunctionsUC::getVal($arrItem, "url");
			
		}
		
		$stateData = self::getCatalogAddonStateData($state, $this->isPages, $urlItemPreview);
		
		$addonHref = $stateData["addon_href"];
		$linkAddHtml = $stateData["link_addhtml"];
		
		$classAdd = "";
		if($isFromWeb == true)
			$classAdd = "uc-web-addon";
        
		$html .= self::TAB4."<div class=\"uc-browser-addon uc-addon-thumbnail {$classAdd}\" href=\"{$addonHref}\" {$linkAddHtml} data-state=\"{$state}\" data-id=\"$id\" data-name=\"{$name}\" data-title=\"{$title}\">".self::BR;
		
		if($state != self::STATE_INSTALLED){
			$html .= $stateData["html_state"];
		}
				
		$html .= self::TAB6."<div class=\"uc-browser-addon-image\" style=\"background-image:url('{$urlPreviewImage}')\"></div>".self::BR;
		$html .= self::TAB6."<div class=\"uc-browser-addon-title\">{$title}</div>".self::BR;
		
		
		if($state != self::STATE_INSTALLED){
			$html .= $stateData["html_additions"];
		}
		
		$html .= self::TAB4."</div>".self::BR;
	
	
		return($html);
	}
	
	
	/**
	 * get addon config html
	 */
	private function getHtmlItemConfig($putMode = false){
		
		$html = "";
		
		$html .= self::BR.self::TAB2."<!-- start addon config -->".self::BR;
		
		//output back button
		$html .= self::TAB2."<div class='uc-browser-dialog-config'>".self::BR;
		$html .= self::TAB2."<div class='uc-dialog-config-inner'>";
		
		$html .= self::TAB4."<span id='uc_browser_loader' class='uc-browser-loader loader_text' style='display:none'>".__("Loading Addon...",ADDONLIBRARY_TEXTDOMAIN)."</span>".self::BR;
		$html .= self::TAB4."<div id='uc_browser_error' class='uc-browser-error unite_error_message' style='display:none'></div>".self::BR;
		$html .= self::TAB3."<div class='uc-browser-addon-config-wrapper'></div>".self::BR;
		
		$html .= self::TAB2."</div>".self::BR;	// inner end
		$html .= self::TAB2."</div>".self::BR;	// dialog end
		
		$html .= self::TAB2."<!-- end addon config -->".self::BR;
		
		if($putMode == true)
			echo $html;
		else
			return($html);
	}
	
	private function a____________OPERATIONS____________(){}
	
	
	/**
	 * sort catalog items
	 */
	public function sortCatalogItems($key1, $key2){
		
		if(strtolower($key1) == "basic")
			return(-1);
		
		if(strtolower($key2) == "basic")
			return(1);
		
		return strcmp($key1, $key2);
	}

	
	/**
	 * sort the categories
	 */
	private function sortCatalog($arrCats){
		
		uksort($arrCats, array($this,"sortCatalogItems"));
				
		return($arrCats);
	}
	
	/**
	 * remove empty cats
	 */
	private function removeEmptyCatalogCats($arrCats){
		
		foreach($arrCats as $key=>$cat){
			
			$addons = UniteFunctionsUC::getVal($cat, "addons");
			if(empty($addons))
				unset($arrCats[$key]);			
		}
		
		return($arrCats);
	}
	
	/**
	 * set browser addon type
	 */
	public function initAddonType($addonType){
		$this->addonType = $addonType;
	}
	
	/**
	 * set pages catalog mode
	 */
	public function setPagesMode(){
		
		$this->isPages = true;
	}
	
	
	private function a____________OUTPUT____________(){}
	
	/**
	 * get addons items
	 */
	private function getArrCats_addons(){
			
		//get categories
		$objAddons = new UniteCreatorAddons();
		$arrCats = $objAddons->getAddonsWidthCategories(true, true, $this->addonType);
		
		if(GlobalsUC::$enableWebCatalog == true)
			$arrCats = $this->webAPI->mergeCatsAndAddonsWithCatalog($arrCats);
		
		$arrCats = $this->removeEmptyCatalogCats($arrCats);
		
		$arrCats = $this->sortCatalog($arrCats);
				
		return($arrCats);
	}
	
	/**
	 * get addons items
	 */
	private function getArrCats_pages(){

		$arrPages = $this->webAPI->getCatalogArray_pages();
		
		if(empty($arrPages))
			$arrPages = array();
			
		return($arrPages);
	}
	
	
	/**
	* get catalog html
	 */
	private function getHtmlCatalog($putMode = false){
				
		if($this->isPages == false)
			$arrCats = $this->getArrCats_addons();
		else
			$arrCats = $this->getArrCats_pages();
				
		$numCats = count($arrCats);
		
		$addClass = "";
		if($this->isPages == true)
			$addClass = " uc-catalog-pages";
		
		$html = "";
		
		$html .= self::BR.self::TAB2."<!-- start addon catalog -->".self::BR;
		
		$html .= self::TAB2."<div class='uc-catalog{$addClass}'>".self::BR;
		
		$html .= $this->getHtmlCatalogHeader();
		
		
		$html .= self::TAB2."<div class='uc-browser-body unite-clearfix'>".self::BR;
		
		//output tabs
		$html .= $this->getHtmlTabs($arrCats);
		
		//output content
		$html .= $this->getHtmlContent($arrCats);
		
		$html .= self::TAB2."</div>".self::BR;	//end body
		
		$html .= self::TAB2."</div>".self::BR;	//end catalog
		
		$html .= self::BR.self::TAB2."<!-- end addon catalog -->".self::BR;
		
		return($html);
	}
	
	
	/**
	 * get browser html
	 */
	private function getHtml($putMode = false){
		
		if(self::$isPutOnce == true)
			UniteFunctionsUC::throwError("You can put the addon browser only once per page");

		$htmlCatalog = $this->getHtmlCatalog();
		
		
		$html = "";
		$html .= self::TAB."<!-- start addon browser --> ".self::BR;
		
		$addHtml = "";
		if(!empty($this->inputIDForUpdate))
			$addHtml .= " data-inputupdate=\"".$this->inputIDForUpdate."\"";
		
		
		$addonType = $this->addonType;
		$addHtml .= " data-addontype='{$addonType}'";

		if($this->isPages)
			$addHtml .= " data-ispages='true'";
		
		$html .= self::TAB."<div id=\"uc_addon_browser\" class=\"uc-browser-wrapper\" {$addHtml} style='display:none'>".self::BR;
		
		if($putMode == true){
			echo $html;
			$html = "";
		}
		
		$html .= $this->getHtmlItemConfig($putMode);
		$html .= $htmlCatalog;
				
		$html .= self::TAB."</div>"; //wrapper
		
		if($putMode == true)
			echo $html;
		else
			return($html);
	}
	
	
	/**
	 * put html
	 */
	private function putHtml(){
		
		$this->getHtml(true);
	}
	
	
	/**
	 * put scripts
	 */
	public function putScripts(){
		
		UniteCreatorAdmin::onAddScriptsBrowser();
	}
	
		
	
	/**
	 * put browser
	 */
	public function putBrowser($putMode = true){
				
		if($putMode == false){
			$html = $this->getHtml();
			return($html);
		}
		
		
		$this->putHtml();
		$this->putActivateProDialog();
		$this->putCatalogUpdateDialog();
		
	}
	
	
	/**
	 * put scripts and browser
	 */
	public function putScriptsAndBrowser($getHTML = false){
		
		try{
			
			$this->putScripts();
			$html = $this->putBrowser($getHTML);
			
			if($getHTML == true)
				return($html);
			else
				echo $html;
			
		}catch(Exception $e){
			
			$message = $e->getMessage();
			
			$trace = "";
			if(GlobalsUC::SHOW_TRACE == true)
				$trace = $e->getTraceAsString();
			
			$htmlError = HelperUC::getHtmlErrorMessage($message, $trace);
			
			return($htmlError);
		}
		
	}
	
	
	/**
	 * put activate dialog
	 */
	private function putActivateProDialog() {
		
		$urlPricing = GlobalsUC::URL_BUY;
		$urlSupport = GlobalsUC::URL_SUPPORT;
		
		?>
           <div class="activateProDialog" title="Activate Your Pro Account" style="display:none">
           
           <div class="uc-popup-container start hidden">
                <div class="uc-popup-content">
                    <div class="uc-popup-holder">
                        <div class="xlarge-title">GO PRO</div>
                        <div class="popup-text">Unleash access to +600 addons <br>and +20 templates</div>
                        <div class="popup-form">
                                <label>Paste your activation key here:</label>
                                <input id="uc_activate_pro_code" type="text" placeholder="xxxx-xxxx-xxxx-xxxx" value="">
                                <input id="uc_button_activate_pro" type="button" class='uc-button-activate' value="Activate Blox Pro">
                                <span id="uc_loader_activate_pro" class='loader_text' style='display:none'>Activating...</span>
                        </div>
                        <div class="bottom-text">Don't have a pro activation key?<br>
                        <a href="<?php echo $urlPricing?>" target="_blank" class="blue-text">View our pricing plans</a></div>
                    </div>
                </div>
            </div>
            
            <div class="uc-popup-container fail hidden">
                <div class="uc-popup-content">
                    <div class="uc-popup-holder">
                        <div class="large-title">Ooops.... <br>Activation Failed :(</div>
                        <div class="popup-error"></div>
                        <div class="popup-text">You probably got you activation code wrong <br>to try again <a id="activation_link_try_again" href="javascript:void(0)">click here</a></div>
                        <div class="bottom-text">or contact our <a href="<?php echo $urlSupport?>" target="_blank">support center</a></div>
                    </div>
                </div>
            </div>
            
            <div class="uc-popup-container activated hidden">
                <div class="uc-popup-content">
                    <div class="uc-popup-holder">
                        <div class="xlarge-title">Hi Five!</div>
                        <div class="popup-text small-padding">Your pro account is activated for the next</div>
                        <div class="days"></div>
                        <span>DAYS</span>
                        <br>
                        <a href="javascript:location.reload()" class="btn">Refresh page to View Your Pro Catalog</a>
                    </div>
                </div>
            </div>
            
            </div>
		
		<?php 
	}
	
	
	/**
	 * put check udpate dialog
	 */
	private function putCatalogUpdateDialog(){
				
		?>
		
			<div id="uc_dialog_catalog_update" title="<?php _e("Check And Update Catalog")?>" class="unite-inputs" style="display:none">
				<div class="unite-dialog-inside">
					
					<span id="uc_dialog_catalog_update_loader" class="loader_text">
						<?php _e("Checking Update", ADDONLIBRARY_TEXTDOMAIN)?>...
					</span>
					
					<div id="uc_dialog_catalog_update_error" class="error-message"></div>
					
					<div id="uc_dialog_catalog_update_message" class="uc-catalog-update-message"></div>
					
				</div>
				
			</div>		
		<?php 
	}
	
}