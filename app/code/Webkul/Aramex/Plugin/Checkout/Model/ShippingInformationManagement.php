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
namespace Webkul\Aramex\Plugin\Checkout\Model;

use Magento\Framework\Session\SessionManager;

class ShippingInformationManagement
{
    protected $quoteRepository;
    /**
     * @var SessionManager
     */
    protected $_coreSession;

     /**
      * @var \Magento\Framework\App\Config\ScopeConfigInterface
      */
    protected $scopeConfig;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SessionManager $coreSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->_coreSession = $coreSession;
        $this->scopeConfig  = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $codCharge = 0;
        if ($this->scopeConfig->getValue('carriers/webkularamex/cod') == 1) {
            $codCharge = $this->scopeConfig->getValue('carriers/webkularamex/cod_value');
        }
        $this->_coreSession->setAramexCodAmount($codCharge);
    }
}
