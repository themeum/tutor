import { wpAjaxInstance } from "@/v3/shared/utils/api";
import endpoints from "@/v3/shared/utils/endpoints";
import { useQuery } from "@tanstack/react-query";

export enum EUTaxRegistrationTypes {
  oneStop = 'one-stop',
  microBusiness = 'micro-business',
}

export enum OverrideOn {
  products = 'products',
  shipping = 'shipping',
}

export type CountryOverrideType = 'state' | 'region';

export interface OverrideValue {
  overrideOn: OverrideOn;
  type: CountryOverrideType;
  rate: number;
  category?: number;
  location: string;
}

export interface TaxRateState {
  id: number | string;
  rate: number;
  apply_on_shipping: boolean;
  override_values?: OverrideValue[];
}

export interface TaxRate {
  country: string;
  states: TaxRateState[];
  is_same_rate: boolean;
  rate: number | null;
  vat_registration_type?: EUTaxRegistrationTypes;
  override_values?: OverrideValue[];
}

export enum TaxCollectionProcess {
  isTaxIncludedInPrice = 1,
  taxIsNotIncluded = 0,
}

export interface TaxSettings {
  rates: TaxRate[];
  apply_tax_on: 'product' | 'checkout';
  active_country?: string | null;
  is_tax_included_in_price: 0 | 1;
  show_price_with_tax: boolean;
  charge_tax_on_shipping: boolean;
}


const getTaxSettings = () => {
	// return Promise.resolve<TaxSettings>(null);
	return wpAjaxInstance.get<TaxSettings>(endpoints.GET_TAX_SETTINGS).then(response => response.data);
}

export const useTaxSettingsQuery = () => {
	return useQuery({
		queryKey: ['TaxSettings'],
		queryFn: getTaxSettings
	});
}

