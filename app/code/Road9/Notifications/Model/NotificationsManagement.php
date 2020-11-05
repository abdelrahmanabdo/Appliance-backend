<?php

namespace Road9\Notifications\Model;
use Magento\Framework\Model\AbstractModel;
use Road9\Notifications\Api\NotificationsInterface;

class NotificationsManagement  implements NotificationsInterface  {

    protected function _construct()
    {
        // $this->_init('Road9\Notifications\Model\ResourceModel\Notification');
    }

     /**
     * {@inheritdoc}
     */
    public function addDeviceToken ($customerId , $token)  {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource =     $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('user_notifications');
        $sql = "Insert Into " . $tableName .  "( user_id , device_id ) Values ( {$customerId} , {$token} )";     
         if ($connection->query($sql)){
             return json_encode(array("status"=>true));
         } else {
            return json_encode(array("status"=>true));

         }
   }
}