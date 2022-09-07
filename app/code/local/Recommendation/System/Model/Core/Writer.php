<?php

/**
 * Classe que gera arquivos json para subir para 
 * a API do sistema de recomendação
 */

class Recommendation_System_Model_Core_Writer {

    protected $_tableName;
    protected $_data;
    protected $_qtyExported;
    /**
     * Armazena o caminho completo do arquivo
     */
    protected $_filePath;
    /**
     * Armazena a URL completa do arquivo
     */
    protected $_fileUrl;
    /**
     * Armazena apenas o caminho do diretório de exportação
     * e o arquivo interno da exportação
     */
    protected $_filePathExport;
    /**
     * Armazena o caminho do diretorio media
     *
     * @var [string]
     */
    protected $_mediaRootDir;


    const DIRECTORY_EXPORT = 'export_api_recomendation';

    /**
     * Função Construtor
     */
    public function init($tableName, $data, $qtyExported){
        $this->_tableName = $tableName;
        $this->_data = $data;
        $this->_qtyExported = $qtyExported;
        $this->_mediaRootDir = Mage::getBaseDir('media');
        $this->_filePathExport = $this->_getFilePathExport();
        //Obtem o caminho do arquivo de exportação
        $this->_filePath = $this->_getAbsolutePath($this->_mediaRootDir);
    }

    /**
     * Salva os dados gerados na exportação
     */
    public function saveData(){
        //Retorna se o arquivo existe ou n
        $fileExists = file_exists($this->_filePath);
        //Caso começou uma exportação para a tabela
        if($this->_qtyExported == 0 && $fileExists){
            //Deleta o arquivo para fazer uma nova exportação
            $this->_deleteFile($this->_filePath);
        }
        $isSaved = $this->_saveFile($this->_filePath, $this->_data);
        if($isSaved)
            return true;
        return false;
    }

    protected function _saveFile($filePath, $data){
        try{
            //Faz a leitura do conteúdo já existente no arquivo
            $dataArray = [];
            $fileExists = file_exists($filePath);
            if($fileExists){
                $fp = fopen($filePath, 'r');
                $dataArray = json_decode(fgets($fp));
                fclose($fp);
            }
            //Adiciona o conteudo da nova exportação
            $dataArray[] = $data;
            //Converte para json
            $dataJsonFile = json_encode($dataArray);
            //Salva os dados da exportação
            $fp = fopen($filePath, 'w');
            fwrite($fp, $dataJsonFile);
            fclose($fp);
            return true;
        }
        catch(\Exception $e){
            return false;
        }
    }

    protected function _deleteFile($fileName){
        $res = unlink($fileName);
        return $res;
    }

    protected function _getAbsolutePath(){
        $path = $this->_mediaRootDir ;
        $filePathExportArray = explode('/', $this->_filePathExport);
        foreach($filePathExportArray as $fileExport){
            $path .= '/' . $fileExport;
            //Verifica se o diretório de exportação foi criado
            if(!is_dir($path)){
                //Cria diretório de exportação
                mkdir($path, 0755);
            }
        }
        $pathDirectory = $this->_mediaRootDir . '/' . $this->_filePathExport;
        
        return $pathDirectory . '/' . $this->_getNameFileExport();
    }
    /**
     * Obtem apenas o caminho do diretório de exportação
     * e o arquivo interno da exportação
     */
    protected function _getFilePathExport(){
        $dateNow = strval(date('Y-m-d'));
        $pathExport = self::DIRECTORY_EXPORT . '/' . $dateNow;

        return $pathExport;
    }

    /**
     * Obtem o nome do arquivo da exportação
     */
    protected function _getNameFileExport(){
        return $this->_tableName .'.json';
    }

    /**
     * Obtem o link do arquivo pela URL
     */
    public function getFileUrl(){
        //Obtem a URL da pasta media
        $urlMedia = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        
        return $urlMedia . $this->_filePathExport . '/' . $this->_getNameFileExport();
    }

}
