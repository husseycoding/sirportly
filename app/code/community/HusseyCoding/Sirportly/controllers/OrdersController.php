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
                                $this->loadLayout();
                                $this->getLayout()->getBlock('root')->setEmail($email);
                                $this->renderLayout();
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
    
    private function _noEmailHtml()
    {
        return '<div>Please add an email address to this Sirportly contact.</div>';
    }
}