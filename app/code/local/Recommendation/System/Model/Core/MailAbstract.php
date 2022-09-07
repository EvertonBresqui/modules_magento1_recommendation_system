<?php

abstract class Recommendation_System_Model_Core_MailAbstract {

    public function sendTemplateMail($settings, $data) 
    {
        $helper = Mage::helper('recommendation_system');
        $emailTemplate  = Mage::getModel('core/email_template')
            ->load($settings['template_id']);
            
        $emailTemplate->setSenderName($settings['sender_name']);
        $emailTemplate->setSenderEmail($settings['sender_email']); 

        try {
            $emailTemplate->send(
                $settings['to_email'], $settings['to_name'], $data
            );
            return true;
        }
        catch (\Exception $e) {
            $helper->setLog(
                    'Error: ' . $e->getMessage()
            );
            return false;
        }
    }
}