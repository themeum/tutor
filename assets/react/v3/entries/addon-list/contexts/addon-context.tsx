import React, { useEffect, useState } from 'react';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { type Addon, useAddonListQuery } from '../services/addons';
import { tutorConfig } from '@Config/config';

interface AddonContextType {
  addons: Addon[];
  updatedAddons: Addon[];
  setUpdatedAddons: (addon: Addon[]) => void;
  searchTerm: string;
  setSearchTerm: (term: string) => void;
}

const AddonContext = React.createContext<AddonContextType>({
  addons: [] as Addon[],
  updatedAddons: [] as Addon[],
  setUpdatedAddons: () => {},
  searchTerm: '' as string,
  setSearchTerm: () => {},
});

export const useAddonContext = () => React.useContext(AddonContext);

export const AddonProvider = ({ children }: { children: React.ReactNode }) => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const [addonList, setAddonList] = useState<Addon[]>([]);
  const [updatedAddons, setUpdatedAddons] = useState<Addon[]>([]);
  const [searchTerm, setSearchTerm] = useState<string>('');

  const addonListQuery = useAddonListQuery();

  useEffect(() => {
    if (addonListQuery.isLoading) {
      return;
    }

    let baseAddons = [];

    if (isTutorPro && addonListQuery.data) {
      baseAddons = addonListQuery.data.addons || [];
    } else {
      baseAddons = tutorConfig.addons_data;
    }

    const filteredAddons = baseAddons.filter((addon) => addon.name.toLowerCase().includes(searchTerm.toLowerCase()));

    setAddonList(filteredAddons);
  }, [addonListQuery.data, addonListQuery.isLoading, isTutorPro, searchTerm]);

  if (addonListQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <AddonContext.Provider
      value={{
        addons: addonList,
        updatedAddons,
        setUpdatedAddons,
        searchTerm,
        setSearchTerm,
      }}
    >
      {children}
    </AddonContext.Provider>
  );
};
