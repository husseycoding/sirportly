<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Team_PaymentFailed
{
    public function toOptionArray()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/teams', false, 'Use ticket default');
    }
}