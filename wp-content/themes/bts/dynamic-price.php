<?php

/*
|--------------------------------------------------------------------------
| Check if product has pricing rules
|--------------------------------------------------------------------------
*/
function bts_has_pricing_rules( $product ) {

    if ( ! $product ) return false;

    // Simple product
    if ( ! $product->is_type( 'variable' ) ) {
        $raw = get_post_meta( $product->get_id(), '_pricing_rules', true );
        return is_array( $raw ) && ! empty( $raw );
    }

    // Variable product – check parent first
    $parent_rules = get_post_meta( $product->get_id(), '_pricing_rules', true );
    if ( is_array( $parent_rules ) && ! empty( $parent_rules ) ) {
        return true;
    }

    // Then check variations
    foreach ( $product->get_children() as $variation_id ) {
        $raw = get_post_meta( $variation_id, '_pricing_rules', true );
        if ( is_array( $raw ) && ! empty( $raw ) ) {
            return true;
        }
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| Render container only if rules exist
|--------------------------------------------------------------------------
*/
add_action( 'woocommerce_before_add_to_cart_button', 'bts_render_bundle_pricing_ui', 20 );
function bts_render_bundle_pricing_ui() {

    global $product;

    if ( ! bts_has_pricing_rules( $product ) ) return;

    echo '<div class="bts-bundle-pricing"></div>';
}

/*
|--------------------------------------------------------------------------
| Extract pricing tiers
|--------------------------------------------------------------------------
*/
function btscodes_get_dynamic_price_tiers( $product_id, $regular_price, $parent_id = null ) {

    $raw = get_post_meta( $product_id, '_pricing_rules', true );

    if ( empty( $raw ) && $parent_id ) {
        $raw = get_post_meta( $parent_id, '_pricing_rules', true );
    }

    if ( empty( $raw ) || ! is_array( $raw ) ) {
        return [];
    }

    $tiers = [];

    foreach ( $raw as $set ) {

        if ( empty( $set['rules'] ) || ! is_array( $set['rules'] ) ) {
            continue;
        }

        foreach ( $set['rules'] as $rule ) {

            $from   = isset($rule['from']) ? (int) $rule['from'] : 0;
            $to     = isset($rule['to']) ? (int) $rule['to'] : 0;
            $type   = $rule['type'] ?? '';
            $amount = isset($rule['amount']) ? (float) $rule['amount'] : 0;

            if ( $from <= 0 ) continue;

            $final = null;

            if ( $type === 'fixed_price' ) {
                $final = $amount;
            }
            elseif ( $type === 'percentage_discount' ) {
                $final = $regular_price - ( $regular_price * ( $amount / 100 ) );
            }
            elseif ( $type === 'price_discount' ) {
                $final = $regular_price - $amount;
            }

            if ( $final === null ) continue;

            if ( $final < 0 ) $final = 0;

            $tiers[] = [
                'from'     => $from,
                'to'       => $to,
                'price'    => wc_format_decimal( $final, 2 ),
                'original' => wc_format_decimal( $regular_price, 2 )
            ];
        }
    }

    // Sort tiers ascending by quantity
    usort( $tiers, function( $a, $b ) {
        return $a['from'] - $b['from'];
    });

    return $tiers;
}

/*
|--------------------------------------------------------------------------
| Frontend Script
|--------------------------------------------------------------------------
*/
add_action( 'wp_footer', 'bts_bundle_pricing_script' );
function bts_bundle_pricing_script() {

    if ( ! is_product() ) return;

    global $product;

    if ( ! bts_has_pricing_rules( $product ) ) return;

    $data = [];

    if ( $product->is_type( 'variable' ) ) {

        foreach ( $product->get_children() as $variation_id ) {

            $variation = wc_get_product( $variation_id );
            if ( ! $variation ) continue;

            $regular_price = (float) $variation->get_regular_price();

            $tiers = btscodes_get_dynamic_price_tiers(
                $variation_id,
                $regular_price,
                $product->get_id()
            );

            if ( empty( $tiers ) ) continue;

            $data[ $variation_id ] = [
                'regular_price' => wc_format_decimal( $regular_price, 2 ),
                'tiers' => $tiers
            ];
        }

    } else {

        $regular_price = (float) $product->get_regular_price();

        $tiers = btscodes_get_dynamic_price_tiers(
            $product->get_id(),
            $regular_price
        );

        if ( ! empty( $tiers ) ) {
            $data['simple'] = [
                'regular_price' => wc_format_decimal( $regular_price, 2 ),
                'tiers' => $tiers
            ];
        }
    }

    if ( empty( $data ) ) return;
?>
<script>
const btsPricingData = <?php echo json_encode( $data ); ?>;

function renderTiers(key) {

    const container = document.querySelector('.bts-bundle-pricing');
    if (!container || !btsPricingData[key]) return;

    const data = btsPricingData[key];
    let html = '';

    const hasOneRule = data.tiers.some(t => t.from === 1);

    if (!hasOneRule) {
        html += `
            <label class="bts-tier">
                <input type="radio" name="bts_qty_tier" value="1" checked>
                <div class="bts-tier-content">
                    <span class="bts-title">1 Stück</span>
                    <span class="bts-price">€${data.regular_price}</span>
                </div>
            </label>
        `;
    }

    data.tiers.forEach(tier => {

        let label = '';

        if (tier.to === 0 || tier.to === null) {
            label = `${tier.from}+ Stück`;
        }
        else if (tier.from === tier.to) {
            label = `${tier.from} Stück`;
        }
        else {
            label = `${tier.from} to ${tier.to} Stück`;
        }            

        html += `
            <label class="bts-tier">
                <input type="radio" name="bts_qty_tier" value="${tier.from}" ${!hasOneRule && tier.from !== 1 ? '' : ''}>
                <div class="bts-tier-content">
                    <span class="bts-title">${label}</span>
                    <span>
                        <span class="bts-price">€${tier.price}</span>
                        <span class="bts-original">€${tier.original}</span>
                    </span>
                </div>
            </label>
        `;
    });

    container.innerHTML = html;
	
	document.querySelectorAll('input[name="bts_qty_tier"]').forEach(el => {
		el.addEventListener('change', function () {

			const qty = document.querySelector('input.qty');
			if (qty) qty.value = this.value;

			const priceContainer = document.querySelector('.summary .price');
			if (!priceContainer) return;

			const selectedValue = parseInt(this.value);

			if (selectedValue === 1) {
				priceContainer.innerHTML = data.regular_price;
			} else {
				const tier = data.tiers.find(t => parseInt(t.from) === selectedValue);
				if (tier) {
					priceContainer.innerHTML =
						`<span class="woocommerce-Price-amount amount">${tier.price}</span>
						 <del>${tier.original}</del>`;
				}
			}
		});
	});	
}

document.addEventListener('DOMContentLoaded', function () {

<?php if ( $product->is_type( 'variable' ) ) : ?>

    jQuery('form.variations_form').on('found_variation', function (e, variation) {
        if (btsPricingData[variation.variation_id]) {
            renderTiers(variation.variation_id);
        } else {
            document.querySelector('.bts-bundle-pricing').innerHTML = '';
        }
    });

<?php else : ?>

    renderTiers('simple');

<?php endif; ?>

});
</script>
<?php
}