<?php
  
namespace Road9\Notifications\Model\ResourceModel\Notification;
  
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
  
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Road9\Notifications\Model\Notification',
            'Road9\Notifications\Model\ResourceModel\Notification'
        );
    }
}