<?php
  
namespace Road9\Notifications\Model\ResourceModel;
  
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
  
class Notification extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('user_notifications', 'id');
    }
}