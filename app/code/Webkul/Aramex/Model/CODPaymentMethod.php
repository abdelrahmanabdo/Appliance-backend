<?php
 /**
  * Webkul Software.
  *
  * @category  Webkul
  * @package   Webkul_Aramex
  * @author    Webkul
  * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\Aramex\Model;
 
/**
 * Pay In Store payment method model
 */
class CODPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'webkularamex';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

     public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        
     ) {
            $this->_checkoutSession = $checkoutSession;
            $this->_storeManager = $storeManager;
            $this->_scopeConfig  = $scopeConfig;
     }


    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        $isCod = $this->_scopeConfig->getValue('carriers/webkularamex/cod');
        $country = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountryId();
        $origionCode = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getStoreId());
        if ($country != (string)$origionCode) {
            $isCod = false;
        }

        if ($isCod) {
            return true;
        }
        return false;
    }
}
