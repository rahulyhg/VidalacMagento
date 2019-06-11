<?php

namespace MercadoPago\Core\Model\CustomTicket;

/**
 * Class Payment
 *
 * @package MercadoPago\Core\Model\CustomTicket
 */
class Payment
    extends \MercadoPago\Core\Model\Custom\Payment
{
    /**
     * Define payment method code
     */
    const CODE = 'mercadopago_customticket';

    protected $_code = self::CODE;

    protected $fields_febraban = array(
        "firstName", "lastName", "docType","docNumber", "address", "addressNumber", "addressCity", "addressState", "addressZipcode"
    );

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        $response = $this->preparePostPayment();

        if ($response !== false) {
            $this->getInfoInstance()->setAdditionalInformation('activation_uri', $response['response']['transaction_details']['external_resource_url']);

            if(isset($response['response']['status'])){
                $this->getInfoInstance()->setAdditionalInformation('status', $response['response']['status']);
            }
            
            $this->setOrderSubtotals($response['response']);
            return true;
        }

        return false;
    }


    public function preparePostPayment($usingSecondCardInfo = null)
    {
        $this->_helperData->log("Ticket -> init prepare post payment", 'mercadopago-custom.log');
        $quote = $this->_getQuote();
        $order = $this->getInfoInstance()->getOrder();
        $payment = $order->getPayment();

        $payment_info = array();

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $payment_info['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        $preference = $this->_coreModel->makeDefaultPreferencePaymentV1($payment_info,$quote,$order);

        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");


        if ($payment->getAdditionalInformation("firstName") != "") {
            $preference['payer']['first_name'] = $payment->getAdditionalInformation("firstName");
        }
        if ($payment->getAdditionalInformation("lastName") != "") {
            $preference['payer']['last_name'] = $payment->getAdditionalInformation("lastName");
        }
        if ($payment->getAdditionalInformation("docType") != "") {
            $preference['payer']['identification']['type'] = $payment->getAdditionalInformation("docType");
            //remove last-name pessoa juridica
            if($preference['payer']['identification']['type'] == "CNPJ"){
                $preference['payer']['last_name'] = "";
            }
        }

        if ($payment->getAdditionalInformation("docNumber") != "") {
            $preference['payer']['identification']['number'] = $payment->getAdditionalInformation("docNumber");
        }
        if ($payment->getAdditionalInformation("address") != "") {
            $preference['payer']['address']['street_name'] = $payment->getAdditionalInformation("address");
        }
        if ($payment->getAdditionalInformation("addressNumber") != "") {
            $preference['payer']['address']['street_number'] = $payment->getAdditionalInformation("addressNumber");
        }
        if ($payment->getAdditionalInformation("addressCity") != "") {
            $preference['payer']['address']['city'] = $payment->getAdditionalInformation("addressCity");
            $preference['payer']['address']['neighborhood'] = $payment->getAdditionalInformation("addressCity");
        }
        if ($payment->getAdditionalInformation("addressState") != "") {
            $preference['payer']['address']['federal_unit'] = $payment->getAdditionalInformation("addressState");
        }
        if ($payment->getAdditionalInformation("addressZipcode") != "") {
            $preference['payer']['address']['zip_code'] = $payment->getAdditionalInformation("addressZipcode");
        }

        $this->_helperData->log("Ticket -> PREFERENCE to POST /v1/payments", 'mercadopago-custom.log', $preference);
        
        /* POST /v1/payments */
        return $this->_coreModel->postPaymentV1($preference);
    }

    /**
     * Assign corresponding data
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {

        // route /checkout/onepage/savePayment
        if (!($data instanceof \Magento\Framework\DataObject)) {
            $data = new \Magento\Framework\DataObject($data);
        }

        //get array info
        $infoForm = $data->getData();
      
        if(isset($infoForm['additional_data'])){
          $infoForm = $infoForm['additional_data'];
        }
      
        $this->_helperData->log("info form", self::LOG_NAME, $infoForm);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('payment_method', $infoForm['payment_method_ticket']);
        
        if (!empty($infoForm['coupon_code'])) {
            $info->setAdditionalInformation('coupon_code', $infoForm['coupon_code']);
        }

        // Fields for new febraban rule
        foreach ($this->fields_febraban as $key) {
            if (isset($infoForm[$key])) {
                $info->setAdditionalInformation($key, $infoForm[$key]);
            }
        }

        return $this;
    }

    /**
     * Return tickets options availables
     *
     * @return array
     */
    public function getTicketsOptions()
    {
        $payment_methods = $this->_coreModel->getPaymentMethods();
        $tickets = array();

        //percorre todos os payments methods
        foreach ($payment_methods['response'] as $pm) {

            //filtra por tickets
            if ($pm['payment_type_id'] == "ticket" || $pm['payment_type_id'] == "atm") {
                $tickets[] = $pm;
            }
        }

        return $tickets;
    }

    function setOrderSubtotals($data) {
        $total = $data['transaction_details']['total_paid_amount'];
        $order = $this->getInfoInstance()->getOrder();
        $order->setGrandTotal($total);
        $order->setBaseGrandTotal($total);
        $couponAmount = $data['coupon_amount'];
        if ($couponAmount) {
            $order->setDiscountCouponAmount($couponAmount * -1);
            $order->setBaseDiscountCouponAmount($couponAmount * -1);
        }
        $this->getInfoInstance()->setOrder($order);
    }
}