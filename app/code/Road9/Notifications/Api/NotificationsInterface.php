<?php 


namespace Road9\Notifications\Api;



interface NotificationsInterface {
     /**
     * 
     *
     * @param  int $customerId
     * @param  int $token
     * @return string
     */
    public function addDeviceToken($customerId, $token);

}