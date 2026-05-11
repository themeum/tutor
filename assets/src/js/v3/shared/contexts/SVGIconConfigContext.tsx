import React, { useContext } from 'react';

interface SVGIconConfigContextValue {
  disableKidsIcons: boolean;
}

const defaultValue: SVGIconConfigContextValue = {
  disableKidsIcons: true,
};

const SVGIconConfigContext = React.createContext<SVGIconConfigContextValue>(defaultValue);

export const useSVGIconConfig = () => useContext(SVGIconConfigContext);

export const SVGIconConfigProvider = ({
  children,
  disableKidsIcons = true,
}: {
  children: React.ReactNode;
  disableKidsIcons?: boolean;
}) => {
  return (
    <SVGIconConfigContext.Provider
      value={{
        disableKidsIcons,
      }}
    >
      {children}
    </SVGIconConfigContext.Provider>
  );
};
