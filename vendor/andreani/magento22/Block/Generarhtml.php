<?php
/**
 * Created by PhpStorm.
 * User: ids
 * Date: 17/08/16
 * Time: 14:43
 */
namespace Ids\Andreani\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
//use Magento\Sales\Model\Order;
use Ids\Andreani\Helper\Data as AndreaniHelper;

class Generarhtml extends Template
{
    /**
     * @var Order
     */
    protected $_order;
    
    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $_storeInfo;

    /**
     * Generarhtml constructor.
     * @param Context $context
     * @param AndreaniHelper $andreaniHelper
     */
    public function __construct
    (
        Context $context,
//        Order $order,
        AndreaniHelper $andreaniHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Information $storeInfo
    )
    {
//        $this->_order           = $order;
        $this->_andreaniHelper  = $andreaniHelper;
        $this->_storeManager = $storeManager;
        $this->_storeInfo = $storeInfo;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getStoreInformation()
    {
        $store = $this->_storeManager->getStore();
        return $this->_storeInfo->getStoreInformationObject($store);
    }

    /**
     * @description apartir del id de la orden devuelve los datos de la guía.
     * @param $orderId
     * @return string
     */
    public function getAndreaniDataGuia($orderId)
    {
        $order      = $this->_andreaniHelper->getLoadShipmentOrder($orderId) ;

        //Recorre la colección de envíos, y verifica si hay datos en el campo asignado
        //para guardar los datos que generarán la guía en PDF.
        $andreaniDatosGuia  = '';
        $guiasArray         = [];
        $shipmentCollection = $order->getShipmentsCollection();
        foreach($shipmentCollection AS $shipments)
        {
            if($shipments->getAndreaniDatosGuia() !='')
            {
                $andreaniDatosGuia                          = $shipments->getAndreaniDatosGuia();
                $guiasArray[$shipments['increment_id']]     = $andreaniDatosGuia;
            }
        }

        $andreaniDatosGuia  = json_decode(unserialize($andreaniDatosGuia));
        return $guiasArray;
    }

    /**
     * @description retorna el path de la ubicación del código de barras para generar la guía.
     * @param $numeroAndreani
     * @return string
     */
    public function getCodigoBarras($numeroAndreani)
    {
       return $this->_andreaniHelper->getCodigoBarras($numeroAndreani);
    }

    /**
     * @description devuelve el logo que el cliente sube por admin
     * @return string
     */
    public function getLogoEmpresaPath()
    {
        return $this->_andreaniHelper->getlogoEmpresaPath();
    }
    
    public function getClientCredentials($categoria)
    {
        $clientCredentials  = [];
        $categoria          = strtolower($categoria);
        
        switch($categoria)
        {
            case 'estandar': $clientCredentials['contrato'] = $this->_andreaniHelper->getEstandarContrato();
                break;
            case 'urgente' : $clientCredentials['contrato'] = $this->_andreaniHelper->getUrgenteContrato();
                break;
            default        : $clientCredentials['contrato'] = $this->_andreaniHelper->getSucursalContrato();
                break;
        }
        
        $clientCredentials['cliente'] = $this->_andreaniHelper->getNroCliente();
        
        return $clientCredentials;
    }
}