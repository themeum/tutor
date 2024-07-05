import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import { useOrderDetailsQuery, type Order } from '@OrderServices/order';
import React from 'react';

interface OrderContextType {
  order: Order;
}

const OrderContext = React.createContext<OrderContextType>({
  order: {} as Order,
});

export const useOrderContext = () => React.useContext(OrderContext);

export const OrderProvider = ({ children, orderId }: { children: React.ReactNode; orderId: number }) => {
  const orderDetailsQuery = useOrderDetailsQuery(orderId);

  if (orderDetailsQuery.isLoading) {
    return <LoadingOverlay />;
  }

  if (!orderDetailsQuery.data) {
    return null;
  }

  return <OrderContext.Provider value={{ order: orderDetailsQuery.data }}>{children}</OrderContext.Provider>;
};
