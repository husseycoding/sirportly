<?php
class HusseyCoding_Sirportly_Block_DataFrame extends Mage_Core_Block_Template
{
    public function getDataFrameUrl()
    {
        return Mage::getUrl('sirportly/orders');
    }
}