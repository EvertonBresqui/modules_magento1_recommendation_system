<?php

class Recommendation_Als_Model_Recommend {

    private $_helper;
    private $_helperSystem;
    private $_mail;
    private $_api;
    private $_pageRecommendedUser;
    private $_store;

    public function init(){
        $this->_helperSystem = Mage::helper('recommendation_system');
        $this->_helper = Mage::helper('recommendation_als');
        $this->_mail = Mage::getSingleton('recommendation_als/mail');
        $this->_api = Mage::getSingleton('recommendation_als/api');
        $this->_pageRecommendedUser = Mage::getSingleton('recommendation_als/pageRecommendedUser');
    }

    /**
     * Processa as recomendações para os usuários
     *
     * @return void
     */
    public function processRecommendations()
    {

        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $this->_store = $store;
            //Seta o id da loja
            Mage::app()->setCurrentStore($store->getId());
            // Verifica se o módulo foi habilitado na visão da loja 
            if ($this->_helper->getSettingsGeneral('enable')) {
                $this->_helperSystem->setLog('[Recommendation_Als] Cron recommend als executed!');
                try {
                    //Verifica se a api esta ativa
                    if ($this->isApiActive()) {
                        // Obtem clientes para efetuar recomendações
                        $collectionCustomer = $this->getCustomersCollection($store->getId());

                        if ($collectionCustomer != false) {
                            // Envia recomendações para os clientes
                            $this->sentRecommendationsCustomers($collectionCustomer);
                            // Salva a paginação
                            $this->saveIncrementPagination();
                        }
                    }
                } catch (\Exception $e) {
                    $this->_helperSystem->setLog('[Recommendation_Als] Cron execution failed: ' . $e->getMessage());
                }
            }
        }
    }

    public function isApiActive()
    {
        if ($this->_api->getToken() != null) {
            return true;
        }
        return false;
    }

    public function sendMailRecommendation($customer, $recommendations)
    {
        $result = $this->_mail->sendMailRecommendation($customer, $recommendations);
        return $result;
    }

    public function getCustomerIds($collectionCustomer)
    {
        $customerIds = array();

        foreach ($collectionCustomer as $customer) {
            $customerIds[] = $customer->getId();
        }

        return $customerIds;
    }

    public function sentRecommendationsCustomers($collectionCustomers)
    {
        $customersIds = $this->getCustomerIds($collectionCustomers);

        if(count($customersIds) > 0){
            $recommendations = $this->_api->getRecommendations($customersIds);

            if (isset($recommendations->result) && is_array($recommendations->result) && count($recommendations->result) > 0) {
                foreach ($collectionCustomers as $customer) {
                    // Envia o email das recomendações para o cliente
                    $this->sendMailRecommendation($customer, $recommendations);
                }
            }
        }
    }

    public function getCustomersCollection()
    {
        $collectionCustomer = Mage::getModel('customer/customer')->getCollection();
        $collectionCustomer->addAttributeToSelect('entity_id');
        $collectionCustomer->addAttributeToSelect('email');
        $collectionCustomer->setPageSize($this->_helper->getSettingsGeneral('qty_registrys_cicle'));
        $collectionCustomer->setCurPage($this->getCurrentPage());
        $collectionCustomer->addFieldToFilter("store_id", array("eq" => $this->_store->getId()));

        return $collectionCustomer;
    }

    public function saveIncrementPagination()
    {
        $currentPagination = $this->getCurrentPage();

        if (!is_numeric($currentPagination)) {
            $currentPagination = 0;
        }

        $this->setPage($currentPagination + 1);
    }

    public function getCurrentPage(){
        $page = $this->getPage();

        if($page->count() > 0){
            return $page->getData()[0]['page_id'];
        }
        return 0;
    }

    public function getPage(){
        $currentPage = $this->_pageRecommendedUser->getCollection()
            ->addFieldToFilter('store_id', $this->_store->getId());
        
        return $currentPage;
    }

    public function isPageExists(){
        $page = $this->getPage();
        
        if($page->count() > 0)
            return true;
        return false;
    }

    public function setPage($pageIncrement){
        $pageExists = $this->isPageExists($this->_store->getId());

        if($pageExists){
            //update
            $page = $this->getPage();
            $pageRegistry = $this->_pageRecommendedUser->load($page->getData()[0]['id']);
            $pageRegistry->setStoreId($this->_store->getId());
            $pageRegistry->setPageId($pageIncrement);
            return $pageRegistry->save();
        }
        else{
            //insert
            $this->_pageRecommendedUser->setData(
                array(
                    'page_id' => $pageIncrement,
                    'store_id' => $this->_store->getId()
                )
            );
            return $this->_pageRecommendedUser->save();
        }
    }
}