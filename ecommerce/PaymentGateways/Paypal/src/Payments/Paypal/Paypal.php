<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use Throwable;
use ErrorException;
use GuzzleHttp\Client;
use Ollyo\PaymentHub\Core\Support\Arr;
use Ollyo\PaymentHub\Core\Support\Path;
use Ollyo\PaymentHub\Core\Support\System;
use GuzzleHttp\Exception\RequestException;
use Ollyo\PaymentHub\Core\Payment\BasePayment;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;
use Ollyo\PaymentHub\Exceptions\InvalidSignatureException;


class Paypal extends BasePayment
{
	/**
	 * The Paypal config Repository instance
	 *
	 * @var   RepositoryContract
     * @since 1.0.0
	 */
	protected $config;

    protected $orderID;

    /**
     * Checks if all required configuration keys are present and not empty.
     *
     * This method ensures that the necessary configuration settings are available
     * and properly set up before proceeding with any operations that depend on them.
     *
     * @return bool Returns true if all required configuration keys are present and not empty, otherwise false.
     * @since  1.0.0
     */
    public function check(): bool
    {
        $configKeys = Arr::make(['client_id','client_secret','merchant_email','success_url','cancel_url','webhook_id']);

        $isConfigOk = $configKeys->every(function($key){
            return $this->config->has($key) && !empty($this->config->get($key));
        });

        return $isConfigOk;
    }

    public function setup(): void
    {
        
    }

    /**
     * Sets the data for processing after preparing it.
     *
     * This method overrides the setData method in the parent class,
     * ensuring that the data is first prepared before setting it.
     * If an error occurs during data preparation, it is re-thrown.
     *
     * @param  mixed     $data The data to be set.
     * @throws Throwable       If an error occurs during data preparation, it is re-thrown.
     * @since  1.0.0
     */
    public function setData($data): void
    {
        try {
            parent::setData($this->prepareData($data));
        } catch (Throwable $error) {
            throw $error;
        }
    }

