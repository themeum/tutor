import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';
import { format } from 'date-fns';

import { useToast } from '@TutorShared/atoms/Toast';
import { DateFormats } from '@TutorShared/config/constants';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import { type DurationUnit, type ID, type MembershipPlan, type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertGMTtoLocalDate, convertToErrorMessage, convertToGMT } from '@TutorShared/utils/util';

type PlanType = 'course' | 'bundle' | 'category' | 'full_site';
type PaymentType = 'onetime' | 'recurring';

export const BILLING_CYCLE_PRESETS = [3, 6, 9, 12];
export const BILLING_CYCLE_CUSTOM_PRESETS = {
  untilCancelled: __('Until cancelled', __TUTOR_TEXT_DOMAIN__),
  noRenewal: __('No Renewal', __TUTOR_TEXT_DOMAIN__),
};

export type Subscription = {
  id: string;
  payment_type: PaymentType;
  plan_type: PlanType;
  assign_id: string; // course_id, category_id, or 0 for full site
  plan_name: string;
  plan_order: string;
  recurring_value: string;
  recurring_interval: Exclude<DurationUnit, 'hour'>;
  is_featured: '0' | '1';
  regular_price: string;
  sale_price: string;
  sale_price_from: string; // start date
  sale_price_to: string; // end date
  recurring_limit: string; // 0 for until canceled
  provide_certificate: '0' | '1';
  enrollment_fee: string;
  trial_value: string;
  trial_interval: DurationUnit;
  is_enabled: '0' | '1';
};

export interface SubscriptionFormData
  extends Omit<
    Subscription,
    'provide_certificate' | 'sale_price_from' | 'sale_price_to' | 'is_featured' | 'is_enabled'
  > {
  charge_enrollment_fee: boolean;
  enable_free_trial: boolean;
  offer_sale_price: boolean;
  schedule_sale_price: boolean;
  is_featured: boolean;
  is_enabled: boolean;
  do_not_provide_certificate: boolean;
  sale_price_from_date: string;
  sale_price_from_time: string;
  sale_price_to_date: string;
  sale_price_to_time: string;
}

export const defaultSubscriptionFormData: SubscriptionFormData = {
  id: '0',
  payment_type: 'recurring',
  plan_type: 'course',
  assign_id: '0',
  plan_name: '',
  plan_order: '0',
  recurring_value: '1',
  recurring_interval: 'month',
  is_featured: false,
  is_enabled: true,
  regular_price: '0',
  sale_price: '0',
  sale_price_from_date: '',
  sale_price_from_time: '',
  sale_price_to_date: '',
  sale_price_to_time: '',
  recurring_limit: __('Until cancelled', __TUTOR_TEXT_DOMAIN__),
  do_not_provide_certificate: false,
  enrollment_fee: '0',
  trial_value: '1',
  trial_interval: 'day',
  charge_enrollment_fee: false,
  enable_free_trial: false,
  offer_sale_price: false,
  schedule_sale_price: false,
};

