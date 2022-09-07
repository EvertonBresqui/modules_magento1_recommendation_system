<?php

class Recommendation_Als_Model_Mail extends Recommendation_System_Model_Core_MailAbstract {

    private $_helperSystem;

    public function init(){
        $this->_helperSystem = Mage::helper('recommendation_system');
    }

    public function sendMailRecommendation($customer, $recommendations)
    {
        $settings = array();
        $data = array();
        // Get Settings Mail
        $settings['template_id'] = $this->_helperSystem->getSettingsGeneral('template_id');
        $settings['sender_name'] = $this->_helperSystem->getSettingsGeneral('name_sale');
        $settings['sender_email'] = $this->_helperSystem->getSettingsGeneral('email_sale');

        $settings['to_email'] = $customer->getEmail();
        $settings['to_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        
        //Set Data
        $data = array(
            'customer_name' => $data['to_name'],
            'recommendations' => json_encode($recommendations)
        );

        $result = $this->sendTemplateMail($settings, $data);

        return $result;
    }
}