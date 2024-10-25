import { useToast } from '@Atoms/Toast';
import { tutorConfig } from '@Config/config';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Option } from '@Utils/types';
import { convertToErrorMessage } from '../../course-builder/utils/utils';

export interface PaymentField {
  name: string;
  label?: string;
  type?: 'select' | 'text' | 'secret_key' | 'textarea' | 'image' | 'webhook_url';
  options?: Option<string>[] | Record<string, string>;
  hint?: string;
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  value: any;
}

export interface PaymentMethod {
  name: string;
  label: string;
  is_active: boolean;
  icon: string;
  support_subscription: boolean;
  update_available?: boolean;
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
  payment_methods: [],
};

export const manualMethodFields: PaymentField[] = [
  {
    name: 'method_name',
    label: __('Title', 'tutor'),
    type: 'text',
    value: '',
  },
  {
    name: 'icon',
    label: __('Icon', 'tutor'),
    type: 'image',
    value: {
      id: 0,
      url: '',
      title: '',
    },
  },
  {
    name: 'payment_instructions',
    label: __('Payment Instructions', 'tutor'),
    type: 'textarea',
    hint: __('Provide clear, step-by-step instructions on how to complete the payment.', 'tutor'),
    value: '',
  },
];

export const convertPaymentMethods = (methods: PaymentMethod[], gateways: PaymentGateway[]) => {
  const gatewayMap = new Map(gateways.map((gateway) => [gateway.name, gateway]));

  // Update methods with data from gateways api
  const updatedMethods = methods.map((method) => {
    const gateway = gatewayMap.get(method.name);
    return gateway ? { ...gateway, is_active: method.is_active, fields: method.fields } : method;
  });

  // Add any new methods from installed gateways that are not already in methods
  gatewayMap.forEach((gateway) => {
    if (gateway.is_installed && !updatedMethods.some((method) => method.name === gateway.name)) {
      updatedMethods.push({ ...gateway, fields: gateway.fields.map(({ name, value }) => ({ name, value })) });
    }
  });

  return updatedMethods;
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
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: installPayment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
      queryClient.invalidateQueries({
        queryKey: ['PaymentGateways'],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: removePayment,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
      queryClient.invalidateQueries({
        queryKey: ['PaymentGateways'],
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
