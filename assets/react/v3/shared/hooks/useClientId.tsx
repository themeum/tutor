import { LocalStorage } from '@Config/constants';
import { useClearTemporaryImagesMutation } from '@Services/images';
import { getStorageItem, setStorageItem } from '@Utils/local-storage';
import { nanoid } from '@Utils/util';
import { createContext, ReactNode, useContext, useEffect, useRef } from 'react';

const ClientIdContext = createContext('');

export const useClientId = () => useContext(ClientIdContext);

interface ClientIdContextProviderProps {
  children: ReactNode;
}

const ClientIdContextProvider = ({ children }: ClientIdContextProviderProps) => {
  const clientIdRef = useRef(nanoid());

  const clearTemporaryImagesMutation = useClearTemporaryImagesMutation();

  useEffect(() => {
    const getClientIdFromStorage = getStorageItem(LocalStorage.clientId);

    if (getClientIdFromStorage && getClientIdFromStorage !== clientIdRef.current) {
      clearTemporaryImagesMutation.mutate({ client_id: getClientIdFromStorage });
    }

    setStorageItem(LocalStorage.clientId, clientIdRef.current);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return <ClientIdContext.Provider value={clientIdRef.current}>{children}</ClientIdContext.Provider>;
};

export default ClientIdContextProvider;
