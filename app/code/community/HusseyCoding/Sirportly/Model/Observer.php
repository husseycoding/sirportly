<?php
class HusseyCoding_Sirportly_Model_Observer
{
    private $_token;
    private $_secret;
    private $_informed = false;
    
    public function adminhtmlCoreConfigDataSaveAfter($observer)
    {
        $data = $observer->getConfigData();
        if ($data->getPath() == 'sirportly/api/token'):
            $this->_token = $data->getValue();
        elseif ($data->getPath() == 'sirportly/api/secret'):
            $this->_secret = $data->getValue();
        endif;
        
        if (isset($this->_token) && isset($this->_secret) && !$this->_informed):
            $this->_informed = true;
            if (Mage::helper('sirportly')->verifyCredentials($this->_token, $this->_secret)):
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sirportly')->__('Successfully verified Sirportly API credentials.'));
            else:
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sirportly')->__('Failed to verify Sirportly API credentials.'));
            endif;
        endif;
    }
}