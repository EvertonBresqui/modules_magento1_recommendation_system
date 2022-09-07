<?php

class Recommendation_System_Model_Export extends Recommendation_System_Model_Core_ApiAstract {

    protected $_helper;
    protected $_resource;
    protected $_readConnection;
    protected $_connectedApi;
    //Objeto para a escrita da exportação por JSON
    protected $_writer;
    /**
     * Attributos
     */
    protected $_table;
    protected $_attributes;
    protected $_qtyTotal;
    protected $_qtyExported;
    protected $_qtyPositionExported;
    protected $_data;
    protected $_linkFile;
    protected $_isDrop;

    public function init(){
        $this->_helper = Mage::helper('recommendation_system');
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
        $this->_writer = Mage::getSingleton('recommendation_system/core_writer');

        $this->setDomain($this->_helper->getSettingsExport('host'));

        // Faz a autenticação na api
        if($this->_helper->getSettingsExport('type_export') == 0){
            $this->_connectedApi = $this->auth();
        }
    }

    public function initData($table, $data){
        $this->_table = $table;
        $this->_attributes = $data->attributes;
        $this->_qtyExported = 0;
        $this->_qtyPositionExported = 0;
        $this->_isDrop = true;
    }

    public function getTables(){
        $return = array();
        if(trim($this->_helper->getSettingsData('tables')) != ''){
            $attributes = explode(PHP_EOL, $this->_helper->getSettingsData('tables'));
            foreach($attributes as $attributeLine){
                $attributeArray = explode('=', $attributeLine);
                $return[$attributeArray[0]]['attributes'] = $attributeArray[1];
                $return[$attributeArray[0]]['qty_total'] = $this->getQtyTotal($attributeArray[0]);
                $return[$attributeArray[0]]['qty_exported'] = 0;
                $return[$attributeArray[0]]['registry_position_exported'] = 0;
            }
        }
        return $return;
    }

    /**
     * Exporta dados
     */
    public function export(){
        $response = array();

        //Obtem os registros do banco
        $this->_data = $this->getData();
        //Verifica se foi exportados os dados
        $isExported = $this->process();

        if($isExported){
            $this->_qtyExported += count($this->_data); 
            $this->_qtyPositionExported += (int) $this->_helper->getSettingsData('qty_registers_cicle');
            if($this->_qtyExported == 0)
                $this->_isDrop = true;
            else
                $this->_isDrop = false;

            $response = array(
                'qty_exported' => $this->_qtyExported, 
                'registry_position_exported' => $this->_qtyPositionExported,
                'link_file' => $this->_linkFile
            );
        }

        return $response;
    }

    /**
     * Obtem os registros do banco
     */
    public function getData(){
        $columnsOrderBy = $this->_getTableIndicator();
        $limit = (int) $this->_helper->getSettingsData('qty_registers_cicle');
        $offset = (int) $this->_qtyPositionExported;

        $data = $this->_readConnection->fetchAll(
            'SELECT ' . $this->_attributes .
            ' FROM '. $this->_table . 
            ' GROUP BY ' . $columnsOrderBy .
            ' LIMIT ' . $limit .
            ' OFFSET ' . $offset
        );

        return $data;
    }

    /**
     * Processa os dados para exportação
     */
    public function process(){
        $isExported = false;
        //Faz a envio direto para a API
        if($this->_helper->getSettingsExport('type_export') == 0 && $this->_connectedApi === true){
            $isExported = $this->send();
        }
        //Faz a exportação em arquivo no formato JSON
        else{
            $isExported = $this->write();
        }
        return $isExported;
    }
    /**
     * Envia os dados para a API
     */
    public function send(){
        $isExported = false;
        try{
            $this->_helper->setLog('Exportação dos registros iniciada.');

            $route = 'sysimport';

            $params = array();
            $params['drop'] = $this->_isDrop;

            $body = array(
                'sale_group' => (int) $this->_helper->getSettingsExport('sale_group'),
                'params' => $params,
                'data' => array(
                    $this->_table => $this->_data
                )
            );
            $response = $this->post($route, $body);

            if($response['httpCode'] == 200){
                $this->_helper->setLog('Registros exportados com sucesso!');
                $isExported = true;
            }
            else{
                $this->_helper->setLog('Falha ao gravar os registros no arquivo de exportação.');
            }
        }
        catch(\Exception $e){
            $this->_helper->setLog('Falha ao tentar gravar no arquivo de exportação: ' . $e->getMessage());
        }
        return $isExported;
    }
    /**
     * Grava os dados em um arquivo JSON
     */
    public function write(){
        $isExported = false;
        try{
            $this->_helper->setLog('Exportação dos registros iniciada.');
            $this->_writer->init($this->_table, $this->_data, $this->_qtyExported);
            //Função que grava os registros
            $isExported = $this->_writer->saveData();
            if($isExported){
                $this->_linkFile = $this->_writer->getFileUrl();
                $this->_helper->setLog('Registros exportados com sucesso!');
            }
            else{
                $this->_helper->setLog('Falha ao gravar os registros no arquivo de exportação.');
            }
        }
        catch(\Exception $e){
            $this->_helper->setLog('Falha ao tentar gravar no arquivo de exportação: ' . $e->getMessage());
        }
        return $isExported;
    }

    /**
     * Obtem a quantidade total de registros das tabelas
     */
    public function getQtyTotal($tableName){
        
        $qtyTotal = (int)$this->_readConnection->query("SELECT COUNT(*) FROM $tableName")->fetchColumn(0);

        return $qtyTotal;
    }

    /**
     * Obtem a chave de ordenação para a paginação
     */
    private function _getTableIndicator(){
        $result = array();
        $tableIndicators = explode(PHP_EOL, $this->_helper->getSettingsData('table_incator_order_by'));
        //Percorre as chaves primárias das tabelas
        foreach($tableIndicators as $tableIndicator){
            $tableIndicatorArray = explode('=', $tableIndicator);
            if($tableIndicatorArray[0] === $this->_table){
                //Remove caracteres de quebra de linha e espaço
                $tableIndicatorArray[1] = $this->_formatStr($tableIndicatorArray[1]);
                $result = $tableIndicatorArray[1];
            }
        }
        return $result;
    }

    /**
     * Remove caracteres indesejados
     */
    private function _formatStr($string){
        $string = trim($string);
        $string = str_replace(PHP_EOL, '', $string);
        return $string;
    }
}