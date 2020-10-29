<?php 

namespace SymfonyPayments\PayPal;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SymfonyPayments\Model\PayPalItem;

class SymfonyPaymentsPayPalClient
{
    private const PAYPAL_URI = "/api/paypal/payment";
    public const STATUS_COMPLETED = "COMPLETED";

    private $url;
    private $client;

    /**
     * @param string $url location of the SymfonyPayments
     */
    public function __construct(string $url)
    {
        $this->url = $url;

        $this->client = new Client([
            "headers" => [
                "Content-Type" => "application/json"
            ]
        ]);
    }

    /**
     * @param $amount
     * @param string $currency
     * @param PayPalItem[]|null $items
     * @param null $returnUrl
     * @param null $cancelUrl
     * @return string
     * @throws GuzzleException
     */
    public function createPayment($amount, $currency = "USD", $items = null, $returnUrl = null, $cancelUrl = null)
    {
        $body = [
            "amount" => $amount,
            "currency" => $currency,
            "return_url" => $returnUrl,
            "cancel_url" => $cancelUrl
        ];

        if ($items != null) {
            $body["items"] = $items;
        }

        $data = $this->client->post($this->url . self::PAYPAL_URI, [
            "body" => json_encode($body)
        ]);

        return $data->getBody()->getContents();
    }

    /**
     * @param $payerId
     * @param $orderId
     * @param null $refundCallbackUrl
     * @return PayPalTransactionResponse
     * @throws GuzzleException
     */
    public function completePayment($payerId, $orderId, $refundCallbackUrl = null)
    {
        $body = [
            "payerID" => $payerId,
            "orderID" => $orderId,
        ];

        if ($refundCallbackUrl != null) {
            $body["refund_callback"] = $refundCallbackUrl;
        }

        $data = $this->client->put($this->url . self::PAYPAL_URI, [
            "body" => json_encode($body)
        ]);

        return new PayPalTransactionResponse($data->getBody()->getContents());
    }
}