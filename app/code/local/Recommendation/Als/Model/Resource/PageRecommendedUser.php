<?php
class Recommendation_Als_Model_Resource_PageRecommendedUser extends Mage_Core_Model_Mysql4_Abstract{
    
    public function _construct(){
        $this->_init('recommendation_als/pagerecomendeduser','id');
    }
}