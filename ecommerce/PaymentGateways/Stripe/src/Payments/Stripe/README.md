
## Payment-Hub

This documentation outlines the structure of the payment data object and implementation procedure of our system. 

The following example demonstrates the process for `Stripe` payments. 


```
$payment = (new PaymentHub(Stripe::class, Config::class))->make();

$payment->setData((object) [
    'items' => (object) [
        [
            'item_id' => 1,
            'item_name' => 'Sample Product 1',
            'regular_price' => 100.00,
            'regular_price_in_smallest_unit' => 10000,
            'quantity' => 3,
            'discounted_price' => 90.00,
            'discounted_price_in_smallest_unit' => 9000,
            'image' => 'https://example.com/image.jpg',
        ],
        [
            'item_id' => 2,
            'item_name' => 'Sample Product 2',
            'regular_price' => 300.00,
            'regular_price_in_smallest_unit' => 30000,
            'quantity' => 1,
            'discounted_price' => 0,
            'discounted_price_in_smallest_unit' => 0,
            'image' => 'https://example.com/image.jpg',
        ],
    ],
    'subtotal' => 570.00,
    'subtotal_in_smallest_unit' => 57000,
    'total_price' => 550.00,
    'total_price_in_smallest_unit' => 55000,
    'order_id' => '67999756',
    'store_name' => 'Sample Store Name',
    'order_description' => 'Sample Order Description',
    'tax' => 230.00,
    'tax_in_smallest_unit' => 23000,
    'currency' => (object) [
        "code" => "USD",
		"symbol" => "$",
		"name" => "US Dollar",
		"locale" => "en-us",
		"numeric_code" => 840
    ],
    'country' => (object) [
        "name" => "United States",
        "numeric_code" => "840",
        "alpha_2" => "US",
        "alpha_3" => "USA",
    ],
    'shipping_charge' => 150.00,
    'shipping_charge_in_smallest_unit' => 15000,
    'coupon_discount' => 400.00,
    'coupon_discount_amount_in_smallest_unit' => 40000,
    'shipping_address' => (object) [
        'name' => 'John Doe',
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'Manhattan',
        'region' => 'NY',
        'postal_code' => '10001',
        'country' => (object) [
            "name" => "United States",
            "numeric_code" => "840",
            "alpha_2" => "US",
            "alpha_3" => "USA",
        ],
        'phone_number' => '123-456-7890',
        'email' => 'john@smith.com',
    ],
    'billing_address' => (object) [
        'name' => 'John Doe',
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'Manhattan',
        'region' => 'NY',
        'postal_code' => '10001',
        'country' => (object) [
            "name" => "United States",
            "numeric_code" => "840",
            "alpha_2" => "US",
            "alpha_3" => "USA",
        ],
        'phone_number' => '+234 201 098 7654',
        'email' => 'john@smith.com',
    ],
    'decimal_separator' => '.',
    'thousand_separator' => ',',
    'customer' => (object) [
        'name' => 'John Doe',
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'Manhattan',
        'region' => 'NY',
        'postal_code' => '10001',
        'country' => (object) [
            "name" => "United States",
            "numeric_code" => "840",
            "alpha_2" => "US",
            "alpha_3" => "USA",
        ],
        'phone_number' => '0823456789',
        'email' => 'john@smith.com',
    ],
]);

$payment->createPayment();
```

## Initialization:

``` $payment = (new PaymentHub(Stripe::class, Config::class))->make(); ```

Initializes the payment gateway using `PaymentHub` and `Config` class.

### Set Payment Data:

```$payment->setData((object)[...]);```

### Create Payment:

```$payment->createPayment();``` 

Create a payment and redirect user to checkout page.


## Data Structure

The payment data is structured as follows:

### Items

An array of objects, each representing an item in the order:

- `item_id`: Unique identifier for the item
- `item_name`: Name of the item
- `regular_price`: Regular price of the item. Data type `float`. Upto 2 decimal.
- `regular_price_in_smallest_unit`: Regular price in the smallest currency unit. Data type `int`.
- `quantity`: Quantity of the item. Data type `int`.
- `discounted_price`: If the item has a discounted price, otherwise, set the price to `0`. Data type `float`. Upto 2 decimal.
- `discounted_price_in_smallest_unit`: Discounted price in the smallest currency unit. Data type `int`.
- `image`: URL of the item's image.

### Order Details

- `subtotal`: Sum of all items. Data type `float`. Upto 2 decimal.
- `subtotal_in_smallest_unit`: Subtotal in the smallest currency unit.  Data type `int`.
- `total_price`: Final price including all charges and discounts. Data type `float`. Upto 2 decimal.
- `total_price_in_smallest_unit`: Total price in the smallest currency unit. Data type `int`.
- `order_id`: Unique identifier for the order.  Data type `string`.
- `store_name`: Name of the store.
- `order_description`: Description of the order

### Pricing Components

