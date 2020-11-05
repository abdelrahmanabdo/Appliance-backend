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

use Magento\Checkout\Model\ConfigProviderInterface;

class CODConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param ConfigFactory     $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer   $currentCustomer
     * @param PaypalHelper      $paypalHelper
     * @param PaymentHelper     $paymentHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig  = $scopeConfig;
    }

    /**
     * set data in window.checkout.config for checkout page.
     *
     * @return array $options
     */
    public function getConfig()
    {
        $options = [
            'webkularamex' => 0,
        ];
        $isCod = $this->_scopeConfig->getValue('carriers/webkularamex/cod');
        if ($isCod) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->get('Magento\Directory\Model\Currency');
            $price = $this->_storeManager->getStore()->getBaseCurrency()->convert(
                $this->_scopeConfig->getValue('carriers/webkularamex/cod_value'),
                $this->_storeManager->getStore()->getCurrentCurrency()
            );
            $options = [
                'webkularamex' => 1,
                'instructions' => [
                    'webkularamex' => strip_tags($this->_scopeConfig->getValue('carriers/webkularamex/cod_instructions'))
                ],
                'aramexCodCharge' => $currency->format(
                    $price,
                    ['display'=>\Zend_Currency::NO_SYMBOL],
                    false
                )
            ];
        }
        return $options;
    }
}
