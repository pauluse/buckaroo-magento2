<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\Method;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY = 'buckaroo_original_transaction_key';

    /**
     * @var \TIG\Buckaroo\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory
     */
    protected $transactionBuilderFactory;

    /**
     * @var \TIG\Buckaroo\Model\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \TIG\Buckaroo\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface
     */
    public $payment;

    /**
     * @var bool
     */
    public $closeOrderTransaction = true;

    /**
     * @var bool
     */
    public $closeAuthorizeTransaction = true;

    /**
     * @var bool
     */
    public $closeCaptureTransaction = true;

    /**
     * @var bool
     */
    public $closeRefundTransaction = true;

    /**
     * @var bool
     */
    public $closeCancelTransaction = true;

    /**
     * @var bool|string
     */
    public $orderPlaceRedirectUrl = true;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * AbstractMethod constructor.
     *
     * @param \Magento\Framework\Model\Context                                  $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory                 $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                      $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                      $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                              $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null      $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null                $resourceCollection
     * @param \TIG\Buckaroo\Gateway\GatewayInterface|null                       $gateway
     * @param \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory|null         $transactionBuilderFactory
     * @param \TIG\Buckaroo\Model\ValidatorFactory                              $validatorFactory
     * @param \Magento\Framework\Message\ManagerInterface                       $messageManager
     * @param \Magento\Framework\App\RequestInterface                           $request
     * @param \TIG\Buckaroo\Helper\Data                                         $helper
     * @param \TIG\Buckaroo\Model\RefundFieldsFactory                           $refundFieldsFactory
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \TIG\Buckaroo\Gateway\GatewayInterface $gateway = null,
        \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory $transactionBuilderFactory = null,
        \TIG\Buckaroo\Model\ValidatorFactory $validatorFactory = null,
        \Magento\Framework\Message\ManagerInterface $messageManager = null,
        \TIG\Buckaroo\Helper\Data $helper = null,
        \Magento\Framework\App\RequestInterface $request = null,
        \TIG\Buckaroo\Model\RefundFieldsFactory $refundFieldsFactory = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->gateway = $gateway;
        $this->transactionBuilderFactory = $transactionBuilderFactory;
        $this->validatorFactory = $validatorFactory;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->request = $request;
        $this->refundFieldsFactory = $refundFieldsFactory;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }
        return parent::getConfigData($field, $storeId);
    }

    /**
     * @return bool|string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->orderPlaceRedirectUrl;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::order($payment, $amount);

        $this->payment = $payment;

        $transaction = $this->getOrderTransactionBuilder($payment)->build();

        if (!$transaction) {
            throw new \LogicException(
                'Order action is not implemented for this payment method.'
            );
        } elseif ($transaction === true) {
            return $this;
        }

        $response = $this->orderTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeOrderTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterOrder($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function orderTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->authorize($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::authorize($payment, $amount);

        $this->payment = $payment;

        $transaction = $this->getAuthorizeTransactionBuilder($payment)->build();

        if (!$transaction) {
            throw new \LogicException(
                'Authorize action is not implemented for this payment method.'
            );
        } elseif ($transaction === true) {
            return $this;
        }

        $response = $this->authorizeTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeAuthorizeTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterAuthorize($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function authorizeTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->authorize($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::capture($payment, $amount);

        $this->payment = $payment;

        $transaction = $this->getCaptureTransactionBuilder($payment)->build();

        if (!$transaction) {
            throw new \LogicException(
                'Capture action is not implemented for this payment method.'
            );
        } elseif ($transaction === true) {
            return $this;
        }

        $response = $this->captureTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeCaptureTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterCapture($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function captureTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->capture($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::refund($payment, $amount);

        $this->payment = $payment;

        $transaction = $this->getRefundTransactionBuilder($payment)->build();

        if (!$transaction) {
            throw new \LogicException(
                'Refund action is not implemented for this payment method.'
            );
        } elseif ($transaction === true) {
            return $this;
        }

        $response = $this->refundTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeRefundTransaction, false);

        $this->afterRefund($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function refundTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->refund($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::cancel($payment);

        $this->payment = $payment;

        $transaction = $this->getVoidTransactionBuilder($payment)->build();

        if (!$transaction) {
            throw new \LogicException(
                'Void action is not implemented for this payment method.'
            );
        } elseif ($transaction === true) {
            return $this;
        }

        $response = $this->voidTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeCancelTransaction, false);

        $this->afterVoid($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function voidTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->void($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment authorization could not be voided. Please try again.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterOrder($payment, $response)
    {
        $this->_eventManager->dispatch(
            'tig_buckaroo_method_order_after',
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterAuthorize($payment, $response)
    {
        $this->_eventManager->dispatch(
            'tig_buckaroo_method_authorize_after',
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterCapture($payment, $response)
    {
        $this->_eventManager->dispatch(
            'tig_buckaroo_method_capture_after',
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterRefund($payment, $response)
    {
        $this->_eventManager->dispatch(
            'tig_buckaroo_method_refund_after',
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterVoid($payment, $response)
    {
        $this->_eventManager->dispatch(
            'tig_buckaroo_method_void_after',
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        return $this;
    }

    /**
     * @param \StdClass                                                                          $response
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param                                                                                    $close
     * @param bool                                                                               $saveId
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface
     */
    public function saveTransactionData(
        \StdClass $response,
        \Magento\Payment\Model\InfoInterface $payment,
        $close,
        $saveId = false
    ) {
        if (!empty($response->Key)) {
            $transactionKey = $response->Key;
            /** @noinspection PhpUndefinedMethodInspection */
            $payment->setIsTransactionClosed($close);

            /**
             * Recursively convert object to array.
             */
            $arrayResponse = json_decode(json_encode($response), true);

            /**
             * Save the transaction's response as additional info for the transaction.
             */
            $rawInfo = $this->getTransactionAdditionalInfo($arrayResponse);

            /** @noinspection PhpUndefinedMethodInspection */
            $payment->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $rawInfo
            );

            /**
             * Save the payment's transaction key.
             */
            if ($saveId) {
                /** @noinspection PhpUndefinedMethodInspection */
                $payment->setTransactionId($transactionKey);
                $payment->setAdditionalInformation(self::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY, $transactionKey);
            }
        }

        return $payment;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function getTransactionAdditionalInfo(array $array)
    {
        return $this->helper->getTransactionAdditionalInfo($array);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getOrderTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getAuthorizeTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getCaptureTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getRefundTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getVoidTransactionBuilder($payment);
}
