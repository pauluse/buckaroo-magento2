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
 * @copyright Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider\Method;

/**
 * @method getPaymentFeeLabel()
 * @method getSellersProtection()
 * @method getSellersProtectionEligible()
 * @method getSellersProtectionIneligible()
 * @method getSellersProtectionItemnotreceivedEligible()
 * @method getSellersProtectionUnauthorizedpaymentEligible()
 */
class Payconiq extends AbstractConfigProvider
{
    const XPATH_PAYCONIQ_PAYMENT_FEE                      = 'payment/tig_buckaroo_payconiq/payment_fee';
    const XPATH_PAYCONIQ_PAYMENT_FEE_LABEL                = 'payment/tig_buckaroo_payconiq/payment_fee_label';
    const XPATH_PAYCONIQ_ACTIVE                           = 'payment/tig_buckaroo_payconiq/active';
    const XPATH_PAYCONIQ_ACTIVE_STATUS                    = 'payment/tig_buckaroo_payconiq/active_status';
    const XPATH_PAYCONIQ_ORDER_STATUS_SUCCESS             = 'payment/tig_buckaroo_payconiq/order_status_success';
    const XPATH_PAYCONIQ_ORDER_STATUS_FAILED              = 'payment/tig_buckaroo_payconiq/order_status_failed';
    const XPATH_PAYCONIQ_AVAILABLE_IN_BACKEND             = 'payment/tig_buckaroo_payconiq/available_in_backend';
    const XPATH_PAYCONIQ_SELLERS_PROTECTION               = 'payment/tig_buckaroo_payconiq/sellers_protection';
    const XPATH_PAYCONIQ_SELLERS_PROTECTION_ELIGIBLE      = 'payment/tig_buckaroo_payconiq/sellers_protection_eligible';
    const XPATH_PAYCONIQ_SELLERS_PROTECTION_INELIGIBLE    = 'payment/tig_buckaroo_payconiq/sellers_protection_ineligible';
    const XPATH_PAYCONIQ_SELLERS_PROTECTION_ITEMNOTRECEIVED_ELIGIBLE = 'payment/tig_buckaroo_payconiq/sellers_protection_itemnotreceived_eligible';
    const XPATH_PAYCONIQ_SELLERS_PROTECTION_UNAUTHORIZEDPAYMENT_ELIGIBLE = 'payment/tig_buckaroo_payconiq/sellers_protection_unauthorizedpayment_eligible';

    const XPATH_ALLOWED_CURRENCIES = 'payment/tig_buckaroo_payconiq/allowed_currencies';

    const XPATH_ALLOW_SPECIFIC                  = 'payment/tig_buckaroo_payconiq/allowspecific';
    const XPATH_SPECIFIC_COUNTRY                = 'payment/tig_buckaroo_payconiq/specificcountry';

    const PAYCONIC_REDIRECT_URL = '/buckaroo/payconiq/pay';

    /**
     * @return array
     */
    public function getConfig()
    {
        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(\TIG\Buckaroo\Model\Method\Payconiq::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'payconiq' => [
                        'paymentFeeLabel' => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                        'redirecturl' => self::PAYCONIC_REDIRECT_URL
                    ],
                ],
            ],
        ];
    }

    /**
     * @return float
     */
    public function getPaymentFee($storeId = null)
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_PAYCONIQ_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : false;
    }
}
