import React, { createContext, type ReactNode, useContext, useReducer } from 'react';

export interface SettingsSegment {
  label: string;
  slug: string;
  fields: SettingsField[];
}

export interface SettingsField {
  key: string;
  type: string;
  label: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  default?: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  options?: Record<string, any>;
  desc?: string;
  searchable?: boolean;
  label_title?: string;
  number_type?: string;
  select_options?: boolean;
  // Additional properties for new field types
  buttons?: Record<
    string,
    {
      type?: string;
      text?: string;
      url?: string;
    }
  >;
  min?: number;
  max?: number;
  accept?: string;
  // For checkgroup type with nested fields
  group_options?: SettingsField[];
  // For segments with tabs
  segments?: SettingsSegment[];
}

export interface SettingsBlock {
  label: string | false;
  slug: string;
  block_type: string;
  fields: SettingsField[];
  fields_group?: SettingsField[];
}

export interface SettingsSection {
  label: string;
  slug: string;
  desc: string;
  template: string;
  icon: string;
  blocks: SettingsBlock[] | Record<string, SettingsBlock>;
  submenu?: SettingsSection[];
}

export interface SettingsState {
  sections: Record<string, SettingsSection>;
  currentSection: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  values: Record<string, any>;
  isLoading: boolean;
  isSaving: boolean;
  searchQuery: string;
  isDirty: boolean;
}

type SettingsAction =
  | { type: 'SET_SECTIONS'; payload: Record<string, SettingsSection> }
  | { type: 'SET_CURRENT_SECTION'; payload: string }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  | { type: 'SET_VALUES'; payload: Record<string, any> }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  | { type: 'UPDATE_VALUE'; payload: { key: string; value: any } }
  | { type: 'SET_LOADING'; payload: boolean }
  | { type: 'SET_SAVING'; payload: boolean }
  | { type: 'SET_SEARCH_QUERY'; payload: string }
  | { type: 'SET_DIRTY'; payload: boolean }
  | { type: 'RESET_DIRTY' };

const initialState: SettingsState = {
  sections: {},
  currentSection: 'general',
  values: {},
  isLoading: false,
  isSaving: false,
  searchQuery: '',
  isDirty: false,
};

const settingsReducer = (state: SettingsState, action: SettingsAction): SettingsState => {
  switch (action.type) {
    case 'SET_SECTIONS':
      return { ...state, sections: action.payload };
    case 'SET_CURRENT_SECTION':
      return { ...state, currentSection: action.payload };
    case 'SET_VALUES':
      return { ...state, values: action.payload };
    case 'UPDATE_VALUE':
      return {
        ...state,
        values: { ...state.values, [action.payload.key]: action.payload.value },
        isDirty: true,
      };
    case 'SET_LOADING':
      return { ...state, isLoading: action.payload };
    case 'SET_SAVING':
      return { ...state, isSaving: action.payload };
    case 'SET_SEARCH_QUERY':
      return { ...state, searchQuery: action.payload };
    case 'SET_DIRTY':
      return { ...state, isDirty: action.payload };
    case 'RESET_DIRTY':
      return { ...state, isDirty: false };
    default:
      return state;
  }
};

interface SettingsContextType {
  state: SettingsState;
  dispatch: React.Dispatch<SettingsAction>;
}

const SettingsContext = createContext<SettingsContextType | undefined>(undefined);

export const useSettings = () => {
  const context = useContext(SettingsContext);
  if (!context) {
    throw new Error('useSettings must be used within a SettingsProvider');
  }
  return context;
};

interface SettingsProviderProps {
  children: ReactNode;
}

export const SettingsProvider: React.FC<SettingsProviderProps> = ({ children }) => {
  const [state, dispatch] = useReducer(settingsReducer, initialState);

  return <SettingsContext.Provider value={{ state, dispatch }}>{children}</SettingsContext.Provider>;
};