export const convertSubscriptionToFormData = (subscription: Subscription): SubscriptionFormData => {
  const determineRecurringLimit = () => {
    if (subscription.recurring_limit === '0') {
      return BILLING_CYCLE_CUSTOM_PRESETS.untilCancelled;
    }

    if (subscription.recurring_limit === '-1') {
      return BILLING_CYCLE_CUSTOM_PRESETS.noRenewal;
    }

    return subscription.recurring_limit || '';
  };

  return {
    id: subscription.id,
    payment_type: subscription.payment_type ?? 'recurring',
    plan_type: subscription.plan_type ?? 'course',
    assign_id: subscription.assign_id,
    plan_name: subscription.plan_name ?? '',
    plan_order: subscription.plan_order ?? '0',
    recurring_value: subscription.recurring_value ?? '0',
    recurring_interval: subscription.recurring_interval ?? 'month',
    is_featured: !!Number(subscription.is_featured),
    is_enabled: !!Number(subscription.is_enabled),
    regular_price: subscription.regular_price ?? '0',
    recurring_limit: determineRecurringLimit(),
    enrollment_fee: subscription.enrollment_fee ?? '0',
    trial_value: subscription.trial_value ?? '0',
    trial_interval: subscription.trial_interval ?? 'day',
    sale_price: subscription.sale_price ?? '0',
    charge_enrollment_fee: !!Number(subscription.enrollment_fee),
    enable_free_trial: !!Number(subscription.trial_value),
    offer_sale_price: !!Number(subscription.sale_price),
    schedule_sale_price: !!subscription.sale_price_from,
    do_not_provide_certificate: !Number(subscription.provide_certificate),
    sale_price_from_date: subscription.sale_price_from
      ? format(convertGMTtoLocalDate(subscription.sale_price_from), DateFormats.yearMonthDay)
      : '',
    sale_price_from_time: subscription.sale_price_from
      ? format(convertGMTtoLocalDate(subscription.sale_price_from), DateFormats.hoursMinutes)
      : '',
    sale_price_to_date: subscription.sale_price_to
      ? format(convertGMTtoLocalDate(subscription.sale_price_to), DateFormats.yearMonthDay)
      : '',
    sale_price_to_time: subscription.sale_price_to
      ? format(convertGMTtoLocalDate(subscription.sale_price_to), DateFormats.hoursMinutes)
      : '',
  };
};

export const convertFormDataToSubscription = (formData: SubscriptionFormData): SubscriptionPayload => {
  const determineRecurringLimit = () => {
    if (formData.recurring_limit === BILLING_CYCLE_CUSTOM_PRESETS.untilCancelled) {
      return '0';
    }

    if (formData.recurring_limit === BILLING_CYCLE_CUSTOM_PRESETS.noRenewal) {
      return '-1';
    }

    return formData.recurring_limit;
  };

  return {
    ...(formData.id && String(formData.id) !== '0' && { id: formData.id }),
    payment_type: formData.payment_type,
    plan_type: formData.plan_type,
    assign_id: formData.assign_id,
    plan_name: formData.plan_name,
    ...(formData.id && String(formData.id) === '0' && { plan_order: formData.plan_order }),
    ...(formData.payment_type === 'recurring' && {
      recurring_value: formData.recurring_value,
      recurring_interval: formData.recurring_interval,
    }),
    regular_price: formData.regular_price,
    recurring_limit: determineRecurringLimit(),
    is_featured: formData.is_featured ? '1' : '0',
    is_enabled: formData.is_enabled ? '1' : '0',
    ...(formData.charge_enrollment_fee && { enrollment_fee: formData.enrollment_fee }),
    ...(formData.enable_free_trial && { trial_value: formData.trial_value, trial_interval: formData.trial_interval }),
    sale_price: formData.offer_sale_price ? formData.sale_price : '0',
    ...(formData.schedule_sale_price && {
      sale_price_from: convertToGMT(new Date(`${formData.sale_price_from_date} ${formData.sale_price_from_time}`)),
      sale_price_to: convertToGMT(new Date(`${formData.sale_price_to_date} ${formData.sale_price_to_time}`)),
    }),
    provide_certificate: formData.do_not_provide_certificate ? '0' : '1',
  };
};

export type SubscriptionPayload = {
  id?: string; // only for update
  payment_type: PaymentType;
  plan_type: PlanType;
  assign_id: string; // course_id, category_id, or 0 for full site
  plan_name: string;
  plan_order?: string; // only send when creating a new plan
  recurring_value?: string;
  recurring_interval?: Exclude<DurationUnit, 'hour'>;
  regular_price: string;
  sale_price?: string;
  sale_price_from?: string; // start date
  sale_price_to?: string; // end date
  recurring_limit: string; // 0 for until canceled or -1 for no renewal
  provide_certificate: '0' | '1';
  is_featured: '0' | '1';
  is_enabled: '0' | '1';
  enrollment_fee?: string;
  trial_value?: string;
  trial_interval?: DurationUnit;
};

