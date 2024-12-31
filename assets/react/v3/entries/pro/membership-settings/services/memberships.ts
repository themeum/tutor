import { useToast } from '@/v3/shared/atoms/Toast';
import { DateFormats } from '@/v3/shared/config/constants';
import { type ErrorResponse } from '@/v3/shared/utils/form';
import { type TutorMutationResponse } from '@/v3/shared/utils/types';
import { convertGMTtoLocalDate, convertToErrorMessage, convertToGMT } from '@/v3/shared/utils/util';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { format } from 'date-fns';
import { type Feature } from '../components/fields/FormFeatureItem';

export interface MembershipPlan {
  id: string;
  is_enabled: boolean;
  plan_name: string | null;
  plan_order: string;
  plan_type: 'full_site' | 'category';
  categories: number[];
  payment_type: string;
  short_description: string | null;
  description: string | null;
  provide_certificate: '0' | '1';
  recurring_interval: string;
  recurring_limit: string;
  recurring_value: string;
  regular_price: string;
  sale_price: string | null;
  sale_price_from: string | null;
  sale_price_to: string | null;
  enrollment_fee: string | null;
  is_featured: '0' | '1';
  featured_text: string | null;
}

export interface MembershipSettings {
  plans: MembershipPlan[];
}

export interface MembershipFormData {
  id?: string;
  plan_name: string;
  plan_type: 'full_site' | 'category';
  short_description: string;
  features: Feature[];
  categories: number[];
  recurring_value: string;
  recurring_interval: string;
  recurring_limit: string;
  regular_price: string;
  offer_sale_price: boolean;
  sale_price: string;
  schedule_sale_price: boolean;
  sale_price_from_date: string;
  sale_price_from_time: string;
  sale_price_to_date: string;
  sale_price_to_time: string;
  charge_enrollment_fee: boolean;
  enrollment_fee: string;
  do_not_provide_certificate: boolean;
  is_featured: boolean;
  featured_text: string;
}

export const defaultValues: MembershipFormData = {
  plan_name: '',
  plan_type: 'full_site',
  short_description: '',
  features: [],
  categories: [],
  recurring_value: '',
  recurring_interval: '',
  recurring_limit: '',
  regular_price: '',
  offer_sale_price: false,
  sale_price: '',
  schedule_sale_price: false,
  sale_price_from_date: '',
  sale_price_from_time: '',
  sale_price_to_date: '',
  sale_price_to_time: '',
  charge_enrollment_fee: false,
  enrollment_fee: '',
  do_not_provide_certificate: false,
  is_featured: false,
  featured_text: '',
};

export type DurationUnit = 'hour' | 'day' | 'week' | 'month' | 'year';

export type MembershipPayload = {
  id?: string; // only for update
  plan_name: string;
  short_description: string;
  description: string;
  plan_type: 'category' | 'full_site';
  cat_ids?: number[];
  recurring_value: string;
  recurring_interval: Omit<DurationUnit, 'hour'>;
  recurring_limit: string; // 0 for until canceled
  regular_price: string;
  sale_price?: string;
  sale_price_from?: string; // start date
  sale_price_to?: string; // end date
  enrollment_fee?: string;
  provide_certificate: '0' | '1';
  is_featured: '0' | '1';
  featured_text: string;
};

export const convertPlanToFormData = (plan: MembershipPlan): MembershipFormData => {
  return {
    id: plan.id,
    plan_name: plan.plan_name ?? '',
    short_description: plan.short_description ?? '',
    regular_price: plan.regular_price ?? '0',
    plan_type: plan.plan_type ?? 'course',
    categories: plan.categories ?? [],
    recurring_value: plan.recurring_value ?? '0',
    recurring_interval: plan.recurring_interval ?? 'month',
    recurring_limit: plan.recurring_limit === '0' ? 'Until cancelled' : plan.recurring_limit || '',
    features: plan.description ? (JSON.parse(plan.description) as Feature[]) : [],
    charge_enrollment_fee: !!Number(plan.enrollment_fee),
    enrollment_fee: plan.enrollment_fee ?? '0',
    do_not_provide_certificate: !Number(plan.provide_certificate),
    is_featured: !!Number(plan.is_featured),
    featured_text: plan.featured_text ?? '',
    offer_sale_price: !!Number(plan.sale_price),
    sale_price: plan.sale_price ?? '0',
    schedule_sale_price: !!plan.sale_price_from,
    sale_price_from_date: plan.sale_price_from
      ? format(convertGMTtoLocalDate(plan.sale_price_from), DateFormats.yearMonthDay)
      : '',
    sale_price_from_time: plan.sale_price_from
      ? format(convertGMTtoLocalDate(plan.sale_price_from), DateFormats.hoursMinutes)
      : '',
    sale_price_to_date: plan.sale_price_to
      ? format(convertGMTtoLocalDate(plan.sale_price_to), DateFormats.yearMonthDay)
      : '',
    sale_price_to_time: plan.sale_price_to
      ? format(convertGMTtoLocalDate(plan.sale_price_to), DateFormats.hoursMinutes)
      : '',
  };
};

export const convertFormDataToPayload = (formData: MembershipFormData): MembershipPayload => {
  return {
    ...(formData.id && String(formData.id) !== '0' && { id: formData.id }),
    plan_name: formData.plan_name,
    short_description: formData.short_description,
    description: JSON.stringify(formData.features),
    plan_type: formData.plan_type,
    ...(formData.plan_type === 'category' && { cat_ids: formData.categories }),
    regular_price: formData.regular_price,
    recurring_value: formData.recurring_value,
    recurring_interval: formData.recurring_interval,
    recurring_limit: formData.recurring_limit === 'Until cancelled' ? '0' : formData.recurring_limit,
    is_featured: formData.is_featured ? '1' : '0',
    featured_text: formData.featured_text,
    ...(formData.charge_enrollment_fee && { enrollment_fee: formData.enrollment_fee }),
    sale_price: formData.offer_sale_price ? formData.sale_price : '0',
    ...(formData.schedule_sale_price && {
      sale_price_from: convertToGMT(new Date(`${formData.sale_price_from_date} ${formData.sale_price_from_time}`)),
      sale_price_to: convertToGMT(new Date(`${formData.sale_price_to_date} ${formData.sale_price_to_time}`)),
    }),
    provide_certificate: formData.do_not_provide_certificate ? '0' : '1',
  };
};

const getMembershipSettings = () => {
  return wpAjaxInstance.get<MembershipPlan[]>(endpoints.GET_MEMBERSHIP_PLANS).then((response) => response.data);
};

export const useMembershipSettingsQuery = () => {
  return useQuery({
    queryKey: ['MembershipSettings'],
    queryFn: getMembershipSettings,
  });
};

const saveMembershipPlan = (payload: MembershipPayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<string>>(endpoints.SAVE_MEMBERSHIP_PLAN, {
    ...(payload.id && { id: payload.id }),
    ...payload,
  });
};

export const useSaveMembershipPlanMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveMembershipPlan,
    onSuccess: (response) => {
      if (response.status_code === 200 || response.status_code === 201) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['MembershipSettings'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const deleteMembershipPlan = (id: string) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<string>>(endpoints.DELETE_MEMBERSHIP_PLAN, { id });
};

export const useDeleteMembershipPlanMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteMembershipPlan,
    onSuccess: (response) => {
      if (response.status_code === 200) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['MembershipSettings'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
