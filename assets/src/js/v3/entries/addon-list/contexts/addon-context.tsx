import { tutorConfig } from '@TutorShared/config/config';
import React, { useState } from 'react';
import { type Addon, useAddonListQuery } from '../services/addons';

interface AddonContextType {
  addons: Addon[];
  updatedAddons: Addon[];
  setUpdatedAddons: (addon: Addon[]) => void;
  searchTerm: string;
  setSearchTerm: (term: string) => void;
  isLoading: boolean;
}

const AddonContext = React.createContext<AddonContextType>({
  addons: [] as Addon[],
  updatedAddons: [] as Addon[],
  setUpdatedAddons: () => {},
  searchTerm: '' as string,
  setSearchTerm: () => {},
  isLoading: false,
});

export const useAddonContext = () => React.useContext(AddonContext);

export const AddonProvider = ({ children }: { children: React.ReactNode }) => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const [updatedAddons, setUpdatedAddons] = useState<Addon[]>([]);
  const [searchTerm, setSearchTerm] = useState<string>('');

  const addonListQuery = useAddonListQuery();

  let addonList = [] as Addon[];

  if (!isTutorPro) {
    addonList = tutorConfig.addons_data;
  } else if (addonListQuery.data) {
    addonList = addonListQuery.data.addons || [];
  }

  return (
    <AddonContext.Provider
      value={{
        addons: addonList,
        updatedAddons,
        setUpdatedAddons,
        searchTerm,
        setSearchTerm,
        isLoading: addonListQuery.isLoading,
      }}
    >
      {children}
    </AddonContext.Provider>
  );
};
