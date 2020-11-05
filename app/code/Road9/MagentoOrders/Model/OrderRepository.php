<?php

namespace Road9\MagentoOrders\Model;


use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Model\Order\ShippingAssignmentBuilder;
use Magento\Sales\Model\ResourceModel\Metadata;

/**
 * Class Repository
 *
 *
 * @package Road9\MagentoOrders
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var ShippingAssignmentBuilder
     */
    private $shippingAssignmentBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var OrderInterface[]
     */
    protected $registry = [];

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * Constructor
     *
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory|null $orderExtensionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory = null,
        JoinProcessorInterface $extensionAttributesJoinProcessor = null
    ) {
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->orderExtensionFactory = $orderExtensionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Sales\Api\Data\OrderExtensionFactory::class);
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor
            ?: ObjectManager::getInstance()->get(JoinProcessorInterface::class);
    }

    /**
     * Find entities by criteria
     * @param  $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function getCustomerList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria){
        $filterGroups = $searchCriteria->getFilterGroups();
        $customerFilterGroup = new FilterGroup();
        $customerFilter = new Filter();
        $customerFilter->setField('customer_id');
        $customerFilter->setValue($customerId);
        $customerFilterGroup->setFilters([
            $customerFilter
        ]);
        $filterGroups[] = $customerFilterGroup;

        $searchCriteria->setFilterGroups($filterGroups);
        $searchResult = parent::getList($searchCriteria);

        return  $searchResult;
    }

}