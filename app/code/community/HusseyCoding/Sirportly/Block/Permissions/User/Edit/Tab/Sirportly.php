<?php
class HusseyCoding_Sirportly_Block_Permissions_User_Edit_Tab_Sirportly extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('user_');

        $fieldset = $form->addFieldset('sirportly_fieldset', array('legend' => Mage::helper('sirportly')->__('User Restrictions')));

        $fieldset->addField('sirportly_restrict', 'select', array(
            'name' => 'sirportly_restrict',
            'label' => Mage::helper('adminhtml')->__('User Permissions'),
            'id' => 'sirportly_restrict',
            'title' => Mage::helper('adminhtml')->__('User Permissions'),
            'values' => $this->_getRestrictionSelect()
        ));

        $fieldset->addField('sirportly_user', 'select', array(
            'name' => 'sirportly_user',
            'label' => Mage::helper('adminhtml')->__('Sirportly User'),
            'id' => 'sirportly_user',
            'title' => Mage::helper('adminhtml')->__('Sirportly User'),
            'values' => $this->_getUserSelect()
        ));

        $fieldset->addField('sirportly_view', 'select', array(
            'name' => 'sirportly_view',
            'label' => Mage::helper('adminhtml')->__('View Tickets'),
            'id' => 'sirportly_view',
            'title' => Mage::helper('adminhtml')->__('View Tickets'),
            'values' => $this->_getViewSelect()
        ));


        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    private function _getRestrictionSelect()
    {
        return array(
            array(
                'value' => 0, 'label' => 'No Permissions'
            ),
            array(
                'value' => 1, 'label' => 'Read Only'
            ),
            array(
                'value' => 2, 'label' => 'Full Permissions'
            )
        );
    }
    
    private function _getUserSelect()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/users', false, 'Any User', array('id' => array('first_name', 'last_name')));
    }
    
    private function _getViewSelect()
    {
        return array(
            array(
                'value' => 0, 'label' => 'Only Assigned to User'
            ),
            array(
                'value' => 1, 'label' => 'Assigned to Users Team(s)'
            )
        );
    }
}
