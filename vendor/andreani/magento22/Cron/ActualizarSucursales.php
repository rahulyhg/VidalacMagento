<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ids\Andreani\Cron;

use Ids\Andreani\Helper\Data as AndreaniHelper;
use Ids\Andreani\Model\Webservice;
use Symfony\Component\Config\Definition\Exception\Exception;

class ActualizarSucursales
{
    protected $_logger;

    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;

    /**
     * @var Webservice
     */
    protected $_webservice;

    /**
     * @var
     */
    protected $resourceConnection;

    /**
     * @var SucursalFactory
     */
    protected $_sucursalFactory;

    /**
     * @var \Ids\Andreani\Model\ProvinciaFactory
     */
    protected $_provinciaFactory;

    /**
     * ActualizarSucursales constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Psr\Log\LoggerInterface $logger
     * @param AndreaniHelper $andreaniHelper
     * @param \Ids\Andreani\Model\ProvinciaFactory $provinciaFactory
     * @param Webservice $webservice
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        AndreaniHelper $andreaniHelper,
        \Ids\Andreani\Model\ProvinciaFactory $provinciaFactory,
        \Ids\Andreani\Model\SucursalFactory $sucursalFactory,
        Webservice $webservice
    ) {
        $this->_logger              = $logger;
        $this->resourceConnection   = $resourceConnection;
        $this->_webservice          = $webservice;
        $this->_andreaniHelper      = $andreaniHelper;
        $this->_sucursalFactory     = $sucursalFactory;
        $this->_provinciaFactory    = $provinciaFactory;
    }

    /**
     * Método que se ejecuta cuando corre el cron.
     */
    public function execute()
    {
        try
        {
            $sucursales = $this->_webservice->consultarSucursales();

            if(is_array($sucursales))
            {
                $this->resourceConnection = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                $connection= $this->resourceConnection->getConnection();

                $infoIdsAndreniSucursal = [];
                //$tablaSucursal  = $this->_sucursalFactory->create()->getCollection();
                $tablaProvincia = $this->_provinciaFactory->create()->getCollection();
                $connection->truncateTable($this->resourceConnection->getTableName('ids_andreani_sucursal'));

                foreach ($sucursales as $_sucursal)
                {
                    $provinciaId = 0;
                    $direccion = explode(',', $_sucursal->Direccion);

                    $telefonos = "Tel: " .$_sucursal->Telefono1;

                    if($_sucursal->Telefono2 != "")
                        $telefonos .= " / " .$_sucursal->Telefono2;
                    if($_sucursal->Telefono3 != "")
                        $telefonos .= " / " .$_sucursal->Telefono3;

                    foreach($tablaProvincia as $_provincia)
                    {
                        if($_provincia->getNombre() == trim($direccion[3]))
                        {
                            $provinciaId = $_provincia->getProvinciaId();
                            break;
                        }
                    }

                    array_push($infoIdsAndreniSucursal,[$_sucursal->Descripcion, trim($direccion[0]), intval($direccion[1]), $telefonos, intval($provinciaId), $_sucursal->Sucursal, trim($direccion[2])]);
                }

                $connection->insertArray($this->resourceConnection->getTableName('ids_andreani_sucursal'),
                    ['nombre','direccion','codigo_postal','telefono','provincia_id','codigo_sucursal','localidad'], $infoIdsAndreniSucursal);
            }
        }
        catch (\Exception $e)
        {
            $this->_logger->debug('Hubo un error al intentar actualizar la tabla ids_andreani_sucursal '.$e);
        }

        return $this;
    }
}