/**
 * Learning Area Sidebar logic
 *
 * @since 4.0.0
 */

interface SidebarData {
  pagesHeight: number;
  resizing: boolean;
  collapsed: boolean;
  $refs: {
    pagesList: HTMLElement;
  } & Record<string, HTMLElement>;
  $nextTick: (callback: () => void) => void;
  init: () => void;
  startResizing: (e: MouseEvent) => void;
  togglePagesHeight: () => void;
}

export const sidebarComponent = (isCollapsed: boolean): SidebarData => {
  return {
    pagesHeight: 0,
    resizing: false,
    collapsed: isCollapsed,
    $refs: {} as SidebarData['$refs'],
    $nextTick: {} as SidebarData['$nextTick'],

    init(this: SidebarData) {
      this.$nextTick(() => {
        if (this.$refs.pagesList) {
          this.pagesHeight = this.$refs.pagesList.scrollHeight;
        }
      });
    },

    startResizing(this: SidebarData, e: MouseEvent) {
      this.resizing = true;
      const startY = e.clientY;
      const currentHeight = this.$refs.pagesList?.offsetHeight || 0;

      const onMouseMove = (moveEvent: MouseEvent) => {
        const delta = startY - moveEvent.clientY;
        // Constraint height between 36px and 400px.
        this.pagesHeight = Math.max(36, Math.min(400, currentHeight + delta));
      };

      const onMouseUp = () => {
        this.resizing = false;
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('mouseup', onMouseUp);
      };

      window.addEventListener('mousemove', onMouseMove);
      window.addEventListener('mouseup', onMouseUp);
    },

    togglePagesHeight(this: SidebarData) {
      if (this.pagesHeight > 36) {
        this.pagesHeight = 36;
      } else if (this.$refs.pagesList) {
        this.pagesHeight = this.$refs.pagesList.scrollHeight;
      }
    },
  };
};

export const initializeSidebar = () => {
  if (window.TutorComponentRegistry) {
    window.TutorComponentRegistry.register({
      type: 'component',
      meta: {
        name: 'learningSidebar',
        component: sidebarComponent,
      },
    });
  }
};
