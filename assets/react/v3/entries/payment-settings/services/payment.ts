import { wpAjaxInstance } from '@/v3/shared/utils/api';
import endpoints from '@/v3/shared/utils/endpoints';
import { useQuery } from '@tanstack/react-query';

export interface PaymentField {
  name: string;
  label: string;
  type: 'select' | 'text' | 'key' | 'textarea' | 'webhook_url';
  options?: { label: string; value: string }[];
  hint?: string;
  value: string;
}

export interface PaymentMethod {
  name: string;
  label: string;
  isActive: boolean;
  icon: string;
  support_recurring: boolean;
  update_available: boolean;
  is_manual: boolean;
  fields: PaymentField[];
}

export interface PaymentSettings {
  payment_methods: PaymentMethod[];
}

const dummyPaymentSettings: PaymentSettings = {
  payment_methods: [
    {
      name: 'paypal',
      label: 'Paypal',
      isActive: true,
      icon: 'http://localhost:10003/wp-content/plugins/tutor/assets/images/paypal.svg',
      support_recurring: true,
      update_available: true,
      is_manual: false,
      fields: [
        {
          name: 'environment',
          label: 'PyPal Environment',
          type: 'select',
          options: [
            {
              label: 'Test',
              value: 'test',
            },
            {
              label: 'Live',
              value: 'live',
            },
          ],
          value: 'test',
        },
        {
          name: 'merchant_email',
          label: 'Merchant Email',
          type: 'text',
          value: '',
        },
        {
          name: 'client_id',
          label: 'Client ID',
          type: 'text',
          value: '',
        },
        {
          name: 'secret_id',
          label: 'Secret ID',
          type: 'key',
          value: '',
        },
        {
          name: 'webhook_id',
          label: 'Webhook ID',
          type: 'key',
          value: '',
        },
        {
          name: 'webhook_url',
          label: 'Webhook URL',
          type: 'webhook_url',
          value: 'http://localhost:10003/wp-json/tutor/v1/ecommerce-webhook?payment_method=paypal',
        },
      ],
    },
    {
      name: 'stripe',
      label: 'Stripe',
      isActive: false,
      icon: 'http://localhost:10003/wp-content/plugins/tutor-pro/assets/images/payment-gateways/stripe.svg',
      support_recurring: false,
      update_available: false,
      is_manual: false,
      fields: [
        {
          name: 'environment',
          label: 'Stripe Environment',
          type: 'select',
          options: [
            {
              label: 'Test',
              value: 'test',
            },
            {
              label: 'Live',
              value: 'live',
            },
          ],
          value: 'test',
        },
        {
          name: 'stripe_secrete_key',
          label: 'Stripe secret key',
          type: 'key',
          value: '',
        },
        {
          name: 'stripe_public_key',
          label: 'Stripe public key',
          type: 'key',
          value: '',
        },
        {
          name: 'stripe_webhook_signature_key',
          label: 'Stripe webhook signature key',
          type: 'key',
          value: '',
        },
        {
          name: 'webhook_url',
          label: 'Webhook URL',
          type: 'webhook_url',
          value: '',
        },
      ],
    },
    {
      name: 'razorpay',
      label: 'Razorpay',
      isActive: false,
      icon: '',
      support_recurring: false,
      update_available: false,
      is_manual: false,
      fields: [
        {
          name: 'razorpay_key_id',
          label: 'Razorpay key ID',
          type: 'key',
          value: '',
        },
        {
          name: 'razorpay_key_secret',
          label: 'Razorpay key secret',
          type: 'key',
          value: '',
        },
        {
          name: 'razorpay_webhook_secret',
          label: 'Razorpay webhook secret',
          type: 'key',
          value: '',
        },
      ],
    },
    {
      name: 'bank_transfer',
      label: 'Bank Transfer',
      isActive: false,
      icon: '',
      support_recurring: false,
      update_available: false,
      is_manual: true,
      fields: [
        {
          name: 'additional_details',
          label: 'Additional details',
          type: 'textarea',
          hint: 'Displays to customers when they’re choosing a payment method.',
          value: '',
        },
        {
          name: 'payment_instructions',
          label: 'Payment instructions',
          type: 'textarea',
          hint: 'Displays to customers after they place an order with this payment method.',
          value: '',
        },
      ],
    },
  ],
};

const getPaymentSettings = () => {
  return Promise.resolve<PaymentSettings>(dummyPaymentSettings);
  // return wpAjaxInstance.get<PaymentSettings>(endpoints.GET_PAYMENT_SETTINGS).then(response => response.data);
};

export const usePaymentSettingsQuery = () => {
  return useQuery({
    queryKey: ['PaymentSettings'],
    queryFn: getPaymentSettings,
  });
};
