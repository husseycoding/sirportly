<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Department
{
    public function toOptionArray()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/departments', 'brand');
    }
}