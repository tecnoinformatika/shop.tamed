<?php

/**
 * Class JC_WC_Swatches_Manager_Admin
 */
class JC_WC_Swatches_Manager_Admin {
	/**
	 * The single instance of the class
	 *
	 * @var JC_WC_Swatches_Manager_Admin
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return JC_WC_Swatches_Manager_Admin
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action( 'admin_init', array( $this, 'init_attribute_hooks' ) );
		add_action( 'admin_print_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 2 );

		// Display attribute fields
		add_action( 'jcsmw_product_attribute_field', array( $this, 'attribute_fields' ), 10, 3 );

		// ajax add attribute
		add_action( 'wp_ajax_jcsmw_add_new_attribute', array( $this, 'add_new_attribute_ajax' ) );

		add_action( 'admin_footer', array( $this, 'add_attribute_term_template' ) );
	}

	/**
	 * Init hooks for adding fields to attribute screen
	 * Save new term meta
	 * Add thumbnail column for attribute term
	 */
	public function init_attribute_hooks() {
		
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( empty( $attribute_taxonomies ) ) {
			return;
		}

		foreach ( $attribute_taxonomies as $tax ) {
			add_action( 'pa_' . $tax->attribute_name . '_add_form_fields', array( $this, 'add_attribute_fields' ) );
			add_action( 'pa_' . $tax->attribute_name . '_edit_form_fields', array( $this, 'edit_attribute_fields' ), 10, 2 );

			add_filter( 'manage_edit-pa_' . $tax->attribute_name . '_columns', array( $this, 'add_attribute_columns' ) );
			add_filter( 'manage_pa_' . $tax->attribute_name . '_custom_column', array( $this, 'add_attribute_column_content' ), 10, 3 );
		}

		add_action( 'created_term', array( $this, 'save_term_meta' ), 10, 2 );
		add_action( 'edit_term', array( $this, 'save_term_meta' ), 10, 2 );
	}




	/**
	 * menu function.
	 */
	public function admin_menu() {

		add_submenu_page('woocommerce', esc_html__('WooCommerce Swatches Manager', 'jcsmw'), esc_html__('WooCommerce Swatches Manager', 'jcsmw'), 
		'manage_woocommerce', 'woo-swatches-manager', array($this, 'swatches_manager'));

	}



