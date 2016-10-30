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
        
        /** @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $items */
        $items = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $items->setPageSize(100)->setCurPage(1);
        
        $serializableItems = [];
        foreach ($items as $item) {
            $serializableItems[] = [
                'item_id' => $item->getId(),
                'qty' => $item->getQty()
            ];
        }
        
        $this->_exampleRequests = [
            '1 Item by Product ID' => [
                [
                    'product_id' => $item->getProductId(),
                    'stock_id' => $item->getStockId(),
                    'qty' => $item->getQty()
                ]
            ],
            '1 Item by Item ID' => [
                [
                    'item_id' => $item->getId(),
                    'qty' => $item->getQty()
                ]
            ],
            '100 Items' => $serializableItems
        ];
        return $this->_exampleRequests;
    }
}