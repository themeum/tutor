import React, { ReactNode } from 'react';

interface OrderRouteGuardProps {
  children: ReactNode;
}

// @TODO: will be implemented later.

const OrderRouteGuard = ({ children }: OrderRouteGuardProps) => {
  return <div>{children}</div>;
};

export default OrderRouteGuard;