	public function swatches_manager(){
		?>


		<style type="text/css" media="screen">
	.dod_container h2{
		margin:  5px 0;
	}
	.dod-nav-tab-wrapper{
		margin-bottom: 0px;
	}
</style>
<div class="" id="icon">
    <h2><?php echo esc_html__('Welcome to WooCommerce Swatches Manager','jcsmw'); ?></h2>
    <br>
</div>
<h2 class="nav-tab-wrapper jcsmw-nav-tab-wrapper">


    <a href="admin.php?page=woo-swatches-manager&amp;tab=advance-options" class="nav-tab <?php $this->get_active('advance-options'); ?>"><?php esc_html_e('Advance Options','jcsmw'); ?></a>
    <a href="admin.php?page=woo-swatches-manager&amp;tab=how-to-use" class="nav-tab <?php $this->get_active('how-to-use'); ?>"><?php esc_html_e('How To Use','jcsmw'); ?></a>
</h2>
<br class="clear">

<?php if( isset($_GET['tab']) && $_GET['tab'] == 'advance-options' ) {
			?>


			<div class="premium features">
			
			<p><a href="http://jcodex.com/woocommerce-swatches-for-products-variation/">
			<img src="<?php echo plugins_url( '../assets/premium-banner.png', __FILE__ ); ?>" />
			</a></p>
			</div>
			<?php
	}

	elseif( isset($_GET['tab']) && $_GET['tab'] == 'how-to-use' ){
		?>

	<div>
	 <div style="width:70%; float:left;">
	 <p><?php esc_html_e("Even this plugin has been installed and activated on your site, variable products will still show dropdowns if you've not configured product attributes.",'jcsmw'); ?></p>
	<ul>
		<li><?php esc_html_e('1. Log in to your WordPress dashboard, navigate to the Products menu and click Attributes.','jcsmw'); ?></li>
		<li><?php esc_html_e('2. Click to attribute name to edit an exists attribute or in the Add New Attribute form you will see the default Type selector.','jcsmw'); ?></li>
		<li><?php esc_html_e("3. Click to that Type selector to change attribute's type. Besides default options Select and Text, there are more 3 options Color, Image, Label to choose.","jcsmw"); ?></li>
		<li><?php esc_html_e("4. Select the suitable type for your attribute and click Save Change/Add attribute","jcsmw"); ?></li>
		<li><?php esc_html_e('5. Go back to manage attributes screen. Click the cog icon on the right side of attribute to start editing terms.','jcsmw'); ?></li>
		<li><?php esc_html_e('6. Start adding new terms or editing exists terms. There is will be a new option at the end of form that let you choose the color, upload image or type the label for those terms.','jcsmw'); ?></li>
	</ul>
	 </div>
	 <div style="width:30%; float:right;">
		<p><a href="http://jcodex.com/woocommerce-variation-swatches/">
		<img src="<?php echo plugins_url( '../assets/premium-banner.png', __FILE__ ); ?>" />
		</a></p>
	 </div>
	</div>


		<?php
	} else {
		?>
		
		<div class="premium features">
		
			
			<p><a href="http://jcodex.com/woocommerce-variation-swatches/">
			<img src="<?php echo plugins_url( '../assets/premium-banner.png', __FILE__ ); ?>" />
			</a></p>
		 
			</div>
		
		<?php
	}
?>		

		<?php
	}
	/**
	 * Load stylesheet and scripts in edit product attribute screen
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'edit-pa_' ) === false && strpos( $screen->id, 'product' ) === false ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style( 'jcsmw-admin', plugins_url( '/assets/css/admin.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ), '20160615' );
		wp_enqueue_script( 'jcsmw-admin', plugins_url( '/assets/js/admin.js', dirname( __FILE__ ) ), array( 'jquery', 'wp-color-picker', 'wp-util' ), '20170113', true );

		wp_localize_script(
			'jcsmw-admin',
			'jcsmw',
			array(
				'i18n'        => array(
					'mediaTitle'  => esc_html__( 'Choose an image', 'jcsmw' ),
					'mediaButton' => esc_html__( 'Use image', 'jcsmw' ),
				),
				'placeholder' => WC()->plugin_url() . '/assets/images/placeholder.png'
			)
		);
	}

	/**
	 * Create hook to add fields to add attribute term screen
	 *
	 * @param string $taxonomy
	 */
	public function add_attribute_fields( $taxonomy ) {
		$attr = JC_SMW()->get_tax_attribute( $taxonomy );

		//do_action( 'jcsmw_product_attribute_field', $attr->attribute_type, '', 'add' );
	}

	/**
		 * Make the tab active on current page
		 * @return string
		 */
		public static function get_active($tab) {
			if (isset($_GET['tab'])) {
				echo $_GET['tab'] == $tab ? 'nav-tab-active' : '';
			} 

			elseif ($tab == 'create-deal') {
			echo 'nav-tab-active';
		}
	}


	/**
	 * Create hook to fields to edit attribute term screen
	 *
	 * @param object $term
	 * @param string $taxonomy
	 */
	public function edit_attribute_fields( $term, $taxonomy ) {
		$attr  = JC_SMW()->get_tax_attribute( $taxonomy );
		$value = get_term_meta( $term->term_id, $attr->attribute_type, true );

		do_action( 'jcsmw_product_attribute_field', $attr->attribute_type, $value, 'edit' );
		if ( wsm_jc()->is__premium_only() ) {
			$shapevalue = get_term_meta( $term->term_id, $attr->attribute_type.'_shape', true );
			do_action( 'jcsmw_product_attribute_field', $attr->attribute_type.'_shape', $shapevalue, 'edit' );
			}
	}

