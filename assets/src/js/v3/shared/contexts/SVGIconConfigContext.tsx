import React, { useContext } from 'react';

interface SVGIconConfigContextValue {
  showKidsIcons: boolean;
}

const defaultValue: SVGIconConfigContextValue = {
  showKidsIcons: false,
};

const SVGIconConfigContext = React.createContext<SVGIconConfigContextValue>(defaultValue);

export const useSVGIconConfig = () => useContext(SVGIconConfigContext);

export const SVGIconConfigProvider = ({
  children,
  showKidsIcons = false,
}: {
  children: React.ReactNode;
  showKidsIcons?: boolean;
}) => {
  return (
    <SVGIconConfigContext.Provider
      value={{
        showKidsIcons,
      }}
    >
      {children}
    </SVGIconConfigContext.Provider>
  );
};
