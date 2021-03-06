<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Service\CreditManagement;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class ServiceParameters
{
    /** @var ServiceParameters\CreateCombinedInvoice */
    private $createCombinedInvoice;

    /** @var ServiceParameters\CreateCreditNote */
    private $createCreditNote;

    public function __construct(
        ServiceParameters\CreateCombinedInvoice $createCombinedInvoice,
        ServiceParameters\CreateCreditNote $createCreditNote
    ) {
        $this->createCombinedInvoice = $createCombinedInvoice;
        $this->createCreditNote = $createCreditNote;
    }

    /**
     * @param OrderPaymentInterface|InfoInterface $payment
     * @param string                              $configProviderType
     * @param array                               $filterParameter
     *
     * @return array
     */
    public function getCreateCombinedInvoice($payment, $configProviderType, $filterParameter = array())
    {
        $requestParameter = $this->createCombinedInvoice->get($payment, $configProviderType);

        $requestParameter = $this->filterParameter($requestParameter, $filterParameter);

        return $requestParameter;
    }

    /**
     * @param OrderPaymentInterface|InfoInterface $payment
     * @param array                               $filterParameter
     *
     * @return array
     */
    public function getCreateCreditNote($payment, $filterParameter = array())
    {
        $requestParameter = $this->createCreditNote->get($payment);

        $requestParameter = $this->filterParameter($requestParameter, $filterParameter);

        return $requestParameter;
    }

    /**
     * @param array $requestParameters
     * @param array $filterParameter
     *
     * @return mixed
     */
    public function filterParameter($requestParameters, $filterParameter)
    {
        if (!isset($requestParameters['RequestParameter'])) {
            return $requestParameters;
        }

        $filteredRequest = array_filter(
            $requestParameters['RequestParameter'],
            function ($parameter) use ($filterParameter) {
                $valueToTest = [];
                $valueToTest['Name'] = $parameter['Name'];

                if (isset($parameter['Group'])) {
                    $valueToTest['Group'] = $parameter['Group'];
                }

                if (in_array($valueToTest, $filterParameter)) {
                    return false;
                }

                return true;
            }
        );

        $requestParameters['RequestParameter'] = array_values($filteredRequest);

        return $requestParameters;
    }
}
