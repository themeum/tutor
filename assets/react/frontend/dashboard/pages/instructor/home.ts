import { Alpine, TutorComponentRegistry } from '@Core/ts';
import { formatPrice } from '@TutorShared/utils/currency';
import { Chart, type ChartConfiguration, type ScriptableContext, type TooltipModel } from 'chart.js/auto';

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

interface OverviewChartColors {
  earnings: ChartColors & { linearGradient: string[] };
  enrolled: ChartColors & { linearGradient: string[] };
  tooltip: {
    background: string;
    title: string;
    subtitle: string;
  };
  ticks: { color: string };
  border: string;
}

const POINT_STYLE = {
  RADIUS: 4,
  HOVER_RADIUS: 6,
  BORDER_WIDTH: 2,
  HOVER_BORDER_WIDTH: 3,
} as const;

const CANVAS_CONFIG = {
  STAT_CARD_HEIGHT: '33px',
  WIDTH: '100%',
  ASPECT_RATIO: 3,
  OVERVIEW_MAX_HEIGHT: 179,
} as const;

const CHART_STYLE = {
  LINE_WIDTH: 1.3,
  TENSION: 0.4,
} as const;

const extractStatCardColors = (element: HTMLElement, data: number[]): ChartColors => {
  const style = getComputedStyle(element);
  const getProperty = (name: string) => style.getPropertyValue(name).trim();
  const isPositiveTrend = data[data.length - 1] > data[0];

  return {
    line: getProperty(isPositiveTrend ? '--tutor-border-success' : '--tutor-border-error'),
    point: getProperty(isPositiveTrend ? '--tutor-actions-success-primary' : '--tutor-icon-critical'),
    pointBorder: getProperty('--tutor-surface-l2'),
  };
};

const extractOverviewChartColors = (element: HTMLElement): OverviewChartColors => {
  const style = getComputedStyle(element);
  const getProperty = (name: string) => style.getPropertyValue(name).trim();

  return {
    earnings: {
      line: getProperty('--tutor-border-brand'),
      point: getProperty('--tutor-actions-brand-primary'),
      pointBorder: getProperty('--tutor-actions-brand-secondary'),
      linearGradient: ['#5E81F4', '#FFFFFF'],
    },
    enrolled: {
      line: getProperty('--tutor-border-success'),
      point: getProperty('--tutor-actions-success-primary'),
      pointBorder: getProperty('--tutor-actions-success-secondary'),
      linearGradient: ['#8AF1B9', '#FFFFFF'],
    },
    tooltip: {
      background: getProperty('--tutor-surface-l1'),
      title: getProperty('--tutor-text-subdued'),
      subtitle: getProperty('--tutor-text-primary'),
    },
    ticks: {
      color: getProperty('--tutor-text-subdued'),
    },
    border: getProperty('--tutor-border-idle'),
  };
};

const hexToRgba = (hex: string, alpha: number): string => {
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const createLinearGradient = (
  ctx: CanvasRenderingContext2D,
  chartArea: { top: number; bottom: number },
  colorTop: string,
  colorBottom: string = '#FFFFFF',
  topOffset: number = -0.95,
  opacity: number = 0.9,
): CanvasGradient => {
  const height = chartArea.bottom - chartArea.top;
  const adjustedTop = chartArea.top + height * topOffset;
  const gradient = ctx.createLinearGradient(0, adjustedTop, 0, chartArea.bottom);

  gradient.addColorStop(0, hexToRgba(colorTop, opacity));
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

const shouldShowTooltip = (tooltip: TooltipModel<'line'>): boolean => {
  if (tooltip.opacity === 0 || !tooltip.dataPoints?.length) {
    return false;
  }

  const dataPoint = tooltip.dataPoints[0];
  const datasetLength = dataPoint.dataset.data.length;
  const isEndPoint = dataPoint.dataIndex === 0 || dataPoint.dataIndex === datasetLength - 1;

  return !isEndPoint;
};

const createTooltipHeader = (titleLines: string[], colors: OverviewChartColors): HTMLElement => {
  const tableHead = document.createElement('thead');

  titleLines.forEach((title: string) => {
    const tr = document.createElement('tr');
    tr.style.borderWidth = '0';

    const th = document.createElement('th');
    Object.assign(th.style, {
      borderWidth: '0',
      fontSize: '10px',
      lineHeight: '1.6',
      fontWeight: 'normal',
      color: colors.tooltip.title,
      marginBottom: '4px',
      textAlign: 'start',
    });

    th.appendChild(document.createTextNode(title));
    tr.appendChild(th);
    tableHead.appendChild(tr);
  });

  return tableHead;
};

const createTooltipBody = (bodyLines: string[][], colors: OverviewChartColors): HTMLElement => {
  const tableBody = document.createElement('tbody');

  bodyLines.forEach((body: string[]) => {
    const tr = document.createElement('tr');
    Object.assign(tr.style, {
      backgroundColor: 'inherit',
      borderWidth: '0',
    });

    const td = document.createElement('td');
    Object.assign(td.style, {
      borderWidth: '0',
      fontSize: '10px',
      lineHeight: '1.6',
      fontWeight: 'bold',
      color: colors.tooltip.subtitle,
      textAlign: 'start',
    });

    td.appendChild(document.createTextNode(body[0]));
    tr.appendChild(td);
    tableBody.appendChild(tr);
  });

  return tableBody;
};

const styleTooltipTable = (table: HTMLTableElement, colors: OverviewChartColors): void => {
  Object.assign(table.style, {
    background: colors.tooltip.background,
    borderRadius: '4px',
    padding: '4px 7px',
    minWidth: '70px',
    boxShadow: '0px 2px 4px -2px #1018280F, 0px 4px 8px -2px #1018281A',
    position: 'relative',
    direction: 'inherit',
  });
};

const addCaretToTable = (table: HTMLTableElement, colors: OverviewChartColors): void => {
  table.querySelectorAll('[data-caret]').forEach((el) => el.remove());

  const caret = document.createElement('div');
  caret.setAttribute('data-caret', 'outer');
  Object.assign(caret.style, {
    position: 'absolute',
    left: '-6px',
    top: '50%',
    transform: 'translateY(-50%)',
    width: '0',
    height: '0',
    borderTop: '6px solid transparent',
    borderBottom: '6px solid transparent',
    borderRight: '6px solid transparent',
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
    borderTop: '5px solid transparent',
    borderBottom: '5px solid transparent',
    borderRight: `5px solid ${colors.tooltip.background}`,
    zIndex: '2',
  });

  table.appendChild(caret);
  table.appendChild(caretInner);
};

const handleTooltip = (context: { chart: Chart; tooltip: TooltipModel<'line'> }, colors: OverviewChartColors): void => {
  const { chart, tooltip } = context;
  const tooltipEl = getOrCreateTooltip(chart);

  if (!shouldShowTooltip(tooltip)) {
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
    left: `${positionX + tooltip.caretX + 12}px`,
    top: `${positionY + tooltip.caretY}px`,
    transform: 'translate(0, -50%)',
  });
};

const isEndPoint = (index: number, dataLength: number): boolean => {
  return index === 0 || index === dataLength - 1;
};

const calculatePointRadii = (dataLength: number): number[] => {
  return Array.from({ length: dataLength }, (_, index) => {
    return isEndPoint(index, dataLength) ? 0 : POINT_STYLE.RADIUS;
  });
};

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
    this.$refs.canvas.setAttribute('height', CANVAS_CONFIG.STAT_CARD_HEIGHT);
    this.$refs.canvas.setAttribute('width', CANVAS_CONFIG.WIDTH);
  },

  createChartConfig(data: number[], colors: ChartColors): ChartConfiguration<'line'> {
    const pointRadii = calculatePointRadii(data.length);

    return {
      type: 'line',
      data: {
        labels: Array.from({ length: data.length }, (_, i) => i),
        datasets: [
          {
            type: 'line',
            data,
            borderWidth: 1,
            tension: CHART_STYLE.TENSION,
            pointRadius: pointRadii,
            pointBackgroundColor: colors.point,
            pointHoverRadius: pointRadii,
            pointBorderWidth: POINT_STYLE.BORDER_WIDTH,
            pointBorderColor: colors.pointBorder,
            borderColor: colors.line,
            backgroundColor: colors.line,
          },
        ],
      },
      options: {
        devicePixelRatio: window.devicePixelRatio || 2,
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false },
        },
        scales: {
          x: { display: false, grid: { display: false } },
          y: { display: false, grid: { display: false } },
        },
      },
    };
  },
});

