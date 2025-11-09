import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

export interface TabItem {
  id: string;
  label: string;
  icon?: string;
  disabled?: boolean;
  href?: string;
}

export interface TabsConfig {
  tabs: TabItem[];
  defaultTab?: string;
  orientation?: 'horizontal' | 'vertical';
  fullWidth?: boolean;
  onChange?: (tabId: string) => void;
  urlParams?: {
    enabled?: boolean;
    paramName?: string;
  };
}

export const tabs = (config: TabsConfig) => ({
  tabs: config.tabs,
  activeTab: config.defaultTab || config.tabs[0]?.id || '',
  orientation: config.orientation || 'horizontal',
  urlParamsConfig: {
    enabled: config.urlParams?.enabled ?? true,
    paramName: config.urlParams?.paramName || 'page_tab',
  },

  async init() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;

    let initialTab = this.activeTab;

    // Only read from URL if URL params are enabled
    if (this.urlParamsConfig.enabled) {
      const url = new URL(window.location.href);
      const tabId = url.searchParams.get(this.urlParamsConfig.paramName);
      if (tabId) {
        initialTab = tabId;
      }
    }

    this.selectTab(initialTab);
    $el.classList.add('tutor-tabs-' + this.orientation);
  },

  selectTab(tabId: string) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $dispatch = (this as any).$dispatch;
    const tab = this.tabs.find((t) => t.id === tabId);

    if (!tab || tab.disabled) {
      return;
    }

    if (tab.href) {
      window.location.href = tab.href;
      return;
    }

    this.activeTab = tabId;

    if (this.urlParamsConfig.enabled) {
      const url = new URL(window.location.href);
      url.searchParams.set(this.urlParamsConfig.paramName, tabId);
      window.history.replaceState({}, '', url.toString());
    }

    if (config.onChange) {
      config.onChange(tabId);
    }

    // Dispatch custom event
    $dispatch(TUTOR_CUSTOM_EVENTS.TAB_CHANGE, { tabId, tab });
  },

  isActive(tabId: string): boolean {
    return this.activeTab === tabId;
  },

  getTabClass(tab: TabItem) {
    const classes = ['tutor-tabs-tab'];
    if (this.isActive(tab.id)) {
      classes.push('tutor-tabs-tab-active');
    }
    return classes.join(' ');
  },
});

export const tabsMeta = {
  name: 'tabs',
  component: tabs,
};
