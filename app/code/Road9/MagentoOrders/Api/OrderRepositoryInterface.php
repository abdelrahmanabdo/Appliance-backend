<?php

namespace Road9\MagentoOrders\Api;

/**
 * Interface OrderRepositoryInterface
 *
 * @api
 * @package Road9\MagentoOrders\Api
 */
interface OrderRepositoryInterface
{

    /**
     * @param $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderItemSearchResultInterface Order item search result interface.
     */
    public function getCustomerList($customerId, ProductSearchCriteriaInterface $searchCriteria = null);
}
