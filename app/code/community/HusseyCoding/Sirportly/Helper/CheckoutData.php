<?php
class HusseyCoding_Sirportly_Helper_CheckoutData extends Mage_Checkout_Helper_Data
{
    public function sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage')
    {
        if (!Mage::getStoreConfig('sirportly/paymentfailed/enabled')):
            return parent::sendPaymentFailedEmail($checkout, $message, $checkoutType);
        endif;
        
        if (Mage::getStoreConfig('sirportly/paymentfailed/email')):
            parent::sendPaymentFailedEmail($checkout, $message, $checkoutType);
        endif;
        
        if (!Mage::helper('sirportly')->createTicket($this->_compileTicketData($checkout, $message, $checkoutType), 'paymentfailed')):
            return parent::sendPaymentFailedEmail($checkout, $message, $checkoutType);
        endif;
        
        return $this;
    }
    
    private function _compileTicketData($checkout, $message, $checkoutType)
    {
        $name = $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname();
        $email = $checkout->getCustomerEmail();
        $subject = Mage::getStoreConfig('sirportly/paymentfailed/subject');
        $subject = !empty($subject) ? $subject : 'Problem Processing Your Payment';
        
        $html = Mage::getModel('core/email_template');
        $template = Mage::getStoreConfig('checkout/payment_failed/template', $checkout->getStoreId());
        
        $html->setDesignConfig(array('area' => 'frontend', 'store' => $checkout->getStoreId()));
        
        if (is_numeric($template)):
            $html->load($template);
        else:
            $code = Mage::getStoreConfig('general/locale/code', $checkout->getStoreId());
            $html->loadDefault($template, $code);
        endif;
        
        $sendhtml = $this->_populateParams($html, $checkout, $message, $checkoutType);
        $sendmessage = $this->_createNonHtml($sendhtml);
        
        return array('name' => $name, 'email' => $email, 'subject' => $subject, 'comment' => $sendmessage, 'html_body' => $sendhtml, 'html_safe' => '1');
    }
    
    private function _populateParams($html, $checkout, $message, $checkoutType)
    {
        $return = trim($html->getTemplateText());
        
        preg_match_all('/{{.*}}/sU', $return, $matches);
        if (!empty($matches[0])):
            $shippingMethod = '';
            if ($shippingInfo = $checkout->getShippingAddress()->getShippingMethod()):
                $data = explode('_', $shippingInfo);
                $shippingMethod = $data[0];
            endif;
            
            $paymentMethod = '';
            if ($paymentInfo = $checkout->getPayment()):
                $paymentMethod = $paymentInfo->getMethod();
            endif;
            
            $items = '';
            foreach ($checkout->getAllVisibleItems() as $_item):
                $items .= $_item->getProduct()->getName() . '  x '. $_item->getQty() . '  '
                    . $checkout->getStoreCurrencyCode() . ' '
                    . $_item->getProduct()->getFinalPrice($_item->getQty()) . "\n";
            endforeach;
            
            $total = $checkout->getStoreCurrencyCode() . ' ' . $checkout->getGrandTotal();
            
            $replace = array(
                'reason' => $message,
                'checkoutType' => $checkoutType,
                'dateAndTime' => Mage::app()->getLocale()->date(),
                'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
                'customerEmail' => $checkout->getCustomerEmail(),
                'billingAddress' => $checkout->getBillingAddress(),
                'shippingAddress' => $checkout->getShippingAddress(),
                'shippingMethod' => Mage::getStoreConfig('carriers/' . $shippingMethod . '/title'),
                'paymentMethod' => Mage::getStoreConfig('payment/' . $paymentMethod . '/title'),
                'items' => nl2br($items),
                'total' => $total
            );
            
            $return = $html->getProcessedTemplate($replace);
        endif;
        
        return trim($return);
    }
    
    private function _createNonHtml($html)
    {
        $html = strip_tags($html);
        $html = html_entity_decode($html);
        $html = trim($html);
        $html = preg_replace('/^[\t ]+/m', '', $html);
        $html = preg_replace('/(\n\n)\n+/m', '$1', $html);
        
        return $html;
    }
}