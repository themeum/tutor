import { AppConfig, useAppConfigQuery } from '@Services/app';
import { useMemo } from 'react';

const appConfigDefaults: AppConfig = {
  baseUrl: '',
  currency: {
    symbol: '$',
    name: 'USD',
  },
  acceptedImageTypes: [],
  weightUnit: 'kg',
  lengthUnit: 'cm',
  disableAnimation: false,
  settings: {
    shipping: [],
    general: {
      addressLineOne: '',
      addressLineTwo: '',
      city: '',
      postcode: '',
      country: '',
      state: null,
      sellingLocation: '',
      sellingCountries: [],
      sellingCountriesExcept: [],
      shippingLocation: '',
      shippingCountries: [],
      shippingCountriesExcept: [],
      enableTaxes: false,
      enableCouponCodes: false,
      calculateCouponsSequentially: false,
      locale: 'en-us',
      currency: '$',
      unit: 'cm',
    },
    advance: {
      cartPage: '',
      checkoutPage: '',
      myAccountPage: '',
      removeAnimation: false,
    },
    tax: {
      rates: [],
      applyTaxOn: 'product',
    },
    products: {
      shopPage: '',
      redirection: false,
      standardUnits: {
        weight: 'kg',
        dimension: 'cm',
      },
      productReviews: false,
      starRatingOnReviews: false,
    },
    AccountAndPrivacy: {
      guestCheckout: false,
      accountCheckout: false,
      allowCreateAccount: false,
      generateUsername: false,
      allowLogInto: false,
      generatePassword: false,
      termsAndCondition: '',
      privacyAndPolicy: '',
      refundcPolicy: '',
      contactAddress: '',
    },
  },
  predefinedCategories: {
    gift_card: 0,
    uncategorised: 0,
  },
};

export const useAppConfigWithDefaults = () => {
  const appConfigQuery = useAppConfigQuery();

  return useMemo<AppConfig>(() => {
    if (appConfigQuery.isLoading || !appConfigQuery.data) {
      return appConfigDefaults;
    }

    return appConfigQuery.data;
  }, [appConfigQuery]);
};
