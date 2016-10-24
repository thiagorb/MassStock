<?php

class Barcala_Massstock_Model_Api2_Massstock_Validator_Collection extends Mage_Api2_Model_Resource_Validator_Fields
{
    const MAXIMUM_AMOUNT_ITEMS = 100;

    public function isValidData(array $data)
    {
        if (count($data) > self::MAXIMUM_AMOUNT_ITEMS) {
            $this->_addError('Maximum amount of items exceeded');
        }

        foreach ($data as $index => $itemData) {
            /* @var $validator Barcala_Massstock_Model_Api2_Massstock_Validator_Item */
            $validator = Mage::getModel('barcala_massstock/api2_massstock_validator_item', [
                'resource' => $this->_resource
            ]);

            $validator->isValidData($itemData);

            $itemErrors = array_map(
                function ($itemError) use ($index) {
                    return "Item at index $index: $itemError";
                },
                $validator->getErrors()
            );

            $this->_addErrors($itemErrors);
        }

        return count($this->getErrors()) == 0;
    }
}