- `tax`: Amount of tax. Data type `float`. Upto 2 decimal. Set the value to `0` if no tax is applicable.
- `tax_in_smallest_unit`: Tax amount in the smallest currency unit. Data type `int`. Set the value to `0` if no tax is applicable.
- `shipping_charge`: Shipping fee. Data type `float`. Upto 2 decimal. Set the value to `0` if no shipping charge is applicable.
- `shipping_charge_in_smallest_unit`: Shipping fee in the smallest currency unit. Data type `int`. Set the value to `0` if no Shipping charge is applicable.
- `coupon_discount`: Discount amount from coupons. Data type `float`. Upto 2 decimal. Set the value to `0` if no coupon discount is applicable.
- `coupon_discount_amount_in_smallest_unit`: Coupon discount in the smallest currency unit. Data type `int`. Set the value to `0` if no Coupon discount is applicable.

### Currency Information

Object containing details about the currency used:

- `code`: Currency code (e.g., "USD")
- `symbol`: Currency symbol (e.g., "$")
- `name`: Full name of the currency
- `locale`: Locale code for the currency
- `numeric_code`: Numeric code for the currency

### Country Information

Object containing details about the country:

- `name`: Full name of the country
- `numeric_code`: Numeric code for the country
- `alpha_2`: Two-letter country code
- `alpha_3`: Three-letter country code

### Address Information

Both shipping and billing addresses are included, containing:

- `name`: Full name of the recipient
- `address1`: Primary address line
- `address2`: Secondary address line (if applicable)
- `city`: City name
- `state`: State or province
- `region`: Region code
- `postal_code`: Postal or ZIP code
- `country`: Object with country details (same structure as `Country` Information)
- `phone_number`: Contact phone number
- `email`: Contact email address

### Customer Information

Contains the same fields as the address information, representing the customer's details.

### Formatting

- `decimal_separator`: Character used as decimal separator
- `thousand_separator`: Character used as thousand separator


### Webhook

```
$webhookData = (object)[
    'get'    => $_GET,
    'post'   => $_POST,
    'server' => $_SERVER,
    'stream' => file_get_contents('php://input')
];

$payment = (new PaymentHub(Stripe::class, Config::class))->make();
$payment->verifyAndCreateOrderData($webhookData);

```

Call this method at the endpoint where the webhook notifications will be received.

This script handles webhook notifications from the payment gateway. It collects the webhook request data, verifies the payment status, and creates or updates the order data based on the webhook information.

## Webhook Data Collection:

- **$_GET**: Collects GET parameters from the request.
- **$_POST**: Collects POST parameters from the request.
- **$_SERVER**: Collects SERVER parameters from the request.
- **file_get_contents('php://input')**: Retrieves the raw input stream of the request.

The `verifyAndCreateOrderData` method processes the webhook data and retrieves and verifies the payment details using the provided payload. 

It throws an error if any error occurs during this process.

If the process is successful, it returns an object containing the `order_id`, `transaction_id`, `payment_status`, `payment_method`, `redirectUrl` (Either success or cancel Url. Based on the payment status) and `payment_payload`(JSON-encoded string). 


### Recurring Payment

```
$payment = (new PaymentHub(Stripe::class, Config::class))->make();

$payment->setData((object)[
    'type' => 'recurring',
    'previous_payload' => '', // JSON string representing the previous payment payload.
    'amount' => 10,
    'amount_in_smallest_unit' => 1000,
    'currency' => (object) [
        "code" => "USD",
        "symbol" => "$",
        "name" => "US Dollar",
        "locale" => "en-us",
        "numeric_code" => 840,
    ],
    'order_id' => '6968754',
    'customer' => (object) [
        'name' => 'John Doe',
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'Manhattan',
        'region' => 'NY',
        'postal_code' => '10001',
        'country' => (object) [
            "name" => "United States",
            "numeric_code" => "840",
            "alpha_2" => "US",
            "alpha_3" => "USA",
        ],
        'phone_number' => '0823456789',
        'email' => 'john@smith.com',
    ],
    'shipping_address' => (object) [
        'name' => 'John Doe',
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'Manhattan',
        'region' => 'NY',
        'postal_code' => '10001',
        'country' => (object) [
            "name" => "United States",
            "numeric_code" => "840",
            "alpha_2" => "US",
            "alpha_3" => "USA",
        ],
        'phone_number' => '123-456-7890',
        'email' => 'john@smith.com',
    ],
]);

$payment->createRecurringPayment();
```

### Data Structure

- `type`: Specifies the payment type, which is set to 'recurring'.
- `previous_payload`: Contains a JSON string representing the details of the previous payment payload.
- `amount`: The payment amount for the new transaction. Data type `float`. Upto 2 decimal.
- `amount_in_smallest_unit` : The payment amount in the smallest currency unit. Data type `int`.
- `currency`: Contains the same fields as the `Currency Information`.
- `order_id`: Unique identifier for the order. Data type `string`.
- `customer`: Contains the same fields as the `Customer Information`.
- `shipping_address`: Contains the same fields as the `Address Information`

### Create Recurring Payment:

```$payment->createRecurringPayment();``` 

Create a Recurring Payment.
