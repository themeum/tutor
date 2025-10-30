// Tabs Component
// Alpine.js tabs with keyboard navigation and ARIA support
import { type AlpineTabsData } from '../types/components';

export function createTabs(defaultTab: number = 0): AlpineTabsData {
  return {
    activeTab: defaultTab,
    $el: undefined as HTMLElement | undefined,

    init() {
      this.setupAccessibility();
    },

    setTab(index: number): void {
      this.activeTab = index;
      this.updateAccessibility();
    },

    isActive(index: number): boolean {
      return this.activeTab === index;
    },

    handleKeydown(event: KeyboardEvent, index: number): void {
      const tabs = this.$el?.querySelectorAll('.tutor-tab');
      if (!tabs) return;

      let newIndex = index;

      switch (event.key) {
        case 'ArrowLeft':
          newIndex = index > 0 ? index - 1 : tabs.length - 1;
          break;
        case 'ArrowRight':
          newIndex = index < tabs.length - 1 ? index + 1 : 0;
          break;
        case 'Home':
          newIndex = 0;
          break;
        case 'End':
          newIndex = tabs.length - 1;
          break;
        default:
          return;
      }

      event.preventDefault();
      this.setTab(newIndex);
      (tabs[newIndex] as HTMLElement).focus();
    },

    setupAccessibility(): void {
      const tabs = this.$el?.querySelectorAll('.tutor-tab');
      const panels = this.$el?.querySelectorAll('.tutor-tab-panel');

      tabs.forEach((tab: Element, index: number) => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-selected', index === this.activeTab ? 'true' : 'false');
        tab.setAttribute('aria-controls', `panel-${index}`);
        tab.setAttribute('id', `tab-${index}`);
        tab.setAttribute('tabindex', index === this.activeTab ? '0' : '-1');
      });

      panels.forEach((panel: Element, index: number) => {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', `tab-${index}`);
        panel.setAttribute('id', `panel-${index}`);
      });
    },

    updateAccessibility(): void {
      const tabs = this.$el?.querySelectorAll('.tutor-tab');

      tabs?.forEach((tab: Element, index: number) => {
        tab.setAttribute('aria-selected', index === this.activeTab ? 'true' : 'false');
        tab.setAttribute('tabindex', index === this.activeTab ? '0' : '-1');
      });
    },
  };
}
