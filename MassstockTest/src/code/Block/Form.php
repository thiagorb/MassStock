<?php

class Barcala_MassstockTest_Block_Form extends Mage_Core_Block_Template
{
    protected $_exampleRequests;

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return Mage::getUrl('massstock/test/callAjax');
    }

    /**
     * @return array
     */
    public function getExampleRequests()
    {
        if ($this->_exampleRequests) {
            return $this->_exampleRequests;
        }

        $items100 = array_values($this->_getItemsCollection()->getItems());

        $visibleItems = array_values(
            $this->_getItemsCollection()
                ->addFieldToFilter('product_id', [
                    'in' => $this->_getEnabledSimpleProductIdsQuery()
                ])
                ->getItems()
        );


        $this->_exampleRequests = [
            '1 Item by Product ID' => $this->_mapToRequestByProductId(array_slice($visibleItems, 0, 1)),
            '1 Item by Item ID' => $this->_mapToRequestByItemId(array_slice($visibleItems, 0, 1)),
            '100 Items' => $this->_mapToRequestByProductId($items100),
            count($visibleItems) . ' Visible Items' => $this->_mapToRequestByProductId($visibleItems),
            count($visibleItems) . ' Visible Items qty++' => $this->_incrementQty($this->_mapToRequestByProductId($visibleItems)),
            count($visibleItems) . ' Visible Items qty 0' => $this->_zeroQty($this->_mapToRequestByProductId($visibleItems))
        ];
        return $this->_exampleRequests;
    }

    /**
     * Increment qty for all elements in the items array
     *
     * @param array[] $items
     * @return array[]
     */
    protected function _incrementQty($items)
    {
        foreach ($items as &$item) {
            $item['qty'] += 1;
        }
        unset($item);
        return $items;
    }

    /**
     * Set qty to 0 for all elements in the items array
     *
     * @param array[] $items
     * @return array[]
     */
    protected function _zeroQty($items)
    {
        foreach ($items as &$item) {
            $item['qty'] = '0';
        }
        unset($item);
        return $items;
    }

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item[] $items
     * @return array[]
     */
    protected function _mapToRequestByItemId($items)
    {
        return array_map(
            function ($item) {
                return [
                    'item_id' => $item->getItemId(),
                    'qty' => $item->getQty()
                ];
            },
            $items
        );
    }

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item[] $items
     * @return array[]
     */
    protected function _mapToRequestByProductId($items)
    {
        return array_map(
            function ($item) {
                return [
                    'product_id' => $item->getProductId(),
                    'stock_id' => $item->getStockId(),
                    'qty' => $item->getQty()
                ];
            },
            $items
        );
    }

    /**
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item_Collection
     */
    protected function _getItemsCollection()
    {
        /** @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $items */
        $items = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $items->setPageSize(100)->setCurPage(1);

        return $items;
    }

    protected function _getEnabledSimpleProductIdsQuery()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $enabledSimpleProductsIds */
        $enabledSimpleProductsIds = Mage::getResourceModel('catalog/product_collection');
        $enabledSimpleProductsIds
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $select = $enabledSimpleProductsIds->getSelect();

        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id');

        return $select;
    }
}