<?php

class Barcala_Massstock_Model_Api2_Massstock_Item_Loader
{
    protected $_connection;

    public function __construct()
    {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    public function load(array $data)
    {
        $conditions = [];

        foreach ($data as $index => $itemData) {
            if (!empty($itemData['item_id'])) {
                $conditions[] = $this->_makeCondition('item_id', $itemData['item_id']);
                continue;
            }

            if (empty($itemData['stock_id'])) {
                $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
            } else {
                $stockId = $itemData['stock_id'];
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
            $items[] = $item;
        }

        return $items;
    }

    protected function _makeCondition($field, $value)
    {
        return sprintf('%s = %s', $field, $this->_connection->quote($value));
    }

    protected function _conjunction($conditions)
    {
        return implode(' AND ', $this->_wrap($conditions));
    }

    protected function _disjunction($conditions)
    {
        return implode(' OR ', $this->_wrap($conditions));
    }

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
