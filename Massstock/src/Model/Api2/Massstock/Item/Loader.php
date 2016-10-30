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
     * @param Barcala_Massstock_Model_Api2_Massstock_Request_Item[] $requestItems Requested items
     * @return Mage_CatalogInventory_Model_Stock_Item[]
     */
    public function load($requestItems)
    {
        $conditions = [];

        foreach ($requestItems as $index => $requestItem) {
            if ($requestItem->hasItemId()) {
                $conditions[] = $this->_makeCondition('item_id', $requestItem->getItemId());
                continue;
            }

            $conditions[] = $this->_conjunction([
                $this->_makeCondition('stock_id', $requestItem->getStockId()),
                $this->_makeCondition('product_id', $requestItem->getProductId())
            ]);
        }

        /* @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $collection */
        $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $collection->getSelect()->where($this->_disjunction($conditions));

        return $collection->getItems();
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
