<?php

class Barcala_Massstock_Model_Api2_Massstock_Request_Item
{
    /**
     * @var Mage_CatalogInventory_Model_Stock_Item|null
     */
    protected $_item = null;
    
    /**
     * @var number|string|null
     */
    protected $_itemId = null;

    /**
     * @var number|string|null
     */
    protected $_productId = null;

    /**
     * @var number|string|null
     */
    protected $_stockId = null;
    
    /**
     * @var float|string|null
     */
    protected $_qty = null;
    
    /**
     * @param array $data
     */
    public function __construct($data)
    {
        if (isset($data['item_id'])) {
            $this->_itemId = $data['item_id'];
        } else {
            $this->_productId = $data['product_id'];
            if (isset($data['stock_id'])) {
                $this->_stockId = $data['stock_id'];
            } else {
                $this->_stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
            }
        }
        $this->_qty = $data['qty'];
    }
    
    /**
     * @return boolean
     */
    public function hasItemId()
    {
        return $this->_itemId !== null;
    }
    
    /**
     * @return number|string|null
     */
    public function getItemId()
    {
        return $this->_itemId;
    }

    /**
     * @return number|string|null
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @return number|string|null
     */
    public function getStockId()
    {
        return $this->_stockId;
    }
    
    /**
     * @return number|string|null
     */
    public function getQty()
    {
        return $this->_qty;
    }
    
    /**
     * @return Mage_CatalogInventory_Model_Stock_Item|null
     */
    public function getItem()
    {
        return $this->_item;
    }
    
    /**
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     */
    public function setItem($item)
    {
        $this->_item = $item;
    }
}