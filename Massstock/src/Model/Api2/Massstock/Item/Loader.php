<?php

class Barcala_Massstock_Model_Api2_Massstock_Item_Loader
{
    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    public function __construct()
    {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Load items that match requested items
     *
     * @param array $data
     * @return Mage_CatalogInventory_Model_Stock_Item[]
     */
    public function load(array $data)
    {
        $conditions = [];

        foreach ($data as $index => $itemData) {
            if (isset($itemData['item_id'])) {
                $conditions[] = $this->_makeCondition('item_id', $itemData['item_id']);
                continue;
            }

            if (isset($itemData['stock_id'])) {
                $stockId = $itemData['stock_id'];
            } else {
                $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
            }

            $conditions[] = $this->_conjunction([
                $this->_makeCondition('stock_id', $stockId),
                $this->_makeCondition('product_id', $itemData['product_id'])
            ]);
        }

        /* @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $collection */
        $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $collection->getSelect()->where($this->_disjunction($conditions));

        $items = [];
        foreach ($collection as $item) {
            /** @var Mage_CatalogInventory_Model_Stock_Item $item */
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Make a condition expression to be used on a query
     *
     * @param string $field Name of field
     * @param mixed  $value Value
     * @return string
     */
    protected function _makeCondition($field, $value)
    {
        return sprintf('%s = %s', $field, $this->_connection->quote($value));
    }

    /**
     * Combine given conditions with an AND operator
     *
     * @param string[] $conditions Conditions
     * @return string
     */
    protected function _conjunction($conditions)
    {
        return implode(' AND ', $this->_wrap($conditions));
    }


    /**
     * Combine given conditions with an OR operator
     *
     * @param string[] $conditions Conditions
     * @return string
     */
    protected function _disjunction($conditions)
    {
        return implode(' OR ', $this->_wrap($conditions));
    }

    /**
     * Combine given conditions with an OR operator
     *
     * @param string[] $conditions Conditions
     * @return string
     */
    protected function _wrap($conditions)
    {
        return array_map(
            function ($condition) {
                return sprintf('(%s)', $condition);
            },
            $conditions
        );
    }
}
