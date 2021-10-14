<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
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
    public $id_customer = null;

    /**
     * @var string
     */
    public $date = null;
    
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'recommend_similar_products_clicks',
        'primary' => 'id_recommend_similar_products_clicks',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'required' => true),
            'date' => array('type' => self::TYPE_DATE, 'required' => true),
        ),
    );

    /**
     * @param string $dateFrom
     *
     * @return array
     */
    public static function getClicks($dateFrom = null)
    {
        $query = (new DbQuery())
            ->select('*')
            ->from('recommend_similar_products_clicks', 'c');

        if ($dateFrom) {
            $query->where('c.date >= ' . $dateFrom);
        }

        if (is_array($result = Db::getInstance()->executeS($query))) {
            return $result;
        }

        return array();
    }
}
