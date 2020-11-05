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
namespace Webkul\Aramex\Controller\Shipping;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

class Update extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory    $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        SessionManager $session
    ) {;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->session = $session;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParam('isAramexMethod');
        if ($data == 'webkularamex') {
            $this->session->setAramexCodMethod(1);
            $response = [
                'errors' => false,
                'message' => __('Aramex COD Applied.')
            ];
        } else {
            $this->session->setAramexCodMethod(0);
            $response = [
                'errors' => false,
                'message' => __('Aramex COD Removed.')
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