	/**
	 * Print HTML of custom fields on attribute term screens
	 *
	 * @param $type
	 * @param $value
	 * @param $form
	 */
	public function attribute_fields( $type, $value, $form ) {
		// Return if this is a default attribute type
		if ( in_array( $type, array( 'select', 'text' ) ) ) {
			return;
		}
		
		if ( wsm_jc()->is_not_paying() ) {
			printf(
				'<%s class="form-field">%s<label for="term-%s">%s</label>%s',
				'edit' == $form ? 'tr' : 'div',
				'edit' == $form ? '<th>' : '',
				esc_attr( $type ),
				JC_SMW()->types[$type],
				'edit' == $form ? '</th><td>' : ''
			);
		}
	if ( wsm_jc()->is__premium_only() ) {
		// Print the open tag of field container
		if($type != 'color_shape'){
			printf(
			'<%s class="form-field">%s<label for="term-%s">%s</label>%s',
			'edit' == $form ? 'tr' : 'div',
			'edit' == $form ? '<th>' : '',
			esc_attr( $type ),
			JC_SMW()->types[$type],
			'edit' == $form ? '</th><td>' : ''
		);
		} else{
			printf(
			'<%s class="form-field">%s<label for="term-%s">Shape</label>%s',
			'edit' == $form ? 'tr' : 'div',
			'edit' == $form ? '<th>' : '',
			esc_attr( $type ),
			JC_SMW()->types[$type],
			'edit' == $form ? '</th><td>' : ''
		);
		}
		
	}
		

		switch ( $type ) {
			
			case 'image':
				$image = $value ? wp_get_attachment_image_src( $value ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				?>
				<div class="jcsmw-term-image-thumbnail" style="float:left;margin-right:10px;">
					<img src="<?php echo esc_url( $image ) ?>" width="60px" height="60px" />
				</div>
				<div style="line-height:60px;">
					<input type="hidden" class="jcsmw-term-image" name="image" value="<?php echo esc_attr( $value ) ?>" />
					<button type="button" class="jcsmw-upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'jcsmw' ); ?></button>
					<button type="button" class="jcsmw-remove-image-button button <?php echo $value ? '' : 'hidden' ?>"><?php esc_html_e( 'Remove image', 'jcsmw' ); ?></button>
				</div>
				<?php
				break;


			case 'color_shape':
			?>
		<select name="<?php echo esc_attr( $type ) ?>">

         <option value="heart" <?php selected($value, "heart"); ?>><?php esc_html_e('Heart','jcsmw'); ?></option>
          <option value="square" <?php selected($value, "square"); ?>><?php esc_html_e('Square','jcsmw'); ?></option>
           <option value="circle" <?php selected($value, "circle"); ?>><?php esc_html_e("Circle",'jcsmw'); ?></option>
         
        </select>
			<?php
			break;


			default:
				?>
				<input type="text" id="term-<?php echo esc_attr( $type ) ?>" name="<?php echo esc_attr( $type ) ?>" value="<?php echo esc_attr( $value ) ?>" />
				<?php
				break;
		}

		// Print the close tag of field container
		echo 'edit' == $form ? '</td></tr>' : '</div>';
	}

	/**
	 * Save term meta
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 */
	public function save_term_meta( $term_id, $tt_id ) {
		foreach ( JC_SMW()->types as $type => $label ) {
			if ( isset( $_POST[$type] ) ) {
				update_term_meta( $term_id, $type, esc_attr($_POST[$type]) );
				update_term_meta( $term_id, $type.'_shape', esc_attr($_POST[$type.'_shape']) );
			}
		}
	}

	/**
	 * Add selector for extra attribute types
	 *
	 * @param $taxonomy
	 * @param $index
	 */
	public function product_option_terms( $taxonomy, $index ) {
		if ( ! array_key_exists( $taxonomy->attribute_type, JC_SMW()->types ) ) {
			return;
		}

		$taxonomy_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );
		global $thepostid;
		?>

		<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'jcsmw' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo $index; ?>][]">
			<?php

