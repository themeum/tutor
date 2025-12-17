import { DragDropManager, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';
import { Chart, type ChartConfiguration, type ScriptableContext, type TooltipModel } from 'chart.js/auto';

import { formatPrice } from '@TutorShared/utils/currency';

interface OverviewChartProps {
  earnings: number[];
  enrolled: number[];
  labels: string[];
}

interface ChartColors {
  line: string;
  point: string;
  pointBorder: string;
  linearGradient?: string[];
}

interface TooltipColors {
  background: string;
  title: string;
  subtitle: string;
}

interface OverviewChartColors {
  earnings: ChartColors & { linearGradient: string[] };
  enrolled: ChartColors & { linearGradient: string[] };
  tooltip: TooltipColors;
  ticks: { color: string };
  border: string;
}

interface StatCardColors extends ChartColors {
  tooltip: TooltipColors;
}

type CourseCompletionChartDataKey = 'enrolled' | 'completed' | 'in_progress' | 'inactive' | 'cancelled';

type CourseCompletionChartData = {
  [key in CourseCompletionChartDataKey]: {
    label: string;
    value: number;
  };
};

const CHART_CONFIG = {
  canvas: {
    statCardHeight: '33px',
    width: '100%',
    overviewMaxHeight: 179,
    completionMaxHeight: 60,
  },

  aspectRatio: {
    overview: 3,
  },

  point: {
    radius: 4,
    hoverRadius: 6,
    borderWidth: 2,
    hoverBorderWidth: 3,
  },

  line: {
    width: 1.3,
    tension: 0.4,
  },

  bar: {
    thickness: 54,
    borderRadius: 5,
    borderWidth: 3,
  },

  gradient: {
    topOffset: -0.95,
    opacity: 0.9,
  },

  tooltip: {
    fontSize: '10px',
    lineHeight: '1.6',
    padding: '4px 7px',
    minWidth: '70px',
    borderRadius: '4px',
    boxShadow: '0px 2px 4px -2px #1018280F, 0px 4px 8px -2px #1018281A',
    caretSize: 6,
    caretInnerSize: 5,
    offsetX: 12,
  },

  common: {
    devicePixelRatio: () => window.devicePixelRatio || 2,
    responsive: true,
    hiddenLegend: { display: false },
    disabledTooltip: { enabled: false },
    hiddenAxis: { display: false, grid: { display: false } },
  },
} as const;

const hexToRgba = (hex: string, alpha: number): string => {
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const isEndPoint = (index: number, dataLength: number): boolean => {
  return index === 0 || index === dataLength - 1;
};

const getCSSProperty = (element: HTMLElement, propertyName: string): string => {
  return getComputedStyle(element).getPropertyValue(propertyName).trim();
};

const extractStatCardColors = (element: HTMLElement, data: number[]): StatCardColors => {
  const isPositiveTrend = data[data.length - 1] > data[0];

  return {
    line: getCSSProperty(element, isPositiveTrend ? '--tutor-border-success' : '--tutor-border-error'),
    point: getCSSProperty(element, isPositiveTrend ? '--tutor-actions-success-primary' : '--tutor-icon-critical'),
    pointBorder: getCSSProperty(element, '--tutor-surface-l2'),
    tooltip: {
      background: getCSSProperty(element, '--tutor-surface-l1'),
      title: getCSSProperty(element, '--tutor-text-subdued'),
      subtitle: getCSSProperty(element, '--tutor-text-primary'),
    },
  };
};

const extractOverviewChartColors = (element: HTMLElement): OverviewChartColors => {
  return {
    earnings: {
      line: getCSSProperty(element, '--tutor-border-brand'),
      point: getCSSProperty(element, '--tutor-actions-brand-primary'),
      pointBorder: getCSSProperty(element, '--tutor-actions-brand-secondary'),
      linearGradient: ['#5E81F4', '#FFFFFF'],
    },
    enrolled: {
      line: getCSSProperty(element, '--tutor-border-success'),
      point: getCSSProperty(element, '--tutor-actions-success-primary'),
      pointBorder: getCSSProperty(element, '--tutor-actions-success-secondary'),
      linearGradient: ['#8AF1B9', '#FFFFFF'],
    },
    tooltip: {
      background: getCSSProperty(element, '--tutor-surface-l1'),
      title: getCSSProperty(element, '--tutor-text-subdued'),
      subtitle: getCSSProperty(element, '--tutor-text-primary'),
    },
    ticks: {
      color: getCSSProperty(element, '--tutor-text-subdued'),
    },
    border: getCSSProperty(element, '--tutor-border-idle'),
  };
};

const extractCompletionChartColors = (element: HTMLElement) => {
  return {
    enrolled: getCSSProperty(element, '--tutor-actions-exception5'),
    completed: getCSSProperty(element, '--tutor-actions-success-primary'),
    in_progress: getCSSProperty(element, '--tutor-button-caution'),
    inactive: getCSSProperty(element, '--tutor-surface-l2-hover'),
    cancelled: getCSSProperty(element, '--tutor-actions-critical-primary'),
    tooltip: {
      background: getCSSProperty(element, '--tutor-surface-l1'),
      title: getCSSProperty(element, '--tutor-text-subdued'),
      subtitle: getCSSProperty(element, '--tutor-text-primary'),
    },
  };
};

const createLinearGradient = (
  ctx: CanvasRenderingContext2D,
  chartArea: { top: number; bottom: number },
  colorTop: string,
  colorBottom: string = '#FFFFFF',
): CanvasGradient => {
  const height = chartArea.bottom - chartArea.top;
  const adjustedTop = chartArea.top + height * CHART_CONFIG.gradient.topOffset;
  const gradient = ctx.createLinearGradient(0, adjustedTop, 0, chartArea.bottom);

  gradient.addColorStop(0, hexToRgba(colorTop, CHART_CONFIG.gradient.opacity));
  gradient.addColorStop(1, hexToRgba(colorBottom, 0));
  return gradient;
};

const createTooltipElement = (): HTMLDivElement => {
  const tooltipEl = document.createElement('div');
  tooltipEl.setAttribute('data-chart-tooltip', '');
  Object.assign(tooltipEl.style, {
    background: 'transparent',
    pointerEvents: 'none',
    position: 'absolute',
    transform: 'translate(-50%, 0)',
    transition: 'all .1s ease',
  });

  const table = document.createElement('table');
  table.style.margin = '0px';
  tooltipEl.appendChild(table);

  return tooltipEl;
};

const getOrCreateTooltip = (chart: Chart): HTMLDivElement => {
  let tooltipEl = chart.canvas.parentNode?.querySelector('div[data-chart-tooltip]') as HTMLDivElement;

  if (!tooltipEl) {
    tooltipEl = createTooltipElement();
    chart.canvas.parentNode?.appendChild(tooltipEl);
  }

  return tooltipEl;
};

const shouldShowTooltip = (tooltip: TooltipModel<'line'> | TooltipModel<'bar'>, chartType: string): boolean => {
  if (tooltip.opacity === 0 || !tooltip.dataPoints?.length) {
    return false;
  }

  if (chartType === 'bar') {
    return true;
  }

  const dataPoint = tooltip.dataPoints[0];
  const datasetLength = dataPoint.dataset.data.length;
  const isEndPoint = dataPoint.dataIndex === 0 || dataPoint.dataIndex === datasetLength - 1;

  return !isEndPoint;
};

const createTooltipHeader = (titleLines: string[], colors: { tooltip: TooltipColors }): HTMLElement => {
  const tableHead = document.createElement('thead');

  if (titleLines.length === 0 || titleLines[0] === '') {
    return tableHead;
  }

  for (const title of titleLines) {
    const tr = document.createElement('tr');
    tr.style.borderWidth = '0';

    const th = document.createElement('th');
    Object.assign(th.style, {
      borderWidth: '0',
      fontSize: CHART_CONFIG.tooltip.fontSize,
      lineHeight: CHART_CONFIG.tooltip.lineHeight,
      fontWeight: 'normal',
      color: colors.tooltip.title,
      marginBottom: '4px',
      textAlign: 'start',
    });

    th.appendChild(document.createTextNode(title));
    tr.appendChild(th);
    tableHead.appendChild(tr);
  }

  return tableHead;
};

const createTooltipBody = (bodyLines: string[][], colors: { tooltip: TooltipColors }): HTMLElement => {
  const tableBody = document.createElement('tbody');

  for (const body of bodyLines) {
    const tr = document.createElement('tr');
    Object.assign(tr.style, {
      backgroundColor: 'inherit',
      borderWidth: '0',
    });

    const td = document.createElement('td');
    Object.assign(td.style, {
      borderWidth: '0',
      fontSize: CHART_CONFIG.tooltip.fontSize,
      lineHeight: CHART_CONFIG.tooltip.lineHeight,
      fontWeight: 'bold',
      color: colors.tooltip.subtitle,
      textAlign: 'start',
    });

    td.appendChild(document.createTextNode(body[0]));
    tr.appendChild(td);
    tableBody.appendChild(tr);
  }

  return tableBody;
};

const styleTooltipTable = (table: HTMLTableElement, colors: { tooltip: TooltipColors }): void => {
  Object.assign(table.style, {
    background: colors.tooltip.background,
    borderRadius: CHART_CONFIG.tooltip.borderRadius,
    padding: CHART_CONFIG.tooltip.padding,
    minWidth: CHART_CONFIG.tooltip.minWidth,
    boxShadow: CHART_CONFIG.tooltip.boxShadow,
    position: 'relative',
    direction: 'inherit',
  });
};

const addCaretToTable = (table: HTMLTableElement, colors: { tooltip: TooltipColors }): void => {
  const carets = table.querySelectorAll('[data-caret]');
  for (const el of carets) {
    el.remove();
  }

  const { caretSize, caretInnerSize } = CHART_CONFIG.tooltip;

  const caret = document.createElement('div');
  caret.setAttribute('data-caret', 'outer');
  Object.assign(caret.style, {
    position: 'absolute',
    left: `-${caretSize}px`,
    top: '50%',
    transform: 'translateY(-50%)',
    width: '0',
    height: '0',
    borderTop: `${caretSize}px solid transparent`,
    borderBottom: `${caretSize}px solid transparent`,
    borderRight: `${caretSize}px solid transparent`,
    zIndex: '1',
  });

  const caretInner = document.createElement('div');
  caretInner.setAttribute('data-caret', 'inner');
  Object.assign(caretInner.style, {
    position: 'absolute',
    left: '-4px',
    top: '50%',
    transform: 'translateY(-50%)',
    width: '0',
    height: '0',
    borderTop: `${caretInnerSize}px solid transparent`,
    borderBottom: `${caretInnerSize}px solid transparent`,
    borderRight: `${caretInnerSize}px solid ${colors.tooltip.background}`,
    zIndex: '2',
  });

  table.appendChild(caret);
  table.appendChild(caretInner);
};

const handleTooltip = (
  context: { chart: Chart; tooltip: TooltipModel<'line'> | TooltipModel<'bar'> },
  colors: { tooltip: TooltipColors },
): void => {
  const { chart, tooltip } = context;
  const tooltipEl = getOrCreateTooltip(chart);

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  if (!shouldShowTooltip(tooltip, (chart.config as any).type)) {
    tooltipEl.style.opacity = '0';
    return;
  }

  const tableRoot = tooltipEl.querySelector('table');
  if (!tableRoot) {
    return;
  }

  while (tableRoot.firstChild) {
    tableRoot.firstChild.remove();
  }

  const titleLines = tooltip.title || [];
  const bodyLines = tooltip.body.map((b) => b.lines);

  const head = createTooltipHeader(titleLines, colors);
  const body = createTooltipBody(bodyLines, colors);

  tableRoot.appendChild(head);
  tableRoot.appendChild(body);

  styleTooltipTable(tableRoot, colors);
  addCaretToTable(tableRoot, colors);

  const { offsetLeft: positionX, offsetTop: positionY } = chart.canvas;
  Object.assign(tooltipEl.style, {
    opacity: '1',
    left: `${positionX + tooltip.caretX + CHART_CONFIG.tooltip.offsetX}px`,
    top: `${positionY + tooltip.caretY}px`,
    transform: 'translate(0, -50%)',
  });
};

const sortSections = (sectionsIds: string[]) => ({
  _sortables: [] as Sortable[],
  _sortableSections: [] as HTMLElement[],
  initialized: false,
  sectionsIds: sectionsIds,
  $el: null as HTMLElement | null,

  init() {
    if (!this.initialized) {
      this.setupDrag();
      this.initialized = true;
    }
  },

  setupDrag() {
    const container = this.$el;
    if (!container) {
      return;
    }

    const manager = new DragDropManager({
      sensors: [PointerSensor, KeyboardSensor],
    });

    const items = Array.from(container.querySelectorAll<HTMLElement>('.tutor-popover-menu-item'));

    this._sortables = [];
    this._sortableSections = [];

    for (const [idx, element] of items.entries()) {
      const handle = element.querySelector('[data-grab]') as HTMLElement | null;
      const id = element.dataset.id ?? String(idx);

      const sortable = new Sortable(
        {
          id,
          index: idx,
          element: element,
          handle: handle ?? undefined,
        },
        manager,
      );

      this._sortables.push(sortable);
    }

    manager.monitor.addEventListener('dragstart', (event) => {
      const operation = event.operation;
      if (!operation.source) {
        return;
      }

      const sourceElement = operation.source.element;
      if (!sourceElement) {
        return;
      }
    });

    manager.monitor.addEventListener('dragend', (event) => {
      const operation = event.operation;
      if (!operation.source) {
        return;
      }

      const sourceElement = operation.source.element;
      if (!sourceElement) {
        return;
      }

      if ('startViewTransition' in document) {
        document.startViewTransition(() => this.updateDom());
      } else {
        this.updateDom();
      }
    });
  },

  updateDom() {
    const newOrder = this.getOrder();
    const sectionMap = this.getSortableSections();

    const firstSectionId = Object.keys(sectionMap)[0];
    const parentContainer = firstSectionId ? sectionMap[firstSectionId]?.parentElement : null;

    if (!parentContainer) {
      return;
    }

    const existingOrder = Array.from(
      new Set(
        Array.from(parentContainer.querySelectorAll<HTMLElement>('[data-section-id]'))
          .map((el) => el.dataset.sectionId)
          .filter((id): id is string => id !== undefined),
      ),
    );

    if (newOrder.length === existingOrder.length && newOrder.every((id, index) => id === existingOrder[index])) {
      return;
    }

    const fragment = document.createDocumentFragment();

    for (const id of newOrder) {
      const section = sectionMap[id];
      if (section) {
        section.remove();
        fragment.appendChild(section);
      }
    }

    parentContainer.appendChild(fragment);
  },

  getOrder() {
    const container = this.$el;
    if (!container) {
      return [];
    }
    const ids = Array.from(container.querySelectorAll<HTMLElement>('.tutor-popover-menu-item'))
      .map((el) => el.dataset.id)
      .filter((id): id is string => id !== undefined);

    return Array.from(new Set(ids));
  },

  getSortableSections() {
    const sections = document.querySelectorAll<HTMLElement>('[data-section-id]');

    return Array.from(sections).reduce(
      (acc, section) => {
        const sectionId = section.dataset.sectionId;
        if (sectionId) {
          acc[sectionId] = section;
        }
        return acc;
      },
      {} as Record<string, HTMLElement>,
    );
  },

  destroy() {
    for (const sortable of this._sortables) {
      sortable.destroy();
    }
    this._sortables = [];

    this.initialized = false;
  },
});

const statCard = (data: number[]) => ({
  $refs: {} as { canvas: HTMLCanvasElement },

  init() {
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    const colors = extractStatCardColors(this.$refs.canvas, data);
    const chartConfig = this.createChartConfig(data, colors);

    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.setAttribute('height', CHART_CONFIG.canvas.statCardHeight);
    this.$refs.canvas.setAttribute('width', CHART_CONFIG.canvas.width);
  },

  createChartConfig(data: number[], colors: StatCardColors): ChartConfiguration<'line'> {
    const dataLength = data.length;

    return {
      type: 'line',
      data: {
        labels: Array.from({ length: dataLength }, (_, i) => i),
        datasets: [
          {
            type: 'line',
            data,
            borderWidth: 1,
            tension: CHART_CONFIG.line.tension,
            pointRadius: 0,
            pointBackgroundColor: colors.point,
            pointHoverRadius: (context: ScriptableContext<'line'>) => {
              return isEndPoint(context.dataIndex, dataLength) ? 0 : 6;
            },
            pointBorderWidth: CHART_CONFIG.point.borderWidth,
            pointBorderColor: colors.pointBorder,
            borderColor: colors.line,
            backgroundColor: colors.line,
            pointHoverBackgroundColor: colors.point,
            pointHoverBorderColor: colors.pointBorder,
            pointHoverBorderWidth: CHART_CONFIG.point.hoverBorderWidth,
          },
        ],
      },
      options: {
        devicePixelRatio: CHART_CONFIG.common.devicePixelRatio(),
        responsive: CHART_CONFIG.common.responsive,
        maintainAspectRatio: false,
        interaction: {
          mode: 'nearest',
          intersect: true,
        },
        plugins: {
          legend: CHART_CONFIG.common.hiddenLegend,
          tooltip: {
            enabled: false,
            position: 'nearest',
            filter: (tooltipItem) => !isEndPoint(tooltipItem.dataIndex, tooltipItem.dataset.data.length),
            external: (context) => handleTooltip(context, colors),
            callbacks: {
              label: (context) => {
                const value = context.parsed.y;
                return value !== null ? value.toString() : '';
              },
              title: () => '',
            },
          },
        },
        scales: {
          x: CHART_CONFIG.common.hiddenAxis,
          y: CHART_CONFIG.common.hiddenAxis,
        },
      },
    };
  },
});

const overviewChart = (data: OverviewChartProps) => ({
  $el: null as HTMLDivElement | null,
  $refs: {} as { canvas: HTMLCanvasElement },
  colors: null as OverviewChartColors | null,

  init() {
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    this.colors = extractOverviewChartColors(this.$refs.canvas);
    const chartConfig = this.createChartConfig(data, this.colors);

    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.style.maxHeight = `${CHART_CONFIG.canvas.overviewMaxHeight}px`;
    this.$refs.canvas.setAttribute('width', CHART_CONFIG.canvas.width);
  },

  getPointRadius(context: ScriptableContext<'line'>, dataLength: number): number {
    return isEndPoint(context.dataIndex, dataLength) ? 0 : CHART_CONFIG.point.hoverRadius;
  },

  getPointHoverColor(
    context: ScriptableContext<'line'>,
    dataLength: number,
    colors: OverviewChartColors,
    colorType: 'point' | 'pointBorder',
  ): string {
    if (isEndPoint(context.dataIndex, dataLength)) {
      return 'transparent';
    }

    const dataset = context.datasetIndex === 0 ? colors.earnings : colors.enrolled;
    return dataset[colorType];
  },

  createDatasetConfig(
    label: string,
    data: number[],
    colors: ChartColors & { linearGradient: string[] },
    allColors: OverviewChartColors,
    dataLength: number,
  ) {
    return {
      label,
      data,
      borderColor: colors.line,
      backgroundColor: (context: ScriptableContext<'line'>) => {
        const chart = context.chart;
        const { ctx, chartArea } = chart;

        if (!chartArea) return colors.line;

        return createLinearGradient(ctx, chartArea, colors.linearGradient[0], colors.linearGradient[1]);
      },
      borderWidth: CHART_CONFIG.line.width,
      tension: CHART_CONFIG.line.tension,
      fill: true,
      pointRadius: (context: ScriptableContext<'line'>) => this.getPointRadius(context, dataLength),
      pointBackgroundColor: 'transparent',
      pointBorderColor: 'transparent',
      pointBorderWidth: CHART_CONFIG.point.hoverBorderWidth,
      pointHoverRadius: CHART_CONFIG.point.hoverRadius,
      pointHoverBackgroundColor: (context: ScriptableContext<'line'>) =>
        this.getPointHoverColor(context, dataLength, allColors, 'point'),
      pointHoverBorderColor: (context: ScriptableContext<'line'>) =>
        this.getPointHoverColor(context, dataLength, allColors, 'pointBorder'),
      pointHoverBorderWidth: CHART_CONFIG.point.hoverBorderWidth,
    };
  },

  createChartConfig(data: OverviewChartProps, colors: OverviewChartColors): ChartConfiguration<'line'> {
    const dataLength = data.earnings.length;

    return {
      type: 'line',
      data: {
        labels: data.labels,
        datasets: [
          this.createDatasetConfig('Earnings', data.earnings, colors.earnings, colors, dataLength),
          this.createDatasetConfig('Enrolled', data.enrolled, colors.enrolled, colors, dataLength),
        ],
      },
      options: {
        devicePixelRatio: CHART_CONFIG.common.devicePixelRatio(),
        aspectRatio: CHART_CONFIG.aspectRatio.overview,
        interaction: {
          mode: 'nearest',
          intersect: true,
        },
        plugins: {
          legend: CHART_CONFIG.common.hiddenLegend,
          tooltip: {
            enabled: false,
            position: 'nearest',
            filter: (tooltipItem) => !isEndPoint(tooltipItem.dataIndex, tooltipItem.dataset.data.length),
            external: (context) => handleTooltip(context, colors),
            callbacks: {
              label: (context) => {
                const value = context.parsed.y;
                if (value === null) return '';
                return context.datasetIndex === 0 ? formatPrice(value) : value.toString();
              },
            },
          },
        },
        scales: {
          x: {
            display: true,
            grid: {
              display: true,
              tickWidth: 0,
              color: (context) => {
                const { index } = context;
                const ticksLength = context.chart.scales.x.ticks.length;
                return isEndPoint(index, ticksLength) ? 'transparent' : colors.border;
              },
            },
            border: {
              color: colors.border,
              dash: [3, 3],
            },
            ticks: {
              color: colors.ticks.color,
              font: { size: 10 },
            },
          },
          y: {
            display: false,
            grace: '10%',
            grid: { display: false },
            border: { display: false },
          },
        },
      },
    };
  },
});

const courseCompletionChart = (data: CourseCompletionChartData) => ({
  $el: null as HTMLDivElement | null,
  $refs: {} as { canvas: HTMLCanvasElement },

  init() {
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    const chartConfig = this.createChartConfig(data);
    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.style.maxHeight = `${CHART_CONFIG.canvas.completionMaxHeight}px`;
    this.$refs.canvas.setAttribute('width', CHART_CONFIG.canvas.width);
  },

  createChartConfig(data: CourseCompletionChartData): ChartConfiguration<'bar'> {
    const colors = extractCompletionChartColors(this.$refs.canvas);

    const chartData = [
      data.enrolled.value,
      data.completed.value,
      data.in_progress.value,
      data.inactive.value,
      data.cancelled.value,
    ];

    const chartColors = [colors.enrolled, colors.completed, colors.in_progress, colors.inactive, colors.cancelled];
    const dataKeys: CourseCompletionChartDataKey[] = ['enrolled', 'completed', 'in_progress', 'inactive', 'cancelled'];

    return {
      type: 'bar',
      data: {
        labels: [''],
        datasets: chartData.map((value, index) => ({
          data: [value],
          backgroundColor: chartColors[index],
          barThickness: CHART_CONFIG.bar.thickness,
          borderRadius: {
            bottomLeft: index === 0 ? CHART_CONFIG.bar.borderRadius : 0,
            topLeft: index === 0 ? CHART_CONFIG.bar.borderRadius : 0,
            bottomRight: index === chartData.length - 1 ? CHART_CONFIG.bar.borderRadius : 0,
            topRight: index === chartData.length - 1 ? CHART_CONFIG.bar.borderRadius : 0,
          },
          borderSkipped: false,
          borderWidth: {
            right: index === chartData.length - 1 ? 0 : CHART_CONFIG.bar.borderWidth,
          },
          borderColor: getCSSProperty(this.$refs.canvas, '--tutor-surface-l1'),
        })),
      },
      options: {
        indexAxis: 'y',
        responsive: CHART_CONFIG.common.responsive,
        maintainAspectRatio: false,
        devicePixelRatio: CHART_CONFIG.common.devicePixelRatio(),
        plugins: {
          legend: CHART_CONFIG.common.hiddenLegend,
          tooltip: {
            enabled: false,
            position: 'nearest',
            external: (context) => handleTooltip(context, colors),
            callbacks: {
              title: (items) => {
                const item = items[0];
                return data[dataKeys[item.datasetIndex]]?.label || '';
              },
              label: (item) => {
                const value = item.raw as number;
                return `${value}`;
              },
            },
          },
        },
        scales: {
          x: {
            stacked: true,
            max: chartData.reduce((sum, value) => sum + value, 0),
            ...CHART_CONFIG.common.hiddenAxis,
          },
          y: {
            stacked: true,
            ...CHART_CONFIG.common.hiddenAxis,
          },
        },
      },
    };
  },
});

const sortableSectionsMeta = {
  name: 'sortableSections',
  component: sortSections,
};

const statCardMeta = {
  name: 'statCard',
  component: statCard,
};

const overviewChartMeta = {
  name: 'overviewChart',
  component: overviewChart,
};

const courseCompletionChartMeta = {
  name: 'courseCompletionChart',
  component: courseCompletionChart,
};

export const initializeHome = () => {
  window.TutorComponentRegistry.registerAll({
    components: [courseCompletionChartMeta, overviewChartMeta, sortableSectionsMeta, statCardMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
