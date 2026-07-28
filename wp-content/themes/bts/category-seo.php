<?php
/**
 * Product Category SEO content blocks.
 *
 * Adds two rich-text (WYSIWYG) fields to the WooCommerce product category
 * (`product_cat`) taxonomy and renders them on the category archive:
 *
 *   - Top block    : shown above the product grid. Only the first ~5 lines are
 *                    visible; the rest is revealed with a "read more" fade.
 *   - Bottom block : shown in full below the products / pagination.
 *
 * Content is stored as term meta so it is per-category and (with WPML) per
 * language automatically.
 *
 * @package bts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BTS_CAT_SEO_TOP' ) ) {
	define( 'BTS_CAT_SEO_TOP', 'bts_category_seo_top' );
}
if ( ! defined( 'BTS_CAT_SEO_BOTTOM' ) ) {
	define( 'BTS_CAT_SEO_BOTTOM', 'bts_category_seo_bottom' );
}

/**
 * -------------------------------------------------------------------------
 *  Admin: fields on the "Add category" screen (plain textareas — TinyMCE
 *  does not initialise reliably inside the inline add-term form).
 * -------------------------------------------------------------------------
 */
add_action( 'product_cat_add_form_fields', 'bts_cat_seo_add_fields' );
function bts_cat_seo_add_fields() {
	?>
	<div class="form-field">
		<label for="<?php echo esc_attr( BTS_CAT_SEO_TOP ); ?>"><?php esc_html_e( 'SEO content — Top (above products)', 'bts' ); ?></label>
		<textarea name="<?php echo esc_attr( BTS_CAT_SEO_TOP ); ?>" id="<?php echo esc_attr( BTS_CAT_SEO_TOP ); ?>" rows="8" cols="50"></textarea>
		<p><?php esc_html_e( 'Shown above the product grid. Only the first ~5 lines are visible; the rest expands via "read more". HTML is allowed.', 'bts' ); ?></p>
	</div>
	<div class="form-field">
		<label for="<?php echo esc_attr( BTS_CAT_SEO_BOTTOM ); ?>"><?php esc_html_e( 'SEO content — Bottom (below products)', 'bts' ); ?></label>
		<textarea name="<?php echo esc_attr( BTS_CAT_SEO_BOTTOM ); ?>" id="<?php echo esc_attr( BTS_CAT_SEO_BOTTOM ); ?>" rows="8" cols="50"></textarea>
		<p><?php esc_html_e( 'Shown below the product grid. HTML is allowed.', 'bts' ); ?></p>
	</div>
	<?php
}

/**
 * -------------------------------------------------------------------------
 *  Admin: fields on the "Edit category" screen (full WYSIWYG editors).
 * -------------------------------------------------------------------------
 */
