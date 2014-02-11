<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Status_PaymentFailed
{
    public function toOptionArray()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/statuses', false, 'Use ticket default');
    }
}