			$all_terms = get_terms( $taxonomy_name, apply_filters( 'woocommerce_product_attribute_terms', array( 'orderby' => 'name', 'hide_empty' => false ) ) );
			if ( $all_terms ) {
				foreach ( $all_terms as $term ) {
					echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy_name, $thepostid ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
				}
			}
			?>
		</select>
		<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'jcsmw' ); ?></button>
		<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'jcsmw' ); ?></button>
		<button class="button fr plus jcsmw_add_new_attribute" data-type="<?php echo $taxonomy->attribute_type ?>"><?php esc_html_e( 'Add new', 'jcsmw' ); ?></button>

		<?php
	}

	/**
	 * Add thumbnail column to column list
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_attribute_columns( $columns ) {
		$new_columns          = array();
		$new_columns['cb']    = $columns['cb'];
		$new_columns['thumb'] = '';
		unset( $columns['cb'] );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Render thumbnail HTML depend on attribute type
	 *
	 * @param $columns
	 * @param $column
	 * @param $term_id
	 */
	public function add_attribute_column_content( $columns, $column, $term_id ) {
		$attr  = JC_SMW()->get_tax_attribute( $_REQUEST['taxonomy'] );
		$value = get_term_meta( $term_id, $attr->attribute_type, true );

		switch ( $attr->attribute_type ) {
			case 'color':
				printf( '<div class="swatch-preview swatch-color" style="background-color:%s;"></div>', esc_attr( $value ) );
				break;

			case 'image':
				$image = $value ? wp_get_attachment_image_src( $value ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				printf( '<img class="swatch-preview swatch-image" src="%s" width="44px" height="44px">', esc_url( $image ) );
				break;

			case 'label':
				printf( '<div class="swatch-preview swatch-label">%s</div>', esc_html( $value ) );
				break;
		}
	}

	/**
	 * Print HTML of modal at admin footer and add js templates
	 */
	public function add_attribute_term_template() {
		global $pagenow, $post;

		if ( $pagenow != 'post.php' || ( isset( $post ) && get_post_type( $post->ID ) != 'product' ) ) {
			return;
		}
		?>

		<div id="jcsmw-modal-container" class="jcsmw-modal-container">
			<div class="jcsmw-modal">
				<button type="button" class="button-link media-modal-close jcsmw-modal-close">
					<span class="media-modal-icon"></span></button>
				<div class="jcsmw-modal-header"><h2><?php esc_html_e( 'Add new term', 'jcsmw' ) ?></h2></div>
				<div class="jcsmw-modal-content">
					<p class="jcsmw-term-name">
						<label>
							<?php esc_html_e( 'Name', 'jcsmw' ) ?>
							<input type="text" class="widefat jcsmw-input" name="name">
						</label>
					</p>
					<p class="jcsmw-term-slug">
						<label>
							<?php esc_html_e( 'Slug', 'jcsmw' ) ?>
							<input type="text" class="widefat jcsmw-input" name="slug">
						</label>
					</p>
					<div class="jcsmw-term-swatch">

					</div>
					<div class="hidden jcsmw-term-tax"></div>

					<input type="hidden" class="jcsmw-input" name="nonce" value="<?php echo wp_create_nonce( '_jcsmw_create_attribute' ) ?>">
				</div>
				<div class="jcsmw-modal-footer">
					<button class="button button-secondary jcsmw-modal-close"><?php esc_html_e( 'Cancel', 'jcsmw' ) ?></button>
					<button class="button button-primary jcsmw-new-attribute-submit"><?php esc_html_e( 'Add New', 'jcsmw' ) ?></button>
					<span class="message"></span>
					<span class="spinner"></span>
				</div>
			</div>
			<div class="jcsmw-modal-backdrop media-modal-backdrop"></div>
		</div>

		<script type="text/template" id="tmpl-jcsmw-input-color">

			<label><?php esc_html_e( 'Color', 'jcsmw' ) ?></label><br>

			<input type="text" class="jcsmw-input jcsmw-input-color" name="swatch">
			<?php
			if ( wsm_jc()->is__premium_only() ) {
				?>
			<br><br>
			<label><?php esc_html_e( 'Shape', 'jcsmw' ) ?></label>

			<select name="shape" class="widefat jcsmw-input jcsmw-select-shape">
				<option value="circle"><?php esc_html_e( 'Circle', 'jcsmw' ) ?></option>
				<option value="heart"><?php esc_html_e( 'Heart', 'jcsmw' ) ?></option>
				<option value="square"><?php esc_html_e( 'Square', 'jcsmw' ) ?></option>

			</select>
		<?php
			}
			?>
		</script>

		<script type="text/template" id="tmpl-jcsmw-input-image">

			<label><?php esc_html_e( 'Image', 'jcsmw' ) ?></label><br>
			<div class="jcsmw-term-image-thumbnail" style="float:left;margin-right:10px;">
				<img src="<?php echo esc_url( WC()->plugin_url() . '/assets/images/placeholder.png' ) ?>" width="60px" height="60px" />
			</div>
			<div style="line-height:60px;">
				<input type="hidden" class="jcsmw-input jcsmw-input-image jcsmw-term-image" name="swatch" value="" />
				<button type="button" class="jcsmw-upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'jcsmw' ); ?></button>
				<button type="button" class="jcsmw-remove-image-button button hidden"><?php esc_html_e( 'Remove image', 'jcsmw' ); ?></button>
			</div>

		</script>

		<script type="text/template" id="tmpl-jcsmw-input-label">

			<label>
				<?php esc_html_e( 'Label', 'jcsmw' ) ?>
				<input type="text" class="widefat jcsmw-input jcsmw-input-label" name="swatch">
			</label>

		</script>

		<script type="text/template" id="tmpl-jcsmw-input-tax">

			<input type="hidden" class="jcsmw-input" name="taxonomy" value="{{data.tax}}">
			<input type="hidden" class="jcsmw-input" name="type" value="{{data.type}}">

		</script>
		<?php
	}

	/**
	 * Ajax function to handle add new attribute term
	 */
	public function add_new_attribute_ajax() {
		$nonce  = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$tax    = isset( $_POST['taxonomy'] ) ? esc_attr($_POST['taxonomy']) : '';
		$type   = isset( $_POST['type'] ) ? esc_attr($_POST['type']) : '';
		$name   = isset( $_POST['name'] ) ? esc_attr($_POST['name']) : '';
		$slug   = isset( $_POST['slug'] ) ? esc_attr($_POST['slug']) : '';
		$swatch = isset( $_POST['swatch'] ) ? esc_attr($_POST['swatch']) : '';
		if ( wsm_jc()->is__premium_only() ) {
			$shape = isset( $_POST['shape'] ) ? esc_attr($_POST['shape']) : '';
		}
		if ( ! wp_verify_nonce( $nonce, '_jcsmw_create_attribute' ) ) {
			wp_send_json_error( esc_html__( 'Wrong request', 'jcsmw' ) );
		}

		if ( empty( $name ) || empty( $swatch ) || empty( $tax ) || empty( $type ) ) {
			wp_send_json_error( esc_html__( 'Not enough data', 'jcsmw' ) );
		}

		if ( ! taxonomy_exists( $tax ) ) {
			wp_send_json_error( esc_html__( 'Taxonomy is not exists', 'jcsmw' ) );
		}

		if ( term_exists( $_POST['name'], $_POST['tax'] ) ) {
			wp_send_json_error( esc_html__( 'This term is exists', 'jcsmw' ) );
		}

		$term = wp_insert_term( $name, $tax, array( 'slug' => $slug ) );

		if ( is_wp_error( $term ) ) {
			wp_send_json_error( $term->get_error_message() );
		} else {
			$term = get_term_by( 'id', $term['term_id'], $tax );
			update_term_meta( $term->term_id, $type, $swatch );
			if ( wsm_jc()->is__premium_only() ) {
				$fkey = $type .'_shape';
				update_term_meta( $term->term_id, $fkey, $shape );
			}
		}

		wp_send_json_success(
			array(
				'msg'  => esc_html__( 'Added successfully', 'jcsmw' ),
				'id'   => $term->term_id,
				'slug' => $term->slug,
				'name' => $term->name,
			)
		);
	}
}