const statCardMeta = {
  name: 'statCard',
  component: statCard,
};

const overviewChart = (data: OverviewChartProps) => ({
  $el: null as HTMLDivElement | null,
  $refs: {} as { canvas: HTMLCanvasElement },
  colors: null as OverviewChartColors | null,

  init() {
    if (this.$el) {
      this.$el.style.maxHeight = `250px`;
    }
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    this.colors = extractOverviewChartColors(this.$refs.canvas);
    const chartConfig = this.createChartConfig(data, this.colors);

    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.style.maxHeight = `${CANVAS_CONFIG.OVERVIEW_MAX_HEIGHT}px`;
    this.$refs.canvas.setAttribute('width', CANVAS_CONFIG.WIDTH);
  },

  getPointRadius(context: ScriptableContext<'line'>, dataLength: number): number {
    return isEndPoint(context.dataIndex, dataLength) ? 0 : POINT_STYLE.HOVER_RADIUS;
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
      borderWidth: CHART_STYLE.LINE_WIDTH,
      tension: CHART_STYLE.TENSION,
      fill: true,
      pointRadius: (context: ScriptableContext<'line'>) => this.getPointRadius(context, dataLength),
      pointBackgroundColor: 'transparent',
      pointBorderColor: 'transparent',
      pointBorderWidth: POINT_STYLE.HOVER_BORDER_WIDTH,
      pointHoverRadius: POINT_STYLE.HOVER_RADIUS,
      pointHoverBackgroundColor: (context: ScriptableContext<'line'>) =>
        this.getPointHoverColor(context, dataLength, allColors, 'point'),
      pointHoverBorderColor: (context: ScriptableContext<'line'>) =>
        this.getPointHoverColor(context, dataLength, allColors, 'pointBorder'),
      pointHoverBorderWidth: POINT_STYLE.HOVER_BORDER_WIDTH,
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
        devicePixelRatio: window.devicePixelRatio || 2,
        aspectRatio: CANVAS_CONFIG.ASPECT_RATIO,
        interaction: {
          mode: 'nearest',
          intersect: true,
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            enabled: false,
            position: 'nearest',
            filter: (tooltipItem) => !isEndPoint(tooltipItem.dataIndex, tooltipItem.dataset.data.length),
            external: (context) => handleTooltip(context, colors),
            callbacks: {
              label: (context) => {
                const value = context.parsed.y;
                if (value === null) return '';
                // Format earnings (dataset 0) with currency, enrolled (dataset 1) as plain number
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

const overviewChartMeta = {
  name: 'overviewChart',
  component: overviewChart,
};

export const initializeHome = () => {
  TutorComponentRegistry.registerAll({
    components: [overviewChartMeta, statCardMeta],
  });

  TutorComponentRegistry.initWithAlpine(Alpine);

  // eslint-disable-next-line no-console
  console.log('Dashboard home page initialized');
};
