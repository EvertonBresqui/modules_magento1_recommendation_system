<?php

namespace Recommendation\System\Controller\Adminhtml\Export;

use \Magento\Backend\App\Action\Context;
use \Magento\Backend\App\Action;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\App\RequestInterface;
use Recommendation\System\Model\Export as ExportModel;

class Export extends Action
{
    protected $_resultJsonFactory;
    protected $_request;
    protected $_export;

    public function __construct(
        Context $context,
        RequestInterface $requestInterface,
        JsonFactory $jsonFactory,
        ExportModel $export
    )
    {
        parent::__construct($context);
        $this->_request = $requestInterface;
        $this->_resultJsonFactory = $jsonFactory;
        $this->_export = $export;
    }

    public function execute()
    {
        $response = 0;
        //Obtem a table a ser exportada
        $table = $this->_request->getParam('table');
        //Obtem os dados
        $data = json_decode($this->_request->getParam('data'));

        //Seta os valores na model
        $this->_export->init($table, $data);
        //Faz a exportação
        $response = $this->_export->exportInit();

        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}