add_action( 'product_cat_edit_form_fields', 'bts_cat_seo_edit_fields', 20, 1 );
function bts_cat_seo_edit_fields( $term ) {
	$top      = get_term_meta( $term->term_id, BTS_CAT_SEO_TOP, true );
	$bottom   = get_term_meta( $term->term_id, BTS_CAT_SEO_BOTTOM, true );
	$settings = array(
		'textarea_rows' => 10,
		'media_buttons' => true,
		'tinymce'       => true,
		'quicktags'     => true,
	);
	?>
	<tr class="form-field bts-cat-seo-row">
		<th scope="row"><label for="btsseotop"><?php esc_html_e( 'SEO content — Top (above products)', 'bts' ); ?></label></th>
		<td>
			<?php wp_editor( $top, 'btsseotop', array_merge( $settings, array( 'textarea_name' => BTS_CAT_SEO_TOP ) ) ); ?>
			<p class="description"><?php esc_html_e( 'Displayed above the product grid. Only the first ~5 lines show; the rest is revealed with a "read more" link.', 'bts' ); ?></p>
		</td>
	</tr>
	<tr class="form-field bts-cat-seo-row">
		<th scope="row"><label for="btsseobottom"><?php esc_html_e( 'SEO content — Bottom (below products)', 'bts' ); ?></label></th>
		<td>
			<?php wp_editor( $bottom, 'btsseobottom', array_merge( $settings, array( 'textarea_name' => BTS_CAT_SEO_BOTTOM ) ) ); ?>
			<p class="description"><?php esc_html_e( 'Displayed below the product grid, after the pagination.', 'bts' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * -------------------------------------------------------------------------
 *  Save handlers.
 * -------------------------------------------------------------------------
 */
add_action( 'created_product_cat', 'bts_cat_seo_save' );
add_action( 'edited_product_cat', 'bts_cat_seo_save' );
function bts_cat_seo_save( $term_id ) {
	foreach ( array( BTS_CAT_SEO_TOP, BTS_CAT_SEO_BOTTOM ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$value = wp_kses_post( wp_unslash( $_POST[ $key ] ) );

		if ( '' === trim( wp_strip_all_tags( $value ) ) && '' === trim( $value ) ) {
			delete_term_meta( $term_id, $key );
		} else {
			update_term_meta( $term_id, $key, $value );
		}
	}
}

/**
 * -------------------------------------------------------------------------
 *  Front-end rendering.
 * -------------------------------------------------------------------------
 */

/**
 * Return the SEO content for the currently queried product category, or ''.
 */
function bts_cat_seo_get( $key ) {
	if ( ! is_product_category() ) {
		return '';
	}
	$term = get_queried_object();
	if ( ! $term || empty( $term->term_id ) ) {
		return '';
	}
	$content = get_term_meta( $term->term_id, $key, true );

	return is_string( $content ) ? $content : '';
}

/**
 * Language-aware UI labels (German site default, English otherwise).
 * Keeps the front-end strings correct on the WPML /en/ pages without
 * requiring String Translation entries.
 */
function bts_cat_seo_label( $key ) {
	$is_de  = ( 0 === strpos( get_locale(), 'de' ) );
	$labels = array(
		'more'          => $is_de ? 'Mehr lesen' : 'Read more',
		'less'          => $is_de ? 'Weniger anzeigen' : 'Show less',
		'subcategories' => $is_de ? 'Unterkategorien' : 'Subcategories',
	);

	return isset( $labels[ $key ] ) ? $labels[ $key ] : '';
}

/**
 * Top block — above the product grid, clamped to ~5 lines with a fade.
 * Hooked into the products header, after the (hidden) default description.
 */
add_action( 'woocommerce_archive_description', 'bts_cat_seo_render_top', 12 );
function bts_cat_seo_render_top() {
	$content = bts_cat_seo_get( BTS_CAT_SEO_TOP );
	if ( '' === trim( $content ) ) {
		return;
	}
	?>
	<div class="bts-cat-seo bts-cat-seo--top is-collapsed">
		<div class="bts-cat-seo__body"><?php echo do_shortcode( wpautop( $content ) ); ?></div>
		<button type="button" class="bts-cat-seo__toggle" aria-expanded="false">
			<span class="bts-cat-seo__toggle-more"><?php echo esc_html( bts_cat_seo_label( 'more' ) ); ?></span>
			<span class="bts-cat-seo__toggle-less"><?php echo esc_html( bts_cat_seo_label( 'less' ) ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * Bottom block — full content below the products and pagination.
 * Priority 5 keeps it inside the main content wrapper (Astra closes the
 * wrapper on woocommerce_after_main_content at priority 10).
 */
add_action( 'woocommerce_after_main_content', 'bts_cat_seo_render_bottom', 5 );
function bts_cat_seo_render_bottom() {
	$content = bts_cat_seo_get( BTS_CAT_SEO_BOTTOM );
	if ( '' === trim( $content ) ) {
		return;
	}
	?>
	<div class="bts-cat-seo bts-cat-seo--bottom">
		<div class="bts-cat-seo__body"><?php echo do_shortcode( wpautop( $content ) ); ?></div>
	</div>
	<?php
}

/**
 * Subcategory links — rendered just before the product grid on category
 * archives that have child categories. Priority 40 places it after the
 * result count (20) and ordering (30), immediately before the loop.
 */
add_action( 'woocommerce_before_shop_loop', 'bts_cat_subcategory_links', 40 );
function bts_cat_subcategory_links() {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	if ( ! $term || empty( $term->term_id ) ) {
		return;
	}

	$children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term->term_id,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $children ) || empty( $children ) ) {
		return;
	}
	?>
	<nav class="bts-subcategories" aria-label="<?php echo esc_attr( bts_cat_seo_label( 'subcategories' ) ); ?>">
		<span class="bts-subcategories__title"><?php echo esc_html( bts_cat_seo_label( 'subcategories' ) ); ?></span>
		<ul class="bts-subcategories__list">
			<?php foreach ( $children as $child ) : ?>
				<li class="bts-subcategories__item">
					<a href="<?php echo esc_url( get_term_link( $child ) ); ?>">
						<span class="bts-subcategories__name"><?php echo esc_html( $child->name ); ?></span>
						<span class="bts-subcategories__count"><?php echo (int) $child->count; ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}
