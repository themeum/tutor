import { useToast } from '@Atoms/Toast';
import { tutorConfig } from '@Config/config';
import { DateFormats } from '@Config/constants';
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { ErrorResponse } from '@Utils/form';
import { PaginatedParams, PaginatedResult } from '@Utils/types';
import { convertToGMT } from '@Utils/util';
import { format } from 'date-fns';

export type CouponType = 'code' | 'automatic';

export type CouponStatus = 'active' | 'inactive' | 'trash';

export interface Course {
	id: number;
	title: string;
	image: '';
	author: string;
	regular_price: string;
	sale_price: string | null;
	plan_start_price?: string;
}

export interface CourseCategory {
	id: number;
	title: string;
	image: string;
	total_courses: number;
}

export type CouponAppliesTo =
	| 'all_courses_and_bundles'
	| 'all_courses'
	| 'all_bundles'
	| 'specific_courses'
	| 'specific_bundles'
	| 'specific_category';

export interface Coupon {
	id?: number;
	coupon_status: CouponStatus;
	coupon_type: CouponType;
	coupon_title: string;
	coupon_code: string;
	discount_type: 'percentage' | 'flat';
	discount_amount: string;
	applies_to: CouponAppliesTo;
	courses?: Course[];
	categories?: CourseCategory[];
	bundles?: Course[];
	usage_limit_status: boolean;
	total_usage_limit: string | null;
	per_user_limit_status: boolean;
	per_user_usage_limit: string | null;
	coupon_uses?: number;
	purchase_requirement: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirement_value: string;
	start_date: string;
	start_time: string;
	is_end_enabled: boolean;
	end_date: string;
	end_time: string;
	created_at_gmt: string;
	updated_at_gmt: string;
	coupon_created_by: string;
	coupon_update_by: string;
}

export interface CouponPayload {
	id?: number;
	coupon_status: CouponStatus;
	coupon_type: CouponType;
	coupon_title: string;
	coupon_code?: string;
	discount_type: 'percentage' | 'flat';
	discount_amount: string;
	applies_to: CouponAppliesTo;
	applies_to_items: number[];
	total_usage_limit?: string;
	per_user_usage_limit?: string;
	purchase_requirement: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirement_value?: string;
	start_date_gmt: string;
	expire_date_gmt?: string;
}

export interface GetCouponResponse {
	id?: number;
	coupon_status: CouponStatus;
	coupon_type: CouponType;
	coupon_title: string;
	coupon_code: string;
	discount_type: 'percentage' | 'flat';
	discount_amount: string;
	applies_to: CouponAppliesTo;
	applies_to_items: Course[] | CourseCategory[];
	total_usage_limit: string | null;
	per_user_usage_limit: string | null;
	purchase_requirement: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirement_value?: string;
	coupon_usage: number;
	start_date_gmt: string;
	expire_date_gmt: string | null;
	created_at_gmt: string;
	updated_at_gmt: string;
	coupon_created_by: string;
	coupon_update_by: string;
}

export const couponInitialValue: Coupon = {
	coupon_status: 'active',
	coupon_type: 'code',
	coupon_title: '',
	coupon_code: '',
	discount_type: 'percentage',
	discount_amount: '',
	applies_to: 'all_courses',
	courses: [],
	categories: [],
	bundles: [],
	usage_limit_status: false,
	total_usage_limit: '',
	per_user_limit_status: false,
	per_user_usage_limit: '',
	purchase_requirement: 'no_minimum',
	purchase_requirement_value: '',
	start_date: '',
	start_time: '',
	is_end_enabled: false,
	end_date: '',
	end_time: '',
	created_at_gmt: '',
	updated_at_gmt: '',
	coupon_created_by: '',
	coupon_update_by: '',
};

function getAppliesToItemIds(data: Coupon) {
	if (data.applies_to === 'specific_courses') {
		return data.courses?.map((item) => item.id) ?? [];
	}
	if (data.applies_to === 'specific_bundles') {
		return data.bundles?.map((item) => item.id) ?? [];
	}
	if (data.applies_to === 'specific_category') {
		return data.categories?.map((item) => item.id) ?? [];
	}
	return [];
}

export function convertFormDataToPayload(data: Coupon): CouponPayload {
	return {
		...(data.id && {
			id: data.id
		}),
		coupon_status: data.coupon_status,
		coupon_type: data.coupon_type,
		...(data.coupon_type === 'code' && {
			coupon_code: data.coupon_code
		}),
		coupon_title: data.coupon_title,
		discount_type: data.discount_type,
		discount_amount: data.discount_amount,
		applies_to: data.applies_to,
		applies_to_items: getAppliesToItemIds(data),
		...(data.total_usage_limit && {
			total_usage_limit: data.total_usage_limit
		}),
		...(data.per_user_usage_limit && {
			per_user_usage_limit: data.per_user_usage_limit
		}),
		...(data.purchase_requirement && {
			purchase_requirement: data.purchase_requirement
		}),
		...(data.purchase_requirement_value && {
			purchase_requirement_value: data.purchase_requirement_value
		}),
		start_date_gmt: convertToGMT(
			new Date(`${data.start_date} ${data.start_time}`), 
			DateFormats.yearMonthDayHourMinuteSecond24H
		),
		...(data.is_end_enabled && data.end_date && {
			expire_date_gmt: convertToGMT(
				new Date(`${data.end_date} ${data.end_time}`), 
				DateFormats.yearMonthDayHourMinuteSecond24H
			),
		})
	}
}

const getCouponDetails = (couponId: number) => {
	return authApiInstance.post<GetCouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_details',
		id: couponId,
	});
};

export const useCouponDetailsQuery = (couponId: number) => {
	return useQuery({
		enabled: !!couponId,
		queryKey: ['CouponDetails', couponId],
		queryFn: () => getCouponDetails(couponId),
	});
};

interface CouponResponse {
	id: number;
	message: string;
	status_code: number;
}

const createCoupon = (payload: CouponPayload) => {
	return authApiInstance.post<CouponPayload, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_create',
		...payload,
	});
};

export const useCreateCouponMutation = () => {
	const { showToast } = useToast();

	return useMutation({
		mutationFn: createCoupon,
		onSuccess: (response) => {
			window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor_coupons`;
			showToast({ type: 'success', message: response.message });
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};

const updateCoupon = (payload: CouponPayload) => {
	return authApiInstance.post<CouponPayload, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_update',
		...payload,
	});
};

export const useUpdateCouponMutation = () => {
	const { showToast } = useToast();
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn: updateCoupon,
		onSuccess: (response) => {
			showToast({ type: 'success', message: response.message });
			queryClient.invalidateQueries({
				queryKey: ['CouponDetails', response.id],
			});
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};

interface GetAppliesToParam extends PaginatedParams {
	applies_to: CouponAppliesTo;
}

const getAppliesToList = (params: GetAppliesToParam) => {
	return authApiInstance.post<PaginatedResult<Course | CourseCategory>>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_applies_to_list',
		...params 
	});
};

export const useAppliesToQuery = (params: GetAppliesToParam) => {
	return useQuery({
		queryKey: ['AppliesTo', params],
		placeholderData: keepPreviousData,
		queryFn: () => {
			return getAppliesToList(params).then((res) => {
				return res.data;
			});
		},
	});
};

