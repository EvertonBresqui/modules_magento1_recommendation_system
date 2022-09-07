<?php
class Recommendation_System_Helper_Data extends Mage_Core_Helper_Abstract
{

    // Configurações gerais do módulo
    const SETTINGS_GENERAL = 'recommendation_system/settings_general';
    // Configurações do cron
    const SETTINGS_CRON = 'recommendation_system/settings_cron';
    // Configurações de exportação
    const SETTINGS_EXPORT = 'recommendation_system/settings_export';
    // Configurações da estrutura dos dados
    const SETTINGS_DATA = 'recommendation_system/settings_data';

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
    /**
     * Obtem as configurações do CRON
     */
    public function getSettingsCron($configNode){
        return Mage::getStoreConfig(self::SETTINGS_CRON .'/' . $configNode, $this->getStoreId());
    }
    /**
     * Obtem as configurações de exportação
     */
    public function getSettingsExport($configNode){
        return Mage::getStoreConfig(self::SETTINGS_EXPORT .'/' . $configNode, $this->getStoreId());
    }
    /**
     * Obtem as configurações da estrutura dos dados
     */
    public function getSettingsData($configNode)
    {
        return Mage::getStoreConfig(self::SETTINGS_DATA .'/' . $configNode, $this->getStoreId());
    }
    /**
     * Função que grava os logs
     */
    public function setLog($message){
        Mage::log(
            '[Recommendation_System] ' . $message,
            null,
            $this->getSettingsGeneral('name_file_log'),
            true
        );
    }
}
