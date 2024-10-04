import { wpAjaxInstance } from "@/v3/shared/utils/api";
import endpoints from "@/v3/shared/utils/endpoints";
import { useMutation, useQuery } from "@tanstack/react-query";

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
  applyOnShipping: boolean;
  overrideValues?: OverrideValue[];
}

export interface TaxRate {
  country: string;
  states: TaxRateState[];
  isSameRate: boolean;
  rate: number | null;
  vat_registration_type?: EUTaxRegistrationTypes;
  overrideValues?: OverrideValue[];
}

export enum TaxCollectionProcess {
  isTaxIncludedInPrice = 1,
  taxIsNotIncluded = 0,
}

export interface TaxSettings {
  rates: TaxRate[];
  applyTaxOn: 'product' | 'checkout';
  activeCountry?: string | null;
  isTaxIncludedInPrice: 0 | 1;
  showPriceWithTax: boolean;
  chargeTaxOnShipping: boolean;
}

const mockSettings: TaxSettings = {
	rates: [
		{
			country: "248",
			isSameRate: false,
			rate: 20,
			states: []
		},
		{
			country: "050",
			isSameRate: true,
			rate: 15,
			states: [],
			overrideValues: [
				{
					overrideOn: OverrideOn.products,
					rate: 25,
					location: "050",
					category: 8,
					type: "region"
				}
			]
		},
		{
			country: "000",
			isSameRate: false,
			rate: 0,
			states: [
				{
					id: "040",
					rate: 20,
					applyOnShipping: false,
					overrideValues: [
						{
							overrideOn: OverrideOn.products,
							rate: 20,
							location: "040",
							category: 5,
							type: "state"
						},
						{
							overrideOn: OverrideOn.products,
							rate: 23,
							location: "040",
							category: 8,
							type: "state"
						}
					]
				}
			],
			vat_registration_type: EUTaxRegistrationTypes.microBusiness
		}
	],
	applyTaxOn: "product",
	isTaxIncludedInPrice: 0,
	showPriceWithTax: true,
	chargeTaxOnShipping: true,
	activeCountry: null
};

const getTaxSettings = () => {
	return Promise.resolve<TaxSettings>(mockSettings);
}

export const useTaxSettingsQuery = () => {
	return useQuery({
		queryKey: ['TaxSettings'],
		queryFn: getTaxSettings
	});
}



const saveTaxSettings = () => {
	return wpAjaxInstance.post(endpoints.SAVE_TAX_SETTINGS);
}

export const useSaveTaxSettingsMutation = () => {
	return useMutation({
		mutationFn: saveTaxSettings
	});
}