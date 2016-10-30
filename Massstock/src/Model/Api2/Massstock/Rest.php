<?php

abstract class Barcala_Massstock_Model_Api2_Massstock_Rest
extends Barcala_Massstock_Model_Api2_Massstock
{
    /**
     * Update specified stock items
     *
     * @param array $data
     */
    public function _multiUpdate(array $data)
    {
        try {
            /** @var Barcala_Massstock_Model_Api2_Massstock_Validator_Collection $validator */
            $validator = Mage::getModel('barcala_massstock/api2_massstock_validator_collection', array(
                'resource' => $this
            ));

            if (!$validator->isValidCollection($data)) {
                return $this->_fail($validator->getErrors());
            }
            
            $requestItems = $this->_transformRequest($data);

            /** @var Barcala_Massstock_Model_Api2_Massstock_Item_Loader $itemLoader */
            $itemLoader = Mage::getModel('barcala_massstock/api2_massstock_item_loader');
            $loadedItems = $itemLoader->load($requestItems);

            /** @var Barcala_Massstock_Model_Api2_Massstock_Item_Mapper $itemMapper */
            $itemMapper = Mage::getModel('barcala_massstock/api2_massstock_item_mapper');
            $itemMapper->map($requestItems, $loadedItems);

            if (count($itemMapper->getErrors()) > 0) {
                return $this->_fail($itemMapper->getErrors());
            }

            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            $table = $resource->getTableName('cataloginventory/stock_item');
            foreach ($requestItems as $requestItem) {
                $connection->update(
                    $table,
                    ['qty' => $requestItem->getQty()],
                    ['item_id = ?' => $requestItem->getItem()->getId()]
                );

                $this->_successMessage(
                    sprintf(
                        'Updated quantity of stock item with ID %s to %s', 
                        $requestItem->getItem()->getId(),
                        $requestItem->getQty()
                    ),
                    self::RESOURCE_UPDATED_SUCCESSFUL
                );
            }

            /** @var Mage_CatalogInventory_Model_Resource_Stock $stockResource */
            $stockResource = Mage::getResourceSingleton('cataloginventory/stock');
            $stockResource->updateSetOutOfStock();
            $stockResource->updateSetInStock();
            $stockResource->updateLowStockDate();
            
            /** @var Mage_Index_Model_Process $process */
            $process = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
            $process->reindexAll();
        } catch (Exception $e) {
            throw $e;
            $this->_errorMessage(
                Mage_Api2_Model_Resource::RESOURCE_INTERNAL_ERROR,
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }
    }

    /**
     * Fail with given errors
     *
     * @param string[] $errors
     */
    protected function _fail($errors)
    {
        foreach ($errors as $error) {
            $this->_errorMessage($error, self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
    
    /**
     * Transform array of requested items
     * 
     * @param array $data
     * @return Barcala_Massstock_Model_Api2_Massstock_Request_Item[]
     */
    protected function _transformRequest($data)
    {
        $items = [];
        
        foreach ($data as $itemData) {
            $items[] = Mage::getModel('barcala_massstock/api2_massstock_request_item', $itemData);
        }
        
        return $items;
    }
}