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
  is_active: boolean;
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
      is_active: true,
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
