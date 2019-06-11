<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ids\Andreani\Plugin\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Ids\Andreani\Controller\Adminhtml\Order\Shipment\MassImprimirGuiasAndreani;

class MassPrintShippingLabelPlugin extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var MassImprimirGuiasAndreani
     */
    protected $_massImprimirGuiasAndreani;

    /**
     * MassPrintShippingLabelPlugin constructor.
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param LabelGenerator $labelGenerator
     * @param CollectionFactory $collectionFactory
     * @param MassImprimirGuiasAndreani $massImprimirGuiasAndreani
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        LabelGenerator $labelGenerator,
        CollectionFactory $collectionFactory,
        MassImprimirGuiasAndreani $massImprimirGuiasAndreani
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->labelGenerator = $labelGenerator;
        $this->_massImprimirGuiasAndreani = $massImprimirGuiasAndreani;
        parent::__construct($context, $filter);
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $guiasContent = [];
        $labelsContent = [];
        $andreaniIds = [];

        if ($collection->getSize()) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            foreach ($collection as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if($shipment->getAndreaniDatosGuia())
                {
                    $guiasContent[$shipment->getIncrementId()] = json_decode(unserialize($shipment->getAndreaniDatosGuia()));
                    $andreaniIds[] = $shipment->getEntityId();
                }
                elseif($labelContent)
                    $labelsContent[] = $labelContent;
            }
        }

        if($guiasContent)
            $this->_massImprimirGuiasAndreani->_generarGuiasMasivas($guiasContent, $andreaniIds, true);

        if (!empty($labelsContent)) {
            $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
            return $this->fileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        if(!$guiasContent && !$labelsContent)
            $this->messageManager->addError(__('There are no shipping labels related to some of the selected shipments.'));

        return $this->resultRedirectFactory->create()->setPath('sales/shipment/');
    }
}
