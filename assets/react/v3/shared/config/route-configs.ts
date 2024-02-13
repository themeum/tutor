const replaceParams = (template: string, params: Record<string, unknown> = {}) => {
  return Object.keys(params).reduce((acc, key) => acc.replace(`:${key}`, String(params[key])), template);
};

// Based on https://davidtimms.github.io/programming-languages/typescript/2020/11/20/exploring-template-literal-types-in-typescript-4.1.html
type PathParams<Path extends string> = Path extends `:${infer Param}/${infer Rest}`
  ? Param | PathParams<Rest>
  : Path extends `:${infer Param}`
  ? Param
  : Path extends `${infer _Prefix}:${infer Rest}`
  ? PathParams<`:${Rest}`>
  : never;

type PathArgs<Path extends string> = {
  [K in PathParams<Path>]: string;
};

export interface RouteDefinition<T extends string> {
  template: T;
  buildLink: (params: PathParams<T> extends never ? void : PathArgs<T>) => string;
}

export const defineRoute = <P extends string>(template: P): RouteDefinition<P> => {
  type Params = PathParams<P>;
  return {
    template,
    buildLink: (params: Params extends never ? void : PathArgs<P>) =>
      replaceParams(template, params as PathArgs<P> | undefined),
  } as const;
};

export const RouteConfig = {
  Home: defineRoute('/'),
  Dashboard: defineRoute('/dashboard'),
  Products: defineRoute('/products'),
  Categories: defineRoute('/categories'),
  CreateNewCategory: defineRoute('/categories/new'),
  EditCategory: defineRoute('/categories/:id'),
  Tags: defineRoute('/tags'),
  CreateNewProduct: defineRoute('/products/new'),
  EditProduct: defineRoute('/products/:id'),
  Orders: defineRoute('/orders'),
  ManageOrder: defineRoute('/orders/:id/manage-order'),
  OrderDetails: defineRoute('/orders/:id/order-details'),
  Customers: defineRoute('/customers'),
  CreateNewCustomer: defineRoute('/customers/new'),
  EditCustomer: defineRoute('/customers/:id'),
  Analytics: defineRoute('/analytics'),
  AnalyticsReport: defineRoute('/analytics/report'),
  Settings: defineRoute('/settings'),
  ProductsSettings: defineRoute('/settings/products'),
  ShippingSettings: defineRoute('/settings/shipping'),
  PaymentsSettings: defineRoute('/settings/payments'),
  AccountAndPrivacySettings: defineRoute('/settings/account-and-privacy'),
  EmailSettings: defineRoute('/settings/email'),
  IntegrationsSettings: defineRoute('/settings/integrations'),
  AdvanceSettings: defineRoute('/settings/advance'),
  TaxSettings: defineRoute('/settings/tax'),
  TaxCountriesAndRates: defineRoute('/settings/tax/countries-and-rates/:id'),
  CouponsAndGifts: defineRoute('/coupons-and-gift-cards'),
  CreateNewCoupon: defineRoute('/coupons-and-gift-cards/create-coupon/:type'),
  EditCoupon: defineRoute('/coupons-and-gift-cards/edit-coupon/:id'),
  CreateGiftCard: defineRoute('/products/create-gift-card'),
};