const getCourseSubscriptions = (objectId: number) => {
  return wpAjaxInstance.post<string, AxiosResponse<Subscription[]>>(endpoints.GET_SUBSCRIPTIONS_LIST, {
    object_id: objectId,
  });
};

export const useCourseSubscriptionsQuery = (objectId: number) => {
  return useQuery({
    queryKey: ['SubscriptionsList', objectId],
    queryFn: () => getCourseSubscriptions(objectId).then((response) => response.data),
  });
};

const saveCourseSubscription = (objectId: number, subscription: SubscriptionPayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<ID>>(endpoints.SAVE_SUBSCRIPTION, {
    object_id: objectId,
    ...(subscription.id && { id: subscription.id }),
    ...subscription,
  });
};

export const useSaveCourseSubscriptionMutation = (objectId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscription: SubscriptionPayload) => saveCourseSubscription(objectId, subscription),
    onSuccess: (response) => {
      if (response.status_code === 200 || response.status_code === 201) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['SubscriptionsList', objectId],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const deleteCourseSubscription = (objectId: number, subscriptionId: number) => {
  return wpAjaxInstance.post<
    {
      object_id: number;
      id: number;
    },
    TutorMutationResponse<ID>
  >(endpoints.DELETE_SUBSCRIPTION, {
    object_id: objectId,
    id: subscriptionId,
  });
};

export const useDeleteCourseSubscriptionMutation = (objectId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionId: number) => deleteCourseSubscription(objectId, subscriptionId),
    onSuccess: (response, subscriptionId) => {
      if (response.status_code === 200) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.setQueryData(['SubscriptionsList', objectId], (data: Subscription[]) => {
          return data.filter((item) => item.id !== String(subscriptionId));
        });
      }
    },

    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const duplicateCourseSubscription = (objectId: number, subscriptionId: number) => {
  return wpAjaxInstance.post<
    {
      object_id: number;
      id: number;
    },
    TutorMutationResponse<ID>
  >(endpoints.DUPLICATE_SUBSCRIPTION, {
    object_id: objectId,
    id: subscriptionId,
  });
};

export const useDuplicateCourseSubscriptionMutation = (objectId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionId: number) => duplicateCourseSubscription(objectId, subscriptionId),
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['SubscriptionsList', objectId],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const sortCourseSubscriptions = (objectId: number, subscriptionIds: number[]) => {
  return wpAjaxInstance.post<
    {
      object_id: number;
      plan_ids: number[];
    },
    TutorMutationResponse<ID>
  >(endpoints.SORT_SUBSCRIPTION, {
    object_id: objectId,
    plan_ids: subscriptionIds,
  });
};

export const useSortCourseSubscriptionsMutation = (objectId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionIds: number[]) => sortCourseSubscriptions(objectId, subscriptionIds),
    onSuccess: (response, payload) => {
      if (response.status_code === 200) {
        queryClient.setQueryData(['SubscriptionsList', objectId], (data: Subscription[]) => {
          const sortedIds = payload.map((id) => String(id));

          return data.sort((a, b) => sortedIds.indexOf(a.id) - sortedIds.indexOf(b.id));
        });
        queryClient.invalidateQueries({
          queryKey: ['SubscriptionsList', objectId],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });

      queryClient.invalidateQueries({
        queryKey: ['SubscriptionsList', objectId],
      });
    },
  });
};

const getMembershipPlans = () => {
  return wpAjaxInstance.get<MembershipPlan[]>(endpoints.GET_MEMBERSHIP_PLANS).then((response) => response.data);
};

export const useMembershipPlansQuery = () => {
  return useQuery({
    queryKey: ['MembershipPlans'],
    queryFn: getMembershipPlans,
  });
};
