<?php

class Barcala_Massstock_Model_Api2_Massstock_Item_Mapper
{
    protected $_connection;

    protected $_errors = [];

    public function __construct()
    {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    public function map(array $data, $loadedItems)
    {
        $mapByItemId = $this->_mapByItemId($loadedItems);
        $mapByStockAndProductId = $this->_mapByStockAndProductId($loadedItems);

        $itemReferences = [];
        $mapItemQty = [];

        foreach ($data as $index => $itemData) {
            if (isset($itemData['item_id'])) {
                $item = $this->_findLoadedItemByItemId($mapByItemId, $itemData['item_id']);
            } else {
                if (empty($itemData['stock_id'])) {
                    $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
                } else {
                    $stockId = $itemData['stock_id'];
                }

                $item = $this->_findLoadedItemByStockAndProductId($mapByStockAndProductId, $stockId, $itemData['product_id']);
            }

            if (!$item) {
                continue;
            }

            if (!isset($itemReferences[$item->getId()])) {
                $itemReferences[$item->getId()] = [];

                $mapItemQty[$item->getId()] = [
                    'qty' => $itemData['qty'],
                    'item' => $item
                ];
            }
            $itemReferences[$item->getId()][] = $index;
        }
        
        foreach ($itemReferences as $itemId => $indexes) {
            if (count($indexes) <= 1) {
                continue;
            }

            $this->_addError(
                sprintf(
                    'Items at indexes [%s] refer to the same stock item (id %s)',
                    implode(', ', $indexes), 
                    $itemId
                )
            );
        }

        return $mapItemQty;
    }

    protected function _findLoadedItemByItemId($mapByItemId, $itemId)
    {
        if (isset($mapByItemId[$itemId])) {
            return $mapByItemId[$itemId];
        }
        
        $this->_addError(sprintf('Failed to load item with id %s', $itemId));
    }

    protected function _findLoadedItemByStockAndProductId($mapByStockAndProductId, $stockId, $productId)
    {
        if (isset($mapByStockAndProductId[$stockId][$productId])) {
            return $mapByStockAndProductId[$stockId][$productId];
        }

        $this->_addError(sprintf('Failed to load item with stock id %s and product id %s', $stockId, $productId));
    }

    protected function _mapByItemId($loadedItems)
    {
        $map = [];

        foreach ($loadedItems as $item) {
            $map[$item->getId()] = $item;
        }

        return $map;
    }

    protected function _mapByStockAndProductId($loadedItems)
    {
        $map = [];

        foreach ($loadedItems as $item) {
            if (!isset($map[$item->getStockId()])) {
                $map[$item->getStockId()] = [];
            }
            $map[$item->getStockId()][$item->getProductId()] = $item;
        }

        return $map;
    }

    protected function _addError($error)
    {
        $this->_errors[] = $error;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}