    /**
     * Prepares the data for sending to `Paypal Server`.
     *
     * This method takes raw data, processes it to create structured purchase unit data
     * including amount, shipping information, and payment preferences. It uses configuration
     * settings and ensures essential fields are set correctly.
     *
     * @param  mixed $data The raw data to be processed.
     * @return array       The structured data for sending to `Paypal Server`.
     * @since  1.0.0
     */
    public function prepareData($data)
    {
        if (empty($data)) {
            return [];
        }

        $data          = Arr::make((array) $data);
        $this->orderID = $data['order_id'];

        $returnData = [
            'purchase_units' => [
                [
                    'custom_id' => $data['order_id'],
                    'items'     => static::getItems($data),
                    'amount'    => $this->createAmountData($data),
                    'payee'     => ['email_address' => $this->config->get('merchant_email')]
                ]
            ],
            'intent'         => 'CAPTURE',
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'user_action'               => 'PAY_NOW',
                        'payment_method_preference' => 'UNRESTRICTED',
                        'return_url'                => $this->config->get('success_url'),
                        'cancel_url'                => $this->config->get('cancel_url'),
                    ]
                ]
            ]
        ];

        if ($data->has('shipping_address') && !empty($data['shipping_address'])) {
            $returnData['purchase_units'][0]['shipping'] = $this->getShippingInfo($data['shipping_address']);
        }
        
        return $returnData;
    }

    /**
     * Initiates the payment creation process.
     *
     * This method retrieves an access token and use the token to return a checkout URL.
     * If an error occurs during these operations, it handles the error and throws an exception.
     *
     * @throws ErrorException If there is an error retrieving the checkout URL or handling the response.
     * @since  1.0.0
     */
    public function createPayment()
    {
        try {
            
            $accessToken = $this->getAccessToken();  
            $checkoutUrl = $this->getCheckoutURL($accessToken);
             
            if (!is_null($checkoutUrl)) {
                header("Location: {$checkoutUrl}");
            }
            
        } catch (RequestException $error) {
            
            $errorMessage = $this->handleErrorResponse($error) ?? $error->getMessage();
            throw new ErrorException($errorMessage);
        }
    }

    /**
     * Creates the amount data array for a payment request.
     *
     * This method constructs and returns an array containing the currency code, total price, and item breakdown
     * for a payment request. It also includes optional fields for shipping, tax, and discount if they are present in the input data.
     *
     * @param  Arr   $data The input data containing the currency, total price, subtotal, and optional fields.
     * @return array       The formatted amount data including currency code, total price, and breakdown.
     * @since  1.0.0
     */
    private function createAmountData(Arr $data)
    {
        $returnData = [
            'currency_code' => $data['currency']->code,
            'value'         => (string) $data['total_price']
        ];

        Arr::make([
            'shipping_charge' => 'shipping',
            'tax'             => 'tax_total',
            'coupon_discount' => 'discount',
            'subtotal'        => 'item_total'
        ])->every(function($value, $key) use ($data, &$returnData) {
            
                if ($data->has($key)) {
                    
                    $returnData['breakdown'][$value] = [
                        'currency_code' => $data['currency']->code,
                        'value'         => (string) $data[$key]
                    ];
                }
            
                return true;
            });

        return $returnData;
    }

    /**
     * Creates the shipping information array for a payment request.
     *
     * This method constructs and returns an array containing the shipping type, receiver's full name,
     * and address for a payment request.
     *
     * @param  object  $data The input data containing the receiver's name and address.
     * @return array        The formatted shipping information including type, name, and address.
     * @since  1.0.0
     */
    private function getShippingInfo($shipping)
    {        
        [$address1, $address2] = System::splitAddress($shipping, 300);

        return [
            'type'      => 'SHIPPING',
            'name'      => ['full_name' => $shipping->name],
            'address'   => [
                'address_line_1'    => $address1,
                'address_line_2'    => $address2,
                'admin_area_2'      => $shipping->city,
                'admin_area_1'      =>  $shipping->region,
                'postal_code'       => $shipping->postal_code,
                'country_code'      => $shipping->country->alpha_2
            ]
        ];
    }

    /**
     * Retrieves an access token from the OAuth2 token endpoint.
     *
     * This method constructs and sends a POST request to the OAuth2 token endpoint using
     * client credentials to obtain an access token. The access token is then returned in the format
     * "token_type access_token".
     *
     * @return string The access token in the format "token_type access_token".
     * @since  1.0.0
     */
    protected function getAccessToken()
    {
        $accessToken = '';

        $requestData = (object)[
            'method'  => 'post',
            'url'     => $this->config->get('api_url') . '/v1/oauth2/token',
            'options' => [
                'auth'        => [$this->config->get('client_id'), $this->config->get('client_secret')],
                'headers'     => ["Content-Type" =>  "application/x-www-form-urlencoded"],
                'form_params' => ['grant_type' => 'client_credentials']
            ]
        ];

        $response = $this->sendHttpRequest($requestData);

        if ($response->token_type && $response->access_token) {
            $accessToken = "{$response->token_type}  {$response->access_token}";
        } 

        return $accessToken;
    }

    /**
     * Retrieves the checkout URL for an order.
     *
     * This method constructs and sends a POST request to the PayPal Orders API endpoint to create
     * an order. It then retrieves the checkout URL from the response, which can be used for redirecting
     * the payer to PayPal for payment authorization.
     *
     * @param  string       $accessToken The access token for authenticating the API request.
     * @return string|null               The checkout URL if available, otherwise null.
     * @since  1.0.0
     */
    protected function getCheckoutURL($accessToken)
    {
        $headers = [
            'Content-Type'      => 'application/json',
            'Authorization'     => $accessToken,
            'PayPal-Request-Id' => "order-id-" . $this->orderID
        ];

        $requestData = (object)[
            'method'  => 'post',
            'url'     => $this->config->get('api_url') . '/v2/checkout/orders',
            'options' => [
                'headers' => $headers,
                'body'    => json_encode($this->getData())
            ]
        ];

        $responseData = $this->sendHttpRequest($requestData);

        return isset($responseData->links) ? $this->getUrl($responseData->links, 'payer-action') : null;
    }

    /**
     * Verifies the webhook signature and processes the order data.
     *
     * This method verifies the webhook signature from the payload. If the signature is valid,
     * it processes the approved order. In case of an error, it handles the error
     * response and sets the return data accordingly.
     *
     * @param  object $payload  The payload object containing the webhook data.
     * @return object           Returns the processed order data or an error response.
     * @throws RequestException if the request fails.
     * @since  1.0.0
     */
    public function verifyAndCreateOrderData(object $payload): object
    {
        try {
                      
            $this->webhookSignatureValidation($payload);
            
            return  $this->processApprovedOrder(json_decode($payload->stream));

        } catch (RequestException $error) {
            
            // Handle the error response
            $errorMessage = $this->handleErrorResponse($error) ?? $error->getMessage();
            return $this->setReturnData(json_decode($payload->stream), $errorMessage);
        }
    }

    /**
     * Validates the webhook signature from the PayPal payload.
     *
     * This method validates the webhook signature provided in the PayPal payload to ensure
     * the request is authentic. It constructs the data to be verified, decodes the signature,
     * retrieves the public key from the provided certificate URL, and verifies the signature using
     * OpenSSL. If the signature is invalid, it throws an InvalidSignatureException.
     *
     * @param  object $payload           The payload object containing the webhook data and headers.
     * @return int                       Returns 1 if the signature is valid.
     * @throws InvalidSignatureException if the signature is invalid.
     * @since  1.0.0
     */
    private function webhookSignatureValidation($payload)
    {
        $transmissionID        = $payload->server['HTTP_PAYPAL_TRANSMISSION_ID'];
        $transmissionSignature = $payload->server['HTTP_PAYPAL_TRANSMISSION_SIG'];
        $transmissionTime      = $payload->server['HTTP_PAYPAL_TRANSMISSION_TIME'];
        $rawPayload            = $payload->stream;
        $certUrl               = $payload->server['HTTP_PAYPAL_CERT_URL'];
        $webhookID             = $this->config->get('webhook_id');
        
        //<transmissionId>|<timeStamp>|<webhookId>|<crc32>
        $data      = implode('|', [$transmissionID, $transmissionTime,$webhookID ,crc32($rawPayload)]);
        
        $signature = base64_decode($transmissionSignature);
        $publicKey = openssl_pkey_get_public(file_get_contents($certUrl));
        
        $checkValidation = openssl_verify($data, $signature, $publicKey, 'sha256WithRSAEncryption');

        if (!$checkValidation) {
           throw new InvalidSignatureException("Invalid Signature");
        }

        return $checkValidation;
    }

    /**
     * Captures a payment for an authorized PayPal order.
     *
     * This method constructs the capture payment URL and headers, including the access token,
     * PayPal request ID. It then sends a POST request to the capture payment URL using the specified headers.
     *
     * @param  object $payloadStream The payload stream sent from Paypal Webhook Notification.
     * @return object                The response object from the capture payment request.
     * @since  1.0.0
     */
    private function capturePayment($payloadStream)
    {
        $links             = $payloadStream->resource->links;
        $capturePaymentUrl = $this->getUrl($links, 'capture');
        $accessToken       = $this->getAccessToken(); 

        $headers = [
            'Content-Type'      => 'application/json',
            'Authorization'     => $accessToken ,
            'PayPal-Request-Id' => "paypal-order-id-" . $payloadStream->resource->id,
            'Prefer'            => 'return=representation',
        ];

        $requestData = (object)[
            'method'  => 'post',
            'url'     => $capturePaymentUrl,
            'options' => ['headers' =>  $headers]
        ];    

        return $this->sendHttpRequest($requestData);
    }

    /**
     * Retrieves the URL from an array of links that matches a specified condition.
     *
     * This method filters the provided links array to find the link that matches the given
     * condition. If a matching link is found, it returns the href property of that link.
     *
     * @param  array        $links     The array of links to search through.
     * @param  string       $condition The condition to match against the rel property of the links.
     * @return string|null             The URL if a matching link is found, otherwise null.
     * @since  1.0.0
     */
    private function getUrl($links,$condition)
    {
        $url = array_filter($links, function ($link) use ($condition) {
            return $link->rel === $condition;
        });

        return $url ? reset($url)->href : null;
    }

    /**
     * Processes an approved PayPal order and captures the payment.
     *
     * This method checks if the order is approved by calling the `isOrderApproved` method.
     * If the order is approved, it captures the payment using the capturePayment method,
     * and sets the return data using the setReturnData method.
     *
     * @param  object        $payloadStream The payload stream containing the payment data.
     * @return object|null                  The return data object if the order is approved, otherwise null.
     * @since  1.0.0
     */
    private function processApprovedOrder($payloadStream)
    {       
        return $this->isOrderApproved($payloadStream) ? $this->setReturnData($this->capturePayment($payloadStream)) : null;
    }

    /**
     * Handles the error response from an HTTP request and formats a user-friendly error message.
     *
     * This method extracts error details from the response body and constructs a comprehensive
     * error message. It processes different parts of the error response, including issues,
     * details, and general error messages, and combines them into a single message string.
     *
     * @param  RequestException $errorResponse The error response from the HTTP request.
     * @return string                          The formatted error message.
     * @since  1.0.0
     */
    private function handleErrorResponse($errorResponse)
    {
        $errorBody = json_decode($errorResponse->getResponse()->getBody());
        $message   = '';

        if (!empty($errorBody->issues)) {
            $message .= $this->processIssues($errorBody->issues);
        }

        if (!empty($errorBody->details)) {
            $message .= $this->processIssues($errorBody->details);
        }

        if (!empty($errorBody->error) && !empty($errorBody->error_description)) {
            $message .= "{$errorBody->error} : {$errorBody->error_description}. ";
        }

        if (!empty($errorBody->message)) {
            $message .= "{$errorBody->name} : {$errorBody->message}. ";
        }

        return $message;
    }

    /**
     * Processes a list of issues from an error response and formats them into a single message string.
     *
     * This method iterates over the provided issues, extracting relevant information such as
     * the issue type, description, and associated fields. It constructs a detailed message string
     * containing all the extracted information.
     *
     * @param  array  $issues The list of issues from the error response.
     * @return string         The formatted message string containing details of all issues.
     * @since  1.0.0
     */
    private function processIssues($issues)
    {
        $finalMessage = array_reduce($issues, function ($message, $issue) {
            
            if (!empty($issue->issue)) {
                $message .= "Issue: {$issue->issue}. ";
            }

            if (!empty($issue->description)) {
                $message .= "Description: {$issue->description}. ";
            }

            if (!empty($issue->fields)) {
                foreach ($issue->fields as $field) {
                    if (!empty($field->field)) {
                        $message .= "Field: {$field->field}. ";
                    }
                }
            }

            return $message;
        }, '');

        return $finalMessage;
    }

    /**
     * Sets and returns order data based on the processing result and error message.
     *
     * Constructs order data based on the provided payload stream and optionally an error message.
     * If an error message is provided, sets the order data with failure status and error details.
     * Otherwise, sets the order data with successful transaction details from the payload stream.
     *
     * @param  object      $payloadStream The payload stream object containing order and payment details.
     * @param  string|null $errorMessage  Optional. Error message to indicate payment failure reason.
     * @return object                     The constructed order data object.
     * @since  1.0.0
     */
    private function setReturnData($payloadStream, $errorMessage = null)
    {
        $returnData = System::defaultOrderData();

        if ($errorMessage) {          
            $returnData->id                   = $payloadStream->resource->purchase_units[0]->custom_id;
            $returnData->payment_status       = 'failed';
            $returnData->payment_payload      = json_encode($payloadStream);
            $returnData->payment_method       = $this->config->get('name');
            $returnData->payment_error_reason = $errorMessage;
            
        } else {        
            $transactionInfo             = $payloadStream->purchase_units[0]->payments->captures[0];
            $returnData->id              = $transactionInfo->custom_id;
            $returnData->payment_status  = $transactionInfo->status;
            $returnData->transaction_id  = $transactionInfo->id;
            $returnData->payment_payload = json_encode($payloadStream);
            $returnData->payment_method  = $this->config->get('name');
        }

        return $returnData;
    }

    /**
     * Checks if the order in the payload stream is approved for capture.
     *
     * Determines if the event type is `CHECKOUT.ORDER.APPROVED`, intent is `CAPTURE`,
     * and status is `APPROVED` in the provided payload stream.
     *
     * @param  object $payloadStream    The payload stream object containing payment details.
     * @return bool                     Returns true if the order is approved for capture, false otherwise.
     * @since  1.0.0
     */
    private function isOrderApproved($payloadStream)
    {
        return  $payloadStream->event_type === 'CHECKOUT.ORDER.APPROVED' && 
                $payloadStream->resource->intent === 'CAPTURE' && 
                $payloadStream->resource->status === 'APPROVED';
    }

    /**
     * Sends an HTTP request using the specified method, URL, and options.
     *
     * Makes an HTTP request using the method (GET, POST, etc.), URL, and additional options
     * provided in the requestData object. Returns the JSON-decoded response body.
     *
     * @param  object $requestData The request data object containing method, URL, and options.
     * @return mixed               Returns the JSON-decoded response body.
     * @since  1.0.0
     */
    private function sendHttpRequest($requestData)
    {
        $http       = new Client();
        $method     = $requestData->method;
        $requestUrl = $requestData->url;
        $response   = $http->$method($requestUrl, $requestData->options);

        return json_decode($response->getBody());
    }

    /**
     * Retrieves and formats items from the provided data.
     *
     * @param  object $data The data object containing item details.
     * @return array        An array of formatted items.
     * @since  1.0.0
     */
    private function getItems($data) : array
    {
        $currency = $data['currency']->code;

        return array_map(function($item) use ($currency) {

            $price = $item['discounted_price'] > 0 ? $item['discounted_price'] : $item['regular_price'];
            
            return [
                'name'          => $item['item_name'],
                'quantity'      => (string) $item['quantity'],
                'image_url'     => $item['image'] ? Path::clean($item['image']) : null,
                'unit_amount'   => [
                    'currency_code' => $currency,
                    'value'         => (string) $price
                ]
            ];
        }, (array) $data['items']);
    }
}