import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import { useCouponDetailsQuery, type Coupon } from '@CouponServices/coupon';
import React from 'react';

interface CouponContextType {
	coupon: Coupon;
}

const CouponContext = React.createContext<CouponContextType>({
	coupon: {} as Coupon,
});

export const useCouponContext = () => React.useContext(CouponContext);

export const CouponProvider = ({ children, couponId }: { children: React.ReactNode; couponId: number }) => {
	const couponDetailsQuery = useCouponDetailsQuery(couponId);

	if (couponDetailsQuery.isLoading) {
		return <LoadingOverlay />;
	}

	if (!couponDetailsQuery.data) {
		return null;
	}

	return <CouponContext.Provider value={{ coupon: couponDetailsQuery.data }}>{children}</CouponContext.Provider>;
};
