import React, { useContext } from 'react';

interface SVGIconConfigContextValue {
  supportKidsIcon: boolean;
}

const defaultValue: SVGIconConfigContextValue = {
  supportKidsIcon: false,
};

const SVGIconConfigContext = React.createContext<SVGIconConfigContextValue>(defaultValue);

export const useSVGIconConfig = () => useContext(SVGIconConfigContext);

export const SVGIconConfigProvider = ({
  children,
  supportKidsIcon = false,
}: {
  children: React.ReactNode;
  supportKidsIcon?: boolean;
}) => {
  return (
    <SVGIconConfigContext.Provider
      value={{
        supportKidsIcon,
      }}
    >
      {children}
    </SVGIconConfigContext.Provider>
  );
};
