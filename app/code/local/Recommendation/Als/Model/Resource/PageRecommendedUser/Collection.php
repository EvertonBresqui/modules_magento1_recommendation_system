<?php

class Recommendation_Als_Model_Resource_PageRecommendedUser_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	
	public function _construct(){
		$this->_init('recommendation_als/pagerecomendeduser');
	}
}