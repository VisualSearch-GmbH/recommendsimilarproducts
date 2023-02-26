<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 *
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

namespace RecommendSimilarProducts\PrestaShop;

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray as LazyArray;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;
use Symfony\Component\Translation\TranslatorInterface;

class ProductLazyArray extends LazyArray
{
    /**
     * @var \Language
     */
    private $language;

    /**
     * @var \Link
     */
    private $link;

    /**
     * @var ProductColorsRetriever
     */
    private $productColorsRetriever;

    public function __construct(
        ProductPresentationSettings $settings,
        array $product,
        \Language $language,
        \Link $link,
        TranslatorInterface $translator
    ) {
        $this->language = $language;
        $this->link = $link;
        $this->productColorsRetriever = new ProductColorsRetriever();

        parent::__construct(
            $settings,
            $product,
            $this->language,
            new ImageRetriever($link),
            $this->link,
            new PriceFormatter(),
            $this->productColorsRetriever,
            $translator
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param array $product
     * @param \Language $language
     * @param bool $canonical
     *
     * @return string
     */
    private function getProductURL(
        array $product,
        \Language $language,
        $canonical = false
    ) {
        $linkRewrite = isset($product['link_rewrite']) ? $product['link_rewrite'] : null;
        $category = isset($product['category']) ? $product['category'] : null;
        $ean13 = isset($product['ean13']) ? $product['ean13'] : null;

        $extraParams = [
            'rsp' => 1,
            'id_source_product' => (int) \Tools::getValue('id_product'),
            'id_target_attribute' => (int) $product['id_product_attribute'],
        ];

        return $this->link->getProductLink(
            $product['id_product'],
            $linkRewrite,
            $category,
            $ean13,
            $language->id,
            null,
            !$canonical && $product['id_product_attribute'] > 0 ? $product['id_product_attribute'] : null,
            false,
            false,
            true,
            $extraParams
        );
    }

    /**
     * @arrayAccess
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getProductURL($this->product, $this->language);
    }

    /**
     * @arrayAccess
     *
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->getProductURL($this->product, $this->language, true);
    }

    /**
     * @arrayAccess
     *
     * @return array
     */
    public function getMainVariants()
    {
        $colors = $this->productColorsRetriever->getColoredVariants($this->product['id_product']);

        if (!is_array($colors)) {
            return [];
        }

        $lazyArray = $this;

        return array_map(function (array $color) use ($lazyArray) {
            $color['add_to_cart_url'] = $lazyArray->link->getAddToCartURL(
                $color['id_product'],
                $color['id_product_attribute']
            );
            $color['url'] = $lazyArray->getProductURL($color, $lazyArray->language);
            $color['type'] = 'color';
            $color['html_color_code'] = $color['color'];
            unset($color['color']);

            return $color;
        }, $colors);
    }
}
