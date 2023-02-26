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
class RecommendSimilarProductsClick extends ObjectModel
{
    /**
     * @var int
     */
    public $id_product = null;

    /**
     * @var int
     */
    public $id_product_attribute = null;

    /**
     * @var int
     */
    public $id_source_product = null;

    /**
     * @var int
     */
    public $id_customer = null;

    /**
     * @var string
     */
    public $remote_ip_address = null;

    /**
     * @var string
     */
    public $date = null;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'recommend_similar_products_clicks',
        'primary' => 'id_recommend_similar_products_clicks',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'required' => true],
            'id_source_product' => ['type' => self::TYPE_INT, 'required' => true],
            'id_customer' => ['type' => self::TYPE_INT, 'required' => true],
            'remote_ip_address' => ['type' => self::TYPE_STRING, 'required' => true],
            'date' => ['type' => self::TYPE_DATE, 'required' => true],
        ],
    ];

    /**
     * @param string $dateFrom
     * @param bool $forActiveProductsOnly
     *
     * @return array
     */
    public static function getClicks($dateFrom = null, $forActiveProductsOnly = false)
    {
        $query = (new DbQuery())
            ->select('c.*')
            ->from('recommend_similar_products_clicks', 'c')
            ->innerJoin('product_shop', 'ps', 'ps.id_product = c.id_product AND ps.id_shop = ' .
                Context::getContext()->shop->id);

        if ($dateFrom) {
            $query->where('c.date >= \'' . $dateFrom . '\'');
        }

        if ($forActiveProductsOnly) {
            $query->where('ps.active = 1');
        }

        if (is_array($result = Db::getInstance()->executeS($query))) {
            return $result;
        }

        return [];
    }
}
