import { tutorConfig } from '@/v3/shared/config/config';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
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

export const initialPaymentSettings: PaymentSettings = {
  payment_methods: [
    {
      name: 'paypal',
      label: 'Paypal',
      is_active: false,
      icon: `${tutorConfig.tutor_url}assets/images/paypal.svg`,
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
          value: `${tutorConfig.home_url}/wp-json/tutor/v1/ecommerce-webhook?payment_method=paypal`,
        },
      ],
    },
  ],
};

const getPaymentSettings = () => {
  return wpAjaxInstance.get<PaymentSettings>(endpoints.GET_PAYMENT_SETTINGS).then((response) => response.data);
};

export const usePaymentSettingsQuery = () => {
  return useQuery({
    queryKey: ['PaymentSettings'],
    queryFn: getPaymentSettings,
  });
};

export interface PaymentGateway {
  name: string;
  label: string;
  icon: string;
  is_installed: boolean;
  support_recurring: boolean;
  can_install: boolean;
}

const getPaymentGateways = () => {
  return wpAjaxInstance.get<PaymentGateway[]>(endpoints.GET_PAYMENT_GATEWAYS).then((response) => response.data);
};

export const usePaymentGatewaysQuery = () => {
  return useQuery({
    queryKey: ['PaymentGateways'],
    queryFn: getPaymentGateways,
  });
};
