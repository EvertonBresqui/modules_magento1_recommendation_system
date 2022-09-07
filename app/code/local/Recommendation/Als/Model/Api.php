<?php

class Recommendation_Als_Model_Api extends Recommendation_System_Model_Core_ApiAstract {

    private $_helperSystem;

    public function init(){
        $this->_helperSystem = Mage::helper('recommendation_system');
        $this->setDomain($this->_helperSystem->getSettingsExport('host'));
        // Faz a autenticação na api
        $this->_connectedApi = $this->auth();
    }

    public function getRecommendations($customerIds)
    {
        $recommendations = array();

        $path = 'sysrecommendation';
        $body = array(
            'sale_group' => (int) $this->_helperSystem->getSettingsExport('sale_group'),
            'increment_id' => (int) $this->_helperSystem->getSettingsExport('increment_id'),
            'users' => $customerIds
        );

        $result = $this->post($path, $body);

        if($result['httpCode'] == 200){
            $recommendations = $result['body'];
        }
        return $recommendations;
    }
}