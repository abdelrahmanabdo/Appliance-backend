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
namespace Webkul\Aramex\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $_session;

/**
 * @param \Magento\Framework\Event\Manager $eventManager
 * @param \Magento\Framework\ObjectManagerInterface $objectManager
 * @param Magento\Customer\Model\Session $customerSession
 * @param \Magento\Framework\Logger\Monolog $logger
 * @param SessionManager $session
 */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        SessionManager $session
    ) {
        $this->_eventManager = $eventManager;
        $this->_session = $session;
    }

    /**
     * after place order event handler
     * Distribute Shipping Price for sellers
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
            $this->_session->unsAramexCodAmount();
            $this->_session->unsAramexCodMethod();
    }
}
