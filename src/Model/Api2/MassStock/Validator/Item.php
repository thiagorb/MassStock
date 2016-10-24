<?php

class Barcala_Massstock_Model_Api2_Massstock_Validator_Item extends Mage_Api2_Model_Resource_Validator_Fields
{
    public function isValidData(array $data)
    {
        if (isset($data['item_id'])) {
            if (!is_numeric($data['item_id'])) {
                $this->_addError('Invalid value for "item_id"');
            }
        } else {
            if (!isset($data['product_id'])) {
                $this->_addError('Missing "item_id" or "product_id"');
            }
        }

        if (isset($data['product_id']) && !is_numeric($data['product_id'])) {
            $this->_addError('Invalid value for "product_id"');
        }

        if (!isset($data['qty']) || !is_numeric($data['qty'])) {
            $this->_addError('Invalid value for "qty"');
        }

        parent::isValidData($data);

        return count($this->getErrors()) == 0;
    }
}
