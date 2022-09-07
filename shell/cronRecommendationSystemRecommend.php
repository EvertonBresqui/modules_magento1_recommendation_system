<?php

require_once 'abstract.php';

class Recommendation_Shell_CronRecommendationSystemRecommend extends Mage_Shell_Abstract
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
        $helper->setLog('[Recommendation_Als] Run cron job recommendation_als_cronjob');
        Mage::getSingleton('recommendation_als/')
        $this->_recommendModel->processRecommendations();
        $helper->setLog('[Recommendation_Als] Cron execution finished!');
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cronRecommendationSystemRecommend.php -- [options]

                      Run (Processa as recomendações dos clientes)
  --help              Help

USAGE;
    }
}

$shell = new Recommendation_Shell_CronRecommendationSystemRecommend();
$shell->run();
