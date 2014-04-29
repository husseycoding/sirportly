<?php
class HusseyCoding_Sirportly_OrdersController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $error = true;
        if ($this->getRequest()->isPost()):
            $data = $this->getRequest()->getPost();
            if (!empty($data['key'])):
                if ($key = Mage::getStoreConfig('sirportly/general/key')):
                    if ($key == $data['key']):
                        if (!empty($data['contacts']) && is_array($data['contacts'])):
                            $error = false;
                            if ($email = $this->_getEmail($data['contacts'])):
                                if ($customer = $this->_customerExists($email)):
                                    $this->loadLayout();
                                    $this->getLayout()->getBlock('root')->setCustomer($customer);
                                    $this->renderLayout();
                                else:
                                    $this->getResponse()->setBody($this->_notFoundHtml($email));
                                endif;
                            else:
                                $this->getResponse()->setBody($this->_noEmailHtml($email));
                            endif;
                        endif;
                    endif;
                endif;
            endif;
        endif;
        
        if ($error):
            $this->getResponse()->setHttpResponseCode(500);
        endif;
    }
    
    private function _getEmail($contacts)
    {
        foreach ($contacts as $contact):
            if (strpos($contact, 'email') === 0):
                $contact = explode(':', $contact);
                $email = end($contact);
                $validator = new Zend_Validate_EmailAddress();
                if ($validator->isValid($email)):
                    return $email;
                endif;
            endif;
        endforeach;
        
        return false;
    }
    
    private function _customerExists($email)
    {
        if (Mage::getStoreConfig('customer/account_share/scope')):
            foreach (Mage::getResourceModel('core/website_collection') as $website):
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId($website->getId());
                $customer->loadByEmail($email);
                if ($customer->getId()):
                    return $customer;
                endif;
            endforeach;
        else:
            $customer = Mage::getModel('customer/customer');
            $customer->loadByEmail($email);
            if ($customer->getId()):
                return $customer;
            endif;
        endif;
        
        return false;
    }
    
    private function _notFoundHtml($email)
    {
        return '<div>No Magento customer found with email address ' .  $email . '.</div>';
    }
    
    private function _noEmailHtml()
    {
        return '<div>Please add an email address to this Sirportly contact.</div>';
    }
}