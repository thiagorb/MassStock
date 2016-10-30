<?php

class Barcala_Massstock_Model_Api2_Massstock_Item_Mapper
{
    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * @var string[]
     */
    protected $_errors = [];

    public function __construct()
    {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Map the requested items to the items loaded from the database
     *
     * @param Barcala_Massstock_Model_Api2_Massstock_Request_Item[] $requestItems Requested items
     * @param Mage_CatalogInventory_Model_Stock_Item[]              $loadedItems  Stock items
     */
    public function map($requestItems, $loadedItems)
    {
        $mapByItemId = $this->_mapByItemId($loadedItems);
        $mapByStockAndProductId = $this->_mapByStockAndProductId($loadedItems);

        $itemReferences = [];

        foreach ($requestItems as $index => $requestItem) {
            if ($requestItem->hasItemId()) {
                $item = $this->_findLoadedItemByItemId($mapByItemId, $requestItem->getItemId());
            } else {
                $item = $this->_findLoadedItemByStockAndProductId(
                    $mapByStockAndProductId, 
                    $requestItem->getStockId(), 
                    $requestItem->getProductId()
                );
            }

            if (!$item) {
                continue;
            }

            if (!isset($itemReferences[$item->getId()])) {
                $itemReferences[$item->getId()] = [];
                $requestItem->setItem($item);
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
    }

    /**
     * Find the stock item with the given item ID
     *
     * @param Mage_CatalogInventory_Model_Stock_Item[] $mapByItemId Map of stock itemm
     * @param integer|string                           $itemId      ItemID
     * @return Mage_CatalogInventory_Model_Stock_Item|null
     */
    protected function _findLoadedItemByItemId($mapByItemId, $itemId)
    {
        if (isset($mapByItemId[$itemId])) {
            return $mapByItemId[$itemId];
        }

        $this->_addError(sprintf('Failed to load item with id %s', $itemId));
    }

    /**
     * Find the stock item with the given stock ID and product ID
     *
     * @param Mage_CatalogInventory_Model_Stock_Item[][] $mapByStockAndProductId Map of stock items
     * @param integer|string                             $stockId                 Stock ID
     * @param integer|string                             $productId                 Product ID
     * @return Mage_CatalogInventory_Model_Stock_Item|null
     */
    protected function _findLoadedItemByStockAndProductId($mapByStockAndProductId, $stockId, $productId)
    {
        if (isset($mapByStockAndProductId[$stockId][$productId])) {
            return $mapByStockAndProductId[$stockId][$productId];
        }

        $this->_addError(sprintf('Failed to load item with stock id %s and product id %s', $stockId, $productId));
    }

    /**
     * Create a map for the stock items by item IDs
     *
     * @param Mage_CatalogInventory_Model_Stock_Item[] $loadedItems Stock items
     * @return Mage_CatalogInventory_Model_Stock_Item[]
     */
    protected function _mapByItemId($loadedItems)
    {
        $map = [];

        foreach ($loadedItems as $item) {
            $map[$item->getId()] = $item;
        }

        return $map;
    }

    /**
     * Create a map for the stock items by stock and product IDs
     *
     * @param Mage_CatalogInventory_Model_Stock_Item[] $loadedItems Stock items
     * @return Mage_CatalogInventory_Model_Stock_Item[][]
     */
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

    /**
     * Add error
     *
     * @param string $error Error message
     */
    protected function _addError($error)
    {
        $this->_errors[] = $error;
    }

    /**
     * Get errors
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}