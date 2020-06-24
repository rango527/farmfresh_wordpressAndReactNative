<?php
/**
 * Product quantity inputs // Drop down by WPWhale
 *
 * @version 4.0.0
 * @since   1.6.0
 * @todo    [dev] (important) re-check new template in WC 3.6
 * @todo    [dev] (important) dropdown: variable products (check "validate on add to cart") (i.e. without fallbacks)
 * @todo    [dev] (important) dropdown: maybe fallback min & step?
 * @todo    [dev] (important) dropdown: "exact (i.e. fixed)" quantity: variable products
 * @todo    [dev] (important) dropdown: "exact (i.e. fixed)" quantity: add zero in cart?
 * @todo    [dev] (maybe) dropdown: labels: per variation
 *
 * wc_version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $product_id ) ) {
	$product = wc_get_product( $product_id );
} elseif ( isset( $GLOBALS['product'] ) ) {
	$product = $GLOBALS['product'];
} else {
	$product = false;
}

$variation_max = 0;
$variation_exact = '';
$productType =  $product->get_type();
if( $productType == 'variable' ) {
	if ( $_product = wc_get_product( $product_id ) ) {
		foreach ( $_product->get_available_variations() as $variation ) {
			$variation_id = $variation['variation_id'];
			if( $variation_max <= 0 ) {
				$variation_max = alg_wc_pq()->core->get_product_qty_min_max (  $_product->get_id(), (float) $variation['max_qty'], 'max', $variation_id );
			}
			if( empty( $variation_exact) ) {
				$variation_exact = alg_wc_pq()->core->get_product_exact_qty( $_product->get_id(), 'allowed', '', $variation_id );
			}
		}
	}
}
if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php
} elseif ( ( ( ! empty( $max_value ) || 0 != ( $max_value_fallback = get_option( 'alg_wc_pq_qty_dropdown_max_value_fallback', 0 ) ) ) && ! empty( $step ) ) ||  ( $productType=='variable' && ( $variation_max > 0 || ! empty($variation_exact) )  ) ) { // dropdown
	if ( empty( $max_value ) ) {
		$max_value = $max_value_fallback;
	}
	?>
	<div class="quantity">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_before', '' ) ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></label>
		<select
			id="<?php echo esc_attr( $input_id ); ?>_pq_dropdown"
			class="qty ajax-ready"
			name="<?php echo esc_attr( $input_name ); ?>_pq_dropdown"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>" >
			<?php
			// Values
			$values = array();
			for ( $i = $min_value; $i <= $max_value; $i = $i + $step ) {
				$values[] = $i;
			}
			
			if ( ! empty( $input_value ) && ! in_array( $input_value, $values ) && $input_value > $min_value ) {
				$values[] = $input_value;
			}
			
			
			// Fixed qty
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( $product && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), $allowed_or_disallowed ) ) ) {
					$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
					foreach ( $values as $i => $value ) {
						if (
							( 'allowed'    === $allowed_or_disallowed && ! in_array( $value, $fixed_qty ) ) ||
							( 'disallowed' === $allowed_or_disallowed &&   in_array( $value, $fixed_qty ) )
						) {
							unset( $values[ $i ] );
						}
					}
				}
			}

			// Labels
			$label_template_singular = '';
			$label_template_plural   = '';
			if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
				$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
				$label_template_singular = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true );
				$label_template_plural   = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true );
			}
			if ( '' === $label_template_singular ) {
				$label_template_singular = get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' );
			}
			if ( '' === $label_template_plural ) {
				$label_template_plural   = get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' );
			}
			// Select options
			foreach ( $values as $value ) {
				?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $input_value ); ?>><?php
					echo str_replace( '%qty%', alg_wc_pq()->core->get_quantity_with_sep( $value ), ( $value < 2 ? $label_template_singular : $label_template_plural ) ); ?></option><?php
			}
			?>
		</select>
		<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" class="qty ajax-ready" value="<?php echo esc_attr( $input_value ); ?>">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_after', '' ) ); ?>
	</div>
	<?php
}else if( 'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), 'allowed' ) ) ){ 
	?>
	<div class="quantity">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_before', '' ) ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></label>
		<select
			id="<?php echo esc_attr( $input_id ); ?>_pq_dropdown"
			class="qty ajax-ready"
			name="<?php echo esc_attr( $input_name ); ?>_pq_dropdown"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>" >
			<?php
			// Values
			$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
			$values = $fixed_qty;
			
			if ( ! empty( $input_value ) && ! in_array( $input_value, $values ) ) {
				$values[] = $input_value;
			}
			
			asort( $values );
			// Fixed qty
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( $product && '' != ( $fixed_qty = alg_wc_pq()->core->get_product_exact_qty( $product->get_id(), $allowed_or_disallowed ) ) ) {
					$fixed_qty = alg_wc_pq()->core->process_exact_qty_option( $fixed_qty );
					foreach ( $values as $i => $value ) {
						if (
							( 'allowed'    === $allowed_or_disallowed && ! in_array( $value, $fixed_qty ) ) ||
							( 'disallowed' === $allowed_or_disallowed &&   in_array( $value, $fixed_qty ) )
						) {
							unset( $values[ $i ] );
						}
					}
				}
			}
			
			asort( $values );

			// Labels
			$label_template_singular = '';
			$label_template_plural   = '';
			if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
				$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
				$label_template_singular = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true );
				$label_template_plural   = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true );
			}
			if ( '' === $label_template_singular ) {
				$label_template_singular = get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' );
			}
			if ( '' === $label_template_plural ) {
				$label_template_plural   = get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' );
			}
			// Select options
			foreach ( $values as $value ) {
				?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $input_value ); ?>><?php
					echo str_replace( '%qty%', alg_wc_pq()->core->get_quantity_with_sep( $value ), ( $value < 2 ? $label_template_singular : $label_template_plural ) ); ?></option><?php
			}
			?>
		</select>
		<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" class="qty ajax-ready" value="<?php echo esc_attr( $input_value ); ?>">
		<?php echo do_shortcode( get_option( 'alg_wc_pq_qty_dropdown_template_after', '' ) ); ?>
	</div>
	<?php
}else { // WC default
	/* translators: %s: Quantity. */
	$label = ! empty( $args['product_name'] ) ? sprintf( __( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : __( 'Quantity', 'woocommerce' );
	?>
	<div class="quantity">
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
			size="4"
			inputmode="<?php echo esc_attr( $inputmode ); ?>" />
	</div>
	<?php
}