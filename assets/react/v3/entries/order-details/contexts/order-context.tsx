import { type Order, useOrderDetailsQuery } from '@OrderDetails/services/order';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
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
    return <LoadingSection />;
  }

  if (!orderDetailsQuery.data) {
    return null;
  }

  return <OrderContext.Provider value={{ order: orderDetailsQuery.data }}>{children}</OrderContext.Provider>;
};
