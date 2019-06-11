<?php

namespace Ids\Andreani\Controller\Localidad;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\TestFramework\Inspection\Exception;
use Ids\Andreani\Model\CodigoPostalFactory;
use Ids\Andreani\Model\SucursalFactory;

/**
 * Class Localidad
 *
 * @description Action que recibe un id de provincia y devuelve todas las localidades que tenga con sus respectivos
 *              codigos postales.
 *
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Controller\Localidad
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var CodigoPostalFactory
     */
    protected $_codigoPostalFactory;

    protected $_sucursalFactory;
    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param CodigoPostalFactory $codigoPostalFactory
     */
    public function __construct
    (
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        CodigoPostalFactory $codigoPostalFactory,
        SucursalFactory $sucursalFactory
    )
    {
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_resultJsonFactory   = $resultJsonFactory;
        $this->_codigoPostalFactory = $codigoPostalFactory;
        $this->_sucursalFactory = $sucursalFactory;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $request     = $this->getRequest();
        $result      = $this->_resultJsonFactory->create();
        $localidades = [];

        if(($provinciaId = $request->getParam('provincia_id')) && $request->isXmlHttpRequest())
        {
            $localidades = $this->_sucursalFactory->create()
                ->getCollection()
                ->addFieldToFilter('provincia_id',['eq'=>$provinciaId]);

            $localidades->getSelect()->group('localidad');
            $localidades = $localidades->getData();
        }

        return $result->setData($localidades);

    }
}