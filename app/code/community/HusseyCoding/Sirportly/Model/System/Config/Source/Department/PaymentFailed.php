<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Department_PaymentFailed
{
    public function toOptionArray()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/departments', 'brand', 'Use ticket default');
    }
}