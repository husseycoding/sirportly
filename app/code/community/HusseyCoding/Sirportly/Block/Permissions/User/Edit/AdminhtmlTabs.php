<?php
class HusseyCoding_Sirportly_Block_Permissions_User_Edit_AdminhtmlTabs extends Mage_Adminhtml_Block_Permissions_User_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        $this->addTabAfter('sirportly_section', array(
            'label' => Mage::helper('adminhtml')->__('Sirportly'),
            'title' => Mage::helper('adminhtml')->__('Sirportly'),
            'content' => $this->getLayout()->createBlock('sirportly/permissions_user_edit_tab_sirportly')->toHtml()
        ), 'roles_section');
        
        parent::_beforeToHtml();
    }
}