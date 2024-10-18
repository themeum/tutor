import { createContext, ReactNode, useContext } from 'react';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { PaymentGateway, usePaymentGatewaysQuery } from '../services/payment';
import { tutorConfig } from '@Config/config';

interface PaymentContextType {
  payment_gateways: PaymentGateway[];
  errorMessage?: string;
}

const PaymentContext = createContext<PaymentContextType>({
  payment_gateways: [],
});

export const usePaymentContext = () => useContext(PaymentContext);

export const PaymentProvider = ({ children }: { children: ReactNode }) => {
  if (!tutorConfig.tutor_pro_url) {
    return <>{children}</>;
  }

  const paymentGatewaysQuery = usePaymentGatewaysQuery();

  if (paymentGatewaysQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <PaymentContext.Provider
      value={{
        payment_gateways: paymentGatewaysQuery.data ?? [],
        errorMessage: paymentGatewaysQuery.error?.response?.data?.message,
      }}
    >
      {children}
    </PaymentContext.Provider>
  );
};
