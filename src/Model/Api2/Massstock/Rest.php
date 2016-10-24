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
            /* @var $validator Barcala_Massstock_Model_Api2_Massstock_Validator_Collection */
            $validator = Mage::getModel('barcala_massstock/api2_massstock_validator_collection', array(
                'resource' => $this
            ));

            if (!$validator->isValidData($data)) {
                foreach ($validator->getErrors() as $error) {
                    $this->_errorMessage($error, self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
                }
                return;
            }


            $queryConditions = [];

            foreach ($data as $index => $itemData) {
                if (!empty($itemData['item_id'])) {
                    $queryConditions[] = ['item_id' => ['eq' => $itemData['item_id']]];
                    continue;
                }

                if (empty($itemData['stock_id'])) {
                    $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
                } else {
                    $stockId = $itemData['stock_id'];
                }

                $queryConditions[] = [
                    'stock_id' => ['eq' => $stockId],
                    'product_id' => ['eq' => $itemData['product_id']]
                ];
            }

            $productIds = array_map(function ($itemData) {
                return $itemData['product_id'];
            }, $data);
            /* @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $items */
            $items = Mage::getResourceModel('cataloginventory/stock_item_collection');

            $items->addFieldToFilter([
                'product_id' => 123,
                'stock_id' => 1
            ], null);

            $loadedProductIds = [];
            foreach ($items as $item) {
                $loadedProductIds[] = $item->getProductId();
            }

            $missingIds = array_diff($productIds, $loadedProductIds);
        } catch (Exception $e) {
            $this->_errorMessage(
                Mage_Api2_Model_Resource::RESOURCE_INTERNAL_ERROR,
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }
    }
}