<?php


defined('ADDON_LIBRARY_INC') or die('Restricted access');


	class UniteFunctionsWPUC{

		public static $urlSite;
		public static $urlAdmin;
		private static $db;
		
		private static $arrTaxCache;
		
		const SORTBY_NONE = "none";
		const SORTBY_ID = "ID";
		const SORTBY_AUTHOR = "author";
		const SORTBY_TITLE = "title";
		const SORTBY_SLUC = "name";
		const SORTBY_DATE = "date";
		const SORTBY_LAST_MODIFIED = "modified";
		const SORTBY_RAND = "rand";
		const SORTBY_COMMENT_COUNT = "comment_count";
		const SORTBY_MENU_ORDER = "menu_order";
		
		const ORDER_DIRECTION_ASC = "ASC";
		const ORDER_DIRECTION_DESC = "DESC";
		
		const THUMB_SMALL = "thumbnail";
		const THUMB_MEDIUM = "medium";
		const THUMB_LARGE = "large";
		const THUMB_FULL = "full";
		
		const STATE_PUBLISHED = "publish";
		const STATE_DRAFT = "draft";
		
		/**
		 * 
		 * init the static variables
		 */
		public static function initStaticVars(){
			//UniteFunctionsUC::printDefinedConstants();
			
			self::$urlSite = site_url();
			
			if(substr(self::$urlSite, -1) != "/")
				self::$urlSite .= "/";
			
			self::$urlAdmin = admin_url();			
			if(substr(self::$urlAdmin, -1) != "/")
				self::$urlAdmin .= "/";
				
		}
		
		
		/**
		 * get DB
		 */
		public static function getDB(){
			
			if(empty(self::$db))
				self::$db = new UniteCreatorDB();
				
			return(self::$db);
		}
		
		
		public static function a_____________POSTS_TYPES___________(){}
		
		/**
		 * 
		 * return post type title from the post type
		 */
		public static function getPostTypeTitle($postType){
			
			$objType = get_post_type_object($postType);
						
			if(empty($objType))
				return($postType);

			$title = $objType->labels->singular_name;
			
			return($title);
		}
		
		
		/**
		 * 
		 * get post type taxomonies
		 */
		public static function getPostTypeTaxomonies($postType){
			$arrTaxonomies = get_object_taxonomies(array( 'post_type' => $postType ), 'objects');
			
			$arrNames = array();
			foreach($arrTaxonomies as $key=>$objTax){
				$arrNames[$objTax->name] = $objTax->labels->name;
			}
			
			return($arrNames);
		}
		
		/**
		 * 
		 * get post types taxonomies as string
		 */
		public static function getPostTypeTaxonomiesString($postType){
			$arrTax = self::getPostTypeTaxomonies($postType);
			$strTax = "";
			foreach($arrTax as $name=>$title){
				if(!empty($strTax))
					$strTax .= ",";
				$strTax .= $name;
			}
			
			return($strTax);
		}
		
		/**
		 *
		 * get post types array with taxomonies
		 */
		public static function getPostTypesWithTaxomonies(){
			$arrPostTypes = self::getPostTypesAssoc();
		
			foreach($arrPostTypes as $postType=>$title){
				
				$arrTaxomonies = self::getPostTypeTaxomonies($postType);
				
				$arrPostTypes[$postType] = $arrTaxomonies;
			}
			
			$page = UniteFunctionsUC::getVal($arrPostTypes, "page");
			if(empty($page)){
				$page["category"] = "Categories";
				$arrPostTypes["page"] = $page;
			}
						
			return($arrPostTypes);
		}
		
		
		/**
		 *
		 * get array of post types with categories (the taxonomies is between).
		 * get only those taxomonies that have some categories in it.
		 */
		public static function getPostTypesWithCats(){
			
			$arrPostTypes = self::getPostTypesWithTaxomonies();
			
			$arrOutput = array();
			foreach($arrPostTypes as $name=>$arrTax){

				//collect categories
				$arrCats = array();
				foreach($arrTax as $taxName=>$taxTitle){
					
					$cats = self::getCategoriesAssoc($taxName);
					if(!empty($cats))
					foreach($cats as $catID=>$catTitle)
						$arrCats[$catID] = $catTitle;
										
				}
								
				$arrPostType = array();
				$arrPostType["name"] = $name;
				$arrPostType["title"] = self::getPostTypeTitle($name);
				$arrPostType["cats"] = $arrCats;
				
				$arrOutput[$name] = $arrPostType;
			}
			
			return($arrOutput);
		}
		
		
		/**
		 *
		 * get array of post types with categories (the taxonomies is between).
		 * get only those taxomonies that have some categories in it.
		 */
		public static function getPostTypesWithCatIDs(){
			
			$arrTypes = self::getPostTypesWithCats();
			
			$arrOutput = array();
			
			foreach($arrTypes as $typeName => $arrType){
				
				$output = array();
				$output["name"] = $typeName;
				
				$typeTitle = self::getPostTypeTitle($typeName);
				
				//collect categories
				$arrCatsTotal = array();
				
				foreach($arrType as $arr){
					$cats = UniteFunctionsUC::getVal($arr, "cats");
					$catsIDs = array_keys($cats);
					$arrCatsTotal = array_merge($arrCatsTotal, $catsIDs);
				}
				
				$output["title"] = $typeTitle;
				$output["catids"] = $arrCatsTotal;
				
				$arrOutput[$typeName] = $output;
			}
			
			
			return($arrOutput);
		}
		
		
		
		/**
		 * 
		 * get all the post types including custom ones
		 * the put to top items will be always in top (they must be in the list)
		 */
		public static function getPostTypesAssoc($arrPutToTop = array()){
			 $arrBuiltIn = array(
			 	"post"=>"post",
			 	"page"=>"page",
			 );
			 
			 $arrCustomTypes = get_post_types(array('_builtin' => false));
			 
			 
			 //top items validation - add only items that in the customtypes list
			 $arrPutToTopUpdated = array();
			 foreach($arrPutToTop as $topItem){
			 	if(in_array($topItem, $arrCustomTypes) == true){
			 		$arrPutToTopUpdated[$topItem] = $topItem;
			 		unset($arrCustomTypes[$topItem]);
			 	}
			 }
			 
			 $arrPostTypes = array_merge($arrPutToTopUpdated,$arrBuiltIn,$arrCustomTypes);
			 
			 //update label
			 foreach($arrPostTypes as $key=>$type){
				$arrPostTypes[$key] = self::getPostTypeTitle($type);			 		
			 }
			 
			 return($arrPostTypes);
		}
		
		
		
		public static function a_____________TAXANOMIES___________(){}
		
		/**
		 *
		 * get assoc list of the taxonomies
		 */
		public static function getTaxonomiesAssoc(){
			$arr = get_taxonomies();
			
			unset($arr["post_tag"]);
			unset($arr["nav_menu"]);
			unset($arr["link_category"]);
			unset($arr["post_format"]);
		
			return($arr);
		}
		
		
		
		/**
		 *
		 * get array of all taxonomies with categories.
		 */
		public static function getTaxonomiesWithCats(){
			
			if(!empty(self::$arrTaxCache))
				return(self::$arrTaxCache);
			
			$arrTax = self::getTaxonomiesAssoc();
			$arrTaxNew = array();
			foreach($arrTax as $key=>$value){
				$arrItem = array();
				$arrItem["name"] = $key;
				$arrItem["title"] = $value;
				$arrItem["cats"] = self::getCategoriesAssoc($key);
				$arrTaxNew[$key] = $arrItem;
			}
			
			self::$arrTaxCache = $arrTaxNew;
			
			return($arrTaxNew);
		}
		
		
		public static function a__________CATEGORIES_AND_TAGS__________(){}
		
		
		/**
		 * 
		 * get the category data
		 */
		public static function getCategoryData($catID){
			$catData = get_category($catID);
			if(empty($catData))
				return($catData);
				
			$catData = (array)$catData;			
			return($catData);
		}
		
		
		
		/**
		 * 
		 * get post categories by postID and taxonomies
		 * the postID can be post object or array too
		 */
		public static function getPostCategories($postID,$arrTax){
			
			if(!is_numeric($postID)){
				$postID = (array)$postID;
				$postID = $postID["ID"];
			}
				
			$arrCats = wp_get_post_terms( $postID, $arrTax);
			$arrCats = UniteFunctionsUC::convertStdClassToArray($arrCats);
			return($arrCats);
		}

		
		/**
		 *
		 * get post categories list assoc - id / title
		 */
		public static function getCategoriesAssoc($taxonomy = "category", $addNotSelected = false){
			
			if($taxonomy === null)
				$taxonomy = "category";
			
			$arrCats = array();
			
			if($addNotSelected == true)
				$arrCats[""] = __("[All Categories]", ADDONLIBRARY_TEXTDOMAIN);
			
			
			if(strpos($taxonomy,",") !== false){
				$arrTax = explode(",", $taxonomy);
				foreach($arrTax as $tax){
					$cats = self::getCategoriesAssoc($tax);
					$arrCats = array_merge($arrCats,$cats);
				}
		
				return($arrCats);
			}
			
			//$cats = get_terms("category");
			$args = array("taxonomy"=>$taxonomy);
			$args["hide_empty"] = false;
			$args["number"] = 100;
			
			$cats = get_categories($args);
		
			foreach($cats as $cat){
				
				//dmp($cat);exit();
					
				$numItems = $cat->count;
				$itemsName = "items";
				if($numItems == 1)
					$itemsName = "item";
		
				$title = $cat->name . " ($numItems $itemsName)";
		
				$id = $cat->cat_ID;
				$arrCats[$id] = $title;
			}
			return($arrCats);
		}
		
		/**
		 *
		 * get categories by id's
		 */
		public static function getCategoriesByIDs($arrIDs,$strTax = null){
		
			if(empty($arrIDs))
				return(array());
		
			if(is_string($arrIDs))
				$strIDs = $arrIDs;
			else
				$strIDs = implode(",", $arrIDs);
		
			$args = array();
			$args["include"] = $strIDs;
		
			if(!empty($strTax)){
				if(is_string($strTax))
					$strTax = explode(",",$strTax);
		
				$args["taxonomy"] = $strTax;
			}
		
			$arrCats = get_categories( $args );
		
			if(!empty($arrCats))
				$arrCats = UniteFunctionsUC::convertStdClassToArray($arrCats);
		
			return($arrCats);
		}
		
		
		/**
		 *
		 * get categories short
		 */
		public static function getCategoriesByIDsShort($arrIDs,$strTax = null){
			$arrCats = self::getCategoriesByIDs($arrIDs,$strTax);
			$arrNew = array();
			foreach($arrCats as $cat){
				$catID = $cat["term_id"];
				$catName = $cat["name"];
				$arrNew[$catID] =  $catName;
			}
		
			return($arrNew);
		}
		
		
		
		
		/**
		 *
		 * get post tags html list
		 */
		public static function getTagsHtmlList($postID,$before="",$sap=",",$after=""){
			
			$tagList = get_the_tag_list($before,",",$after,$postID);
			
			return($tagList);
		}

		
		/**
		 * get category by slug name
		 */
		public static function getCatIDBySlug($slug){
			
			$arrCats = get_categories(array("hide_empty"=>false));
			
			foreach($arrCats as $cat){
				$cat = (array)$cat;
				$catSlug = $cat["slug"];
				$catID = $cat["term_id"];
				
				if($catSlug == $slug)
					return($catID);
			}
			
			return(null);
		}
		
		public static function a_______________GENERAL_GETTERS____________(){}
		
		
		/**
		 *
		 * get sort by with the names
		 */
		public static function getArrSortBy(){
			$arr = array();
			$arr[self::SORTBY_ID] = "Post ID";
			$arr[self::SORTBY_DATE] = "Date";
			$arr[self::SORTBY_TITLE] = "Title";
			$arr[self::SORTBY_SLUC] = "Slug";
			$arr[self::SORTBY_AUTHOR] = "Author";
			$arr[self::SORTBY_LAST_MODIFIED] = "Last Modified";
			$arr[self::SORTBY_COMMENT_COUNT] = "Number Of Comments";
			$arr[self::SORTBY_RAND] = "Random";
			$arr[self::SORTBY_NONE] = "Unsorted";
			$arr[self::SORTBY_MENU_ORDER] = "Custom Order";
			return($arr);
		}
		
		
		/**
		 *
		 * get array of sort direction
		 */
		public static function getArrSortDirection(){
			$arr = array();
			$arr[self::ORDER_DIRECTION_DESC] = "Descending";
			$arr[self::ORDER_DIRECTION_ASC] = "Ascending";
			return($arr);
		}
		
		public static function a_____________POSTS____________(){}
		
		
		/**
		 *
		 * get single post
		 */
		public static function getPost($postID, $addAttachmentImage = false, $getMeta = false){
			
			$post = get_post($postID);
			if(empty($post))
				UniteFunctionsUC::throwError("Post with id: $postID not found");
		
			$arrPost = $post->to_array();
		
			if($addAttachmentImage == true){
				$arrImage = self::getPostAttachmentImage($postID);
				if(!empty($arrImage))
					$arrPost["image"] = $arrImage;
			}
		
			if($getMeta == true)
				$arrPost["meta"] = self::getPostMeta($postID);
		
			return($arrPost);
		}
		
		
		
		/**
		 * get post meta data
		 */
		public static function getPostMeta($postID){
		
			$arrMeta = get_post_meta($postID);
			foreach($arrMeta as $key=>$item){
				if(is_array($item) && count($item) == 1)
					$arrMeta[$key] = $item[0];
			}
		
		
			return($arrMeta);
		}
		
		
		/**
		 *
		 * get posts post type
		 */
		public static function getPostsByType($postType, $sortBy = self::SORTBY_TITLE){
		
			if(empty($postType))
				$postType = "any";
				
			$query = array(
					'post_type'=>$postType,
					'orderby'=>$sortBy
			);
		
			$arrPosts = get_posts($query);
					
			foreach($arrPosts as $key=>$post){
		
				if(method_exists($post, "to_array"))
					$arrPost = $post->to_array();
				else
					$arrPost = (array)$post;
				
				$arrPosts[$key] = $arrPost;
			}
			
			return($arrPosts);
		}


		/**
		 * get posts post type
		 */
		public static function getPosts($filters){
			
			$args = array();
			
			$args["post_type"] = UniteFunctionsUC::getVal($filters, "posttype");
			$args["category"] = UniteFunctionsUC::getVal($filters, "category");
			$args["orderby"] = UniteFunctionsUC::getVal($filters, "orderby");
			$args["order"] = UniteFunctionsUC::getVal($filters, "orderdir");
			$args["posts_per_page"] = UniteFunctionsUC::getVal($filters, "limit");
			
			$arrPosts = get_posts($args);
			
			return($arrPosts);
		}

		/**
		 * get post thumb id from post id
		 */
		public static function getFeaturedImageID($postID){
			$thumbID = get_post_thumbnail_id( $postID );
			return($thumbID);
		}
		
		
		/**
		 * get post id by name, using DB
		 */
		public static function isPostNameExists($postName){
			
			$tablePosts = UniteProviderFunctionsUC::$tablePosts;
			
			$db = self::getDB();
			$response = $db->fetch($tablePosts, array("post_name"=>$postName));
			
			$isExists = !empty($response);
			
			return($isExists);
		}
		
		/**
		 * get post id by post name
		 */
		public static function getPostIDByPostName(){
			
			$tablePosts = UniteProviderFunctionsUC::$tablePosts;
			
			$db = self::getDB();
			$response = $db->fetch($tablePosts, array("post_name"=>$postName));
			
			if(empty($response))
				return(null);
			
			$postID = $response[0]["ID"];
			
			return($postID);
		}
		
		
		/**
		 * update post content
		 */
		public static function updatePostContent($postID, $content){
			
			$arrData = array(
			      'ID'           => $postID,
			      'post_content' => $content,
			 );		
			  	
			$wpError = wp_update_post( $arrData ,true);
			
			if (is_wp_error($wpError)) {
    			UniteFunctionsUC::throwError("Error updating post: $postID");
			}
			
		}
		
		
		/**
		 * update post ordering
		 */
		public static function updatePostOrdering($postID, $ordering){
			
			$arrData = array(
			      'ID'           => $postID,
			      'menu_order' => $ordering,
			 );		
			
			$wpError = wp_update_post( $arrData ,true);
			
			//dmp($arrData);
			
			if (is_wp_error($wpError)) {
    			UniteFunctionsUC::throwError("Error updating post: $postID");
			}
			
		}
		
		
		/**
		 * insert post
		 * params: [cat_slug, content]
		 */
		public static function insertPost($title, $alias, $params = array()){
			
			$catSlug = UniteFunctionsUC::getVal($params, "cat_slug");
			$content = UniteFunctionsUC::getVal($params, "content");
			$isPage = UniteFunctionsUC::getVal($params, "ispage");
			$isPage = UniteFunctionsUC::strToBool($isPage);
			
			$catID = null;
			if(!empty($catSlug)){
				$catID = self::getCatIDBySlug($catSlug);
				if(empty($catID))
					UniteFunctionsUC::throwError("Category id not found by slug: $slug");
			}
			
			$isPostExists = self::isPostNameExists($alias);
			
			if($isPostExists == true)
				UniteFunctionsUC::throwError("Post with name: <b> {$alias} </b> already exists");
			
			
			$arguments = array();
			$arguments["post_title"] = $title;
			$arguments["post_name"] = $alias;
			$arguments["post_status"] = "publish";
			
			if(!empty($content))
				$arguments["post_content"] = $content;
			
			if(!empty($catID))
				$arguments["post_category"] = array($catID);
			
			if($isPage == true)
				$arguments["post_type"] = "page";
				
			$newPostID = wp_insert_post($arguments, true);
			
			
			if(is_wp_error($newPostID)){
				$errorMessage = $newPostID->get_error_message();
				UniteFunctionsUC::throwError($errorMessage);
			}
			
			
			return($newPostID);
		}
		
		
		/**
		 * insert new page
		 */
		public static function insertPage($title, $alias, $params = array()){
			
			$params["ispage"] = true;
			
			$pageID = self::insertPost($title, $alias, $params);
			
			return($pageID);
		}
		
		
		/**
		 * delete all post metadata
		 */
		public static function deletePostMetadata($postID){
			
			$postID = (int)$postID;
			
			$tablePostMeta = UniteProviderFunctionsUC::$tablePostMeta;
			
			$db = self::getDB();
			$db->delete($tablePostMeta, "post_id=$postID");
		}
		
		public static function a__________ATTACHMENT__________(){}
		
		/**
		 *
		 * get attachment image url
		 */
		public static function getUrlAttachmentImage($thumbID, $size = self::THUMB_FULL){
			
			$arrImage = wp_get_attachment_image_src($thumbID, $size);
			if(empty($arrImage))
				return(false);
			
			$url = UniteFunctionsUC::getVal($arrImage, 0);
			return($url);
		}
		
		
		
		
		/**
		 * get attachment data
		 */
		public static function getAttachmentData($thumbID){
			
			if(is_numeric($thumbID) == false)
				return(null);
			
			$post = get_post($thumbID);
			if(empty($post))
				return(null);
			
			$title = wp_get_attachment_caption($thumbID);
				
			$item = array();
			$item["image_id"] = $post->ID;
			$item["image"] = $post->guid;
			
			if(empty($title))
				$title = $post->post_title;
			
			$urlThumb = self::getUrlAttachmentImage($thumbID,self::THUMB_MEDIUM);
			if(empty($urlThumb))
				$urlThumb = $post->guid;
			
			$item["thumb"] = $urlThumb;
			
			$item["title"] = $title;
			$item["description"] = $post->post_content;
			$item["alt"] = $altText;
			$item["caption"] = $caption;
			
			return($item);
		}
		
		
		/**
		 * get thumbnail sizes array
		 * mode: null, "small_only", "big_only"
		 */
		public static function getArrThumbSizes($mode = null){
			global $_wp_additional_image_sizes;
			
			$arrWPSizes = get_intermediate_image_sizes();
		
			$arrSizes = array();
		
			if($mode != "big_only"){
				$arrSizes[self::THUMB_SMALL] = "Thumbnail (150x150)";
				$arrSizes[self::THUMB_MEDIUM] = "Medium (max width 300)";
			}
		
			if($mode == "small_only")
				return($arrSizes);
		
			foreach($arrWPSizes as $size){
				$title = ucfirst($size);
				switch($size){
					case self::THUMB_LARGE:
					case self::THUMB_MEDIUM:
					case self::THUMB_FULL:
					case self::THUMB_SMALL:
						continue(2);
						break;
					case "ug_big":
						$title = __("Big", ADDONLIBRARY_TEXTDOMAIN);
						break;
				}
		
				$arrSize = UniteFunctionsUC::getVal($_wp_additional_image_sizes, $size);
				$maxWidth = UniteFunctionsUC::getVal($arrSize, "width");
		
				if(!empty($maxWidth))
					$title .= " (max width $maxWidth)";
		
				$arrSizes[$size] = $title;
			}
		
			$arrSizes[self::THUMB_LARGE] = __("Large (max width 1024)", ADDONLIBRARY_TEXTDOMAIN);
			$arrSizes[self::THUMB_FULL] = __("Full", ADDONLIBRARY_TEXTDOMAIN);
		
			return($arrSizes);
		}
		
		
		/**
		 * Get an attachment ID given a URL.
		*
		* @param string $url
		*
		* @return int Attachment ID on success, 0 on failure
		*/
		public static function getAttachmentIDFromImageUrl( $url ) {
		
			$attachment_id = 0;
		
			$dir = wp_upload_dir();
		
			if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
		
				$file = basename( $url );
		
				$query_args = array(
						'post_type'   => 'attachment',
						'post_status' => 'inherit',
						'fields'      => 'ids',
						'meta_query'  => array(
								array(
										'value'   => $file,
										'compare' => 'LIKE',
										'key'     => '_wp_attachment_metadata',
								),
						)
				);
				
				$query = new WP_Query( $query_args );
		
				if ( $query->have_posts() ) {
		
					foreach ( $query->posts as $post_id ) {
		
						$meta = wp_get_attachment_metadata( $post_id );
		
						$original_file       = basename( $meta['file'] );
						$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
		
						if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
							$attachment_id = $post_id;
							break;
						}
		
					}
		
				}
		
			}
		
			return $attachment_id;
		}		
		
		
		
		public static function a__________OTHER_FUNCTIONS__________(){}
		
		
		/**
		 *
		 * get wp-content path
		 */
		public static function getPathUploads(){
			
			if(is_multisite()){
				if(!defined("BLOGUPLOADDIR")){
					$pathBase = self::getPathBase();
					$pathContent = $pathBase."wp-content/uploads/";
				}else
					$pathContent = BLOGUPLOADDIR;
			}else{
				$pathContent = WP_CONTENT_DIR;
				if(!empty($pathContent)){
					$pathContent .= "/";
				}
				else{
					$pathBase = self::getPathBase();
					$pathContent = $pathBase."wp-content/uploads/";
				}
			}
		
			return($pathContent);
		}
		
		
		
		
		
		/**
		 *
		 * simple enqueue script
		 */
		public static function addWPScript($scriptName){
			wp_enqueue_script($scriptName);
		}
		
		/**
		 *
		 * simple enqueue style
		 */
		public static function addWPStyle($styleName){
			wp_enqueue_style($styleName);
		}
		
		
		/**
		 *
		 * check if some db table exists
		 */
		public static function isDBTableExists($tableName){
			global $wpdb;
		
			if(empty($tableName))
				UniteFunctionsUC::throwError("Empty table name!!!");
		
			$sql = "show tables like '$tableName'";
		
			$table = $wpdb->get_var($sql);
		
			if($table == $tableName)
				return(true);
		
			return(false);
		}
		
		/**
		 *
		 * validate permission that the user is admin, and can manage options.
		 */
		public static function isAdminPermissions(){
		
			if( is_admin() &&  current_user_can("manage_options") )
				return(true);
		
			return(false);
		}
		
		
		/**
		 * add shortcode
		 */
		public static function addShortcode($shortcode, $function){
		
			add_shortcode($shortcode, $function);
		
		}
		
		/**
		 *
		 * add all js and css needed for media upload
		 */
		public static function addMediaUploadIncludes(){
		
			self::addWPScript("thickbox");
			self::addWPStyle("thickbox");
			self::addWPScript("media-upload");
		
		}
		
		
		
		
		/**
		 * check if post exists by title
		 */
		public static function isPostExistsByTitle($title, $postType){
			
			$post = get_page_by_title( $title, ARRAY_A, $postType );
			
			return !empty($post);
		}
		
		
		
		
		/**
		 * tells if the page is posts of pages page
		 */
		public static function isAdminPostsPage(){
			
			$screen = get_current_screen();
			$screenID = $screen->base;
			if(empty($screenID))
				$screenID = $screen->id;
			
			
			if($screenID != "page" && $screenID != "post")
				return(false);
			
			
			return(true);
		}
		
		
		/**
		 *
		 * register widget (must be class)
		 */
		public static function registerWidget($widgetName){
			add_action('widgets_init', create_function('', 'return register_widget("'.$widgetName.'");'));
		}
		
		
		
	}	//end of the class
	
	//init the static vars
	UniteFunctionsWPUC::initStaticVars();
	
?>