<?php

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

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

            if (!$validator->isValidCollection($data)) {
                return $this->_fail($validator->getErrors());
            }

            $itemLoader = Mage::getModel('barcala_massstock/api2_massstock_item_loader');
            $loadedItems = $itemLoader->load($data);

            $itemMapper = Mage::getModel('barcala_massstock/api2_massstock_item_mapper');
            $mapItemQty = $itemMapper->map($data, $loadedItems);

            if (count($itemMapper->getErrors()) > 0) {
                return $this->_fail($itemMapper->getErrors());
            }

            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            $table = $resource->getTableName('cataloginventory/stock_item');
            foreach ($mapItemQty as $itemQty) {
                $connection->update(
                    $table,
                    ['qty' => $itemQty['qty']],
                    ['item_id = ?' => $itemQty['item']->getId()]
                );
            }
        } catch (Exception $e) {
            throw $e;
            $this->_errorMessage(
                Mage_Api2_Model_Resource::RESOURCE_INTERNAL_ERROR,
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }
    }

    protected function _fail($errors)
    {
        foreach ($errors as $error) {
            $this->_errorMessage($error, self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
}