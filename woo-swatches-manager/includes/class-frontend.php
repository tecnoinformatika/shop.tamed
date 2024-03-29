<?php

/**
 * Class JC_WC_Swatches_Manager_Frontend
 */
class JC_WC_Swatches_Manager_Frontend {
	/**
	 * The single instance of the class
	 *
	 * @var JC_WC_Swatches_Manager_Frontend
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return JC_WC_Swatches_Manager_Frontend
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'get_swatch_html' ), 100, 2 );
		add_filter( 'jcsmw_swatch_html', array( $this, 'swatch_html' ), 5, 4 );
	}

	/**
	 * Enqueue scripts and stylesheets
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wpb-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );
		wp_enqueue_style( 'jcsmw-frontend', plugins_url( 'assets/css/frontend.css', dirname( __FILE__ ) ), array(), '20160615' );
		wp_enqueue_script( 'jcsmw-frontend', plugins_url( 'assets/js/frontend.js', dirname( __FILE__ ) ), array( 'jquery' ), '20160615', true );
	}

	/**
	 * Filter function to add swatches bellow the default selector
	 *
	 * @param $html
	 * @param $args
	 *
	 * @return string
	 */
	public function get_swatch_html( $html, $args ) {
		$swatch_types = JC_SMW()->types;
		$attr         = JC_SMW()->get_tax_attribute( $args['attribute'] );

		// Return if this is normal attribute
		if ( empty( $attr ) ) {
			return $html;
		}

		if ( ! array_key_exists( $attr->attribute_type, $swatch_types ) ) {
			return $html;
		}

		$options   = $args['options'];
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$class     = "variation-selector variation-select-{$attr->attribute_type}";
		$swatches  = '';

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[$attribute];
		}

		if ( array_key_exists( $attr->attribute_type, $swatch_types ) ) {
			if ( ! empty( $options ) && $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options ) ) {
						$swatches .= apply_filters( 'jcsmw_swatch_html', '', $term, $attr, $args );
					}
				}
			}

			if ( ! empty( $swatches ) ) {
				$class .= ' hidden';

				$swatches = '<div class="jcsmw-swatches" data-attribute_name="attribute_' . esc_attr( $attribute ) . '">' . $swatches . '</div>';
				$html     = '<div class="' . esc_attr( $class ) . '">' . $html . '</div>' . $swatches;
			}
		}

		return $html;
	}

	/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $attr
	 * @param $args
	 *
	 * @return string
	 */
	public function swatch_html( $html, $term, $attr, $args ) {
		$selected = sanitize_title( $args['selected'] ) == $term->slug ? 'selected' : '';
		$name     = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );

		switch ( $attr->attribute_type ) {
			case 'color':
				$color = get_term_meta( $term->term_id, 'color', true );
				if ( wsm_jc()->is__premium_only() ) {
					$fkey = $attr->attribute_type .'_shape';
					$shape = get_term_meta( $term->term_id, $fkey, true );
					
					list( $r, $g, $b ) = sscanf( $color, "#%02x%02x%02x" );
					$html = sprintf(
						'<span class="swatch swatch-color swatch-%s %s" style="color:%s;" title="%s" data-value="%s"><i class="fa fa-'.$shape.'" aria-hidden="true"></i></span>',
						esc_attr( $term->slug ),
						$selected,
						esc_attr( $color ),
						"rgba($r,$g,$b,0.5)",
						esc_attr( $name ),
						esc_attr( $term->slug ),
						$name
					);
				
				}
				
				if ( wsm_jc()->is_not_paying() ) {
					list( $r, $g, $b ) = sscanf( $color, "#%02x%02x%02x" );
					$html = sprintf(
						'<span class="swatch swatch-color swatch-%s %s" style="background-color:%s;color:%s;" title="%s" data-value="%s">%s</span>',
						esc_attr( $term->slug ),
						$selected,
						esc_attr( $color ),
						"rgba($r,$g,$b,0.5)",
						esc_attr( $name ),
						esc_attr( $term->slug ),
						$name
					);
				}
				
				
				break;

			case 'image':
				$image = get_term_meta( $term->term_id, 'image', true );
				$image = $image ? wp_get_attachment_image_src( $image ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				$html  = sprintf(
					'<span class="swatch swatch-image swatch-%s %s" title="%s" data-value="%s"><img src="%s" alt="%s">%s</span>',
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					esc_url( $image ),
					esc_attr( $name ),
					esc_attr( $name )
				);
				break;

			case 'label':
				$label = get_term_meta( $term->term_id, 'label', true );
				$label = $label ? $label : $name;
				$html  = sprintf(
					'<span class="swatch swatch-label swatch-%s %s" title="%s" data-value="%s">%s</span>',
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					esc_html( $label )
				);
				break;
		}

		return $html;
	}
}