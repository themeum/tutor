import { useToast } from '@Atoms/Toast';
import { tutorConfig } from '@Config/config';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation, useQuery } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Option } from '@Utils/types';

export interface PaymentField {
  name: string;
  label: string;
  type: 'select' | 'text' | 'secret_key' | 'textarea' | 'image' | 'webhook_url';
  options?: Option<string>[] | Record<string, string>;
  hint?: string;
  value: any;
}

export interface PaymentMethod {
  name: string;
  label: string;
  is_active: boolean;
  icon: string;
  support_subscription: boolean;
  update_available: boolean;
  is_installable?: boolean;
  is_manual?: boolean;
  fields: PaymentField[];
}

export interface PaymentSettings {
  payment_methods: PaymentMethod[];
}

export const getWebhookUrl = (gateway: string) => {
  return `${tutorConfig.home_url}/wp-json/tutor/v1/ecommerce-webhook?payment_method=${gateway}`;
};

export const initialPaymentSettings: PaymentSettings = {
  payment_methods: [
    {
      name: 'paypal',
      label: __('Paypal', 'tutor'),
      is_active: false,
      icon: `${tutorConfig.tutor_url}assets/images/paypal.svg`,
      support_subscription: true,
      update_available: false,
      is_manual: false,
      fields: [
        {
          name: 'environment',
          label: __('PyPal Environment', 'tutor'),
          type: 'select',
          options: [
            {
              label: __('Test', 'tutor'),
              value: 'test',
            },
            {
              label: __('Live', 'tutor'),
              value: 'live',
            },
          ],
          value: 'test',
        },
        {
          name: 'merchant_email',
          label: __('Merchant Email', 'tutor'),
          type: 'text',
          value: '',
        },
        {
          name: 'client_id',
          label: __('Client ID', 'tutor'),
          type: 'text',
          value: '',
        },
        {
          name: 'secret_id',
          label: __('Secret ID', 'tutor'),
          type: 'secret_key',
          value: '',
        },
        {
          name: 'webhook_id',
          label: __('Webhook ID', 'tutor'),
          type: 'secret_key',
          value: '',
        },
        {
          name: 'webhook_url',
          label: __('Webhook URL', 'tutor'),
          type: 'webhook_url',
          value: getWebhookUrl('paypal'),
        },
      ],
    },
  ],
};

export const convertPaymentMethods = (methods: PaymentMethod[], gateways: PaymentGateway[]) => {
  let isModified = false;
  let updatedMethods = [...methods];

  if (gateways.length === 0) {
    const filteredMethods = updatedMethods.filter((method) => !Object.hasOwn(method, 'is_installable'));
    if (filteredMethods.length !== updatedMethods.length) {
      isModified = true;
    }
    return { methods: filteredMethods, isModified };
  }

  gateways.forEach((gateway) => {
    const existingMethod = updatedMethods.find((method) => method.name === gateway.name);

    // Append new method if gateway is installed but not found in methods
    if (gateway.is_installed && !existingMethod) {
      updatedMethods.push(gateway);
      isModified = true;
    }

    // Remove method if gateway is not installed but found in methods
    if (!gateway.is_installed && existingMethod) {
      updatedMethods = updatedMethods.filter((method) => method.name !== gateway.name);
      isModified = true;
    }

    // Update existing method if gateway is installed and update is available
    if (gateway.is_installed && gateway.update_available && existingMethod && !existingMethod.update_available) {
      updatedMethods = updatedMethods.map((method) =>
        method.name === gateway.name ? { ...method, update_available: true } : method
      );
      isModified = true;
    }

    // Update existing method if gateway is installed and update is not available
    if (gateway.is_installed && !gateway.update_available && existingMethod && existingMethod.update_available) {
      updatedMethods = updatedMethods.map((method) =>
        method.name === gateway.name ? { ...method, update_available: false } : method
      );
      isModified = true;
    }
  });

  return { methods: updatedMethods, isModified };
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
  is_active: boolean;
  is_installed: boolean;
  support_subscription: boolean;
  is_installable: boolean;
  update_available: boolean;
  fields: PaymentField[];
}

interface ErrorResponse {
  response: {
    data: {
      status_code: number;
      message: string;
    };
  };
}

const getPaymentGateways = () => {
  return wpAjaxInstance.get<PaymentGateway[]>(endpoints.GET_PAYMENT_GATEWAYS).then((response) => response.data);
};

export const usePaymentGatewaysQuery = () => {
  return useQuery<PaymentGateway[], ErrorResponse>({
    queryKey: ['PaymentGateways'],
    queryFn: getPaymentGateways,
  });
};

interface PaymentResponse {
  status_code: number;
  message: string;
  data: PaymentMethod | null;
}

interface PaymentPayload {
  slug: string;
  action_type?: string;
}

const installPayment = (payload: PaymentPayload) => {
  return wpAjaxInstance.post<PaymentPayload, PaymentResponse>(endpoints.INSTALL_PAYMENT_GATEWAY, {
    ...payload,
  });
};

export const useInstallPaymentMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: installPayment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

const removePayment = (payload: PaymentPayload) => {
  return wpAjaxInstance.post<PaymentPayload, PaymentResponse>(endpoints.REMOVE_PAYMENT_GATEWAY, {
    ...payload,
  });
};

export const useRemovePaymentMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: removePayment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
