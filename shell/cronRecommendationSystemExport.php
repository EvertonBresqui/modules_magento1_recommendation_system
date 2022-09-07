<?php

require_once 'abstract.php';

class Recommendation_Shell_CronRecommendationSystemExport extends Mage_Shell_Abstract
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('max_execution_time', 360000);
        set_time_limit(360000);

        if ($this->getArg('help')) {
            $this->usageHelp();
        } else {
            $this->_execute();
        }
    }

    protected function _execute()
    {
        $helper = Mage::helper('recommendation_system');
        $helper->setLog('[Recommendation_System] Run cron job recommendation_system_cronjob_export_sync');
        if($helper->getSettingsCron('enable_cron')){
            $helper->setLog('[Recommendation_System] Cron feed generation categories!');
            try{
                $modelExport = Mage::getSingleton('recommendation_system/export');
                $modelExport->init();
                $tables = $modelExport->getTables();
                foreach($tables as $tableName => $values){
                    //Convert to object
                    $tableValues = json_decode(json_encode($values));
                    //Seta os dados para a exportação                    
                    $modelExport->initData($tableName, $tableValues);
                    //Loop para gerar exportação
                    $i = 0;
                    while($i < $tableValues->qty_total){

                        //Faz a exportação
                        $response = $modelExport->export();

                        $i = $response['qty_exported'];
                    }
                }
                $helper->setLog('[Recommendation_System] Cron successfully executed!');
            }
            catch(\Exception $e){
                $helper->setLog('[Recommendation_System] Cron execution failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronRecommendationSystemExport.php -- [options]

                      Run (Faz a exportação dos dados para a Api)
  --help              Help

USAGE;
    }
}

$shell = new Recommendation_Shell_CronRecommendationSystemExport();
$shell->run();
