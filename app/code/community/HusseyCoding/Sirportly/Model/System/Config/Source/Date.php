<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Date
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('sirportly')->__('Match Sirportly')),
            array('value' => 1, 'label' => Mage::helper('sirportly')->__('Custom'))
        );
    }
}