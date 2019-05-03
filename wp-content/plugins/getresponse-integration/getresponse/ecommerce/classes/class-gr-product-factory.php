<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductFactory
 * @package Getresponse\WordPress
 */
class ProductFactory {

	/**
	 * @param \WC_Product $product
	 *
	 * @return Product
	 */
	public static function buildFromSimpleProduct( $product ) {

		$description = $product->get_description();

		if (1000 < strlen($description)) {
			$description = substr($description, 0, 999);
		}

		$variant = new ProductVariant(
			$product->get_name(),
			$product->get_permalink(),
            round(wc_get_price_excluding_tax($product), 2),
			round(wc_get_price_including_tax($product), 2),
			$product->get_stock_quantity(),
			$product->get_sku(),
			$description,
			$product->get_id(),
			self::get_product_images( $product )
		);

		$variants = array( $variant );

		return new Product(
			$product->get_name(),
			$product->get_permalink(),
			$product->get_type(),
			$product->get_id(),
			$variants,
			self::build_product_categories( $product )
		);
	}

	/**
	 * @param \WC_Product|\WC_Product_Variable $product
	 *
	 * @return Product
	 */
	public static function buildFromVariableProduct( $product ) {

		$variants = array();

		foreach ( $product->get_available_variations() as $_variation ) {

			$variation = new \WC_Product_Variation( $_variation['variation_id'] );

			$description = $variation->get_description();

			if (1000 < strlen($description)) {
				$description = substr($description, 0, 999);
			}

			$variants[] = new ProductVariant(
				$variation->get_name(),
				$variation->get_permalink(),
                round(wc_get_price_excluding_tax($variation), 2),
                round(wc_get_price_including_tax($variation), 2),
				$variation->get_stock_quantity(),
				$variation->get_sku(),
				$description,
				$variation->get_id(),
                self::get_product_images( $variation )
			);
		}

		return new Product(
			$product->get_name(),
			$product->get_permalink(),
			$product->get_type(),
			$product->get_id(),
			$variants,
			self::build_product_categories( $product )
		);
	}

	/**
	 * @param \WC_Product|\WC_Product_Variable $product
	 *
	 * @return array
	 */
	private static function get_product_images($product) {

	    $imageNumber = 0;
	    $productImages = array();

        $imageUrl = new ImageUrl(wp_get_attachment_url($product->get_image_id()));

        if ($imageUrl->isValid()) {
            $productImages[] = array('position' => $imageNumber++, 'src' => $imageUrl->getUrl());
        }

        $galleryImageIds = $product->get_gallery_image_ids();

        foreach ($galleryImageIds as $imageId) {
            $imageUrl = new ImageUrl(wp_get_attachment_url($imageId));
            if ($imageUrl->isValid()) {
                $productImages[] = array('position' => $imageNumber++, 'src' => $imageUrl->getUrl());
            }
        }

        return $productImages;
	}

	/**
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	private static function build_product_categories( $product ) {

		$categories = array();

		foreach ( $product->get_category_ids() as $category_id ) {

			/** @var \WP_Term $term */
			if ( $term = get_term_by( 'id', $category_id, 'product_cat' ) ) {

				$categories[] = array(
					'name'       => $term->name,
					'url'        => null,
					'parentId'   => $term->parent,
					'externalId' => $term->term_id,
					'isDefault'  => false
				);
			}
		}

		return $categories;
	}
}
