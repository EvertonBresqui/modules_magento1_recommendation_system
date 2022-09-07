<?php
class Recommendation_Als_Helper_Data extends Mage_Core_Helper_Abstract
{

    // Configurações gerais do módulo
    const SETTINGS_GENERAL = 'recommendation_als/settings_general';

    public function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * Obtem as configurações gerais
     */
    public function getSettingsGeneral($configNode){
        return Mage::getStoreConfig(self::SETTINGS_GENERAL .'/' . $configNode, $this->getStoreId());
    }
}
