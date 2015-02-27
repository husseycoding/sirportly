<?php
class HusseyCoding_Sirportly_Model_System_Config_Source_Links
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('sirportly')->__('Admin')),
            array('value' => 1, 'label' => Mage::helper('sirportly')->__('Frontend'))
        );
    }
}