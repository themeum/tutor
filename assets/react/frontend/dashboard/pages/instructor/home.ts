import { Chart, type ChartConfiguration, type ScriptableContext, type TooltipModel } from 'chart.js/auto';

import { Alpine, TutorComponentRegistry } from '@Core/ts';

const POINT_STYLE = {
  RADIUS: 4,
  BORDER_WIDTH: 2,
} as const;

const CANVAS_CONFIG = {
  HEIGHT: '33px',
  WIDTH: '100%',
  ASPECT_RATIO: 3,
} as const;

const statCard = (data: number[]) => ({
  $refs: {} as { canvas: HTMLCanvasElement },

  init() {
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    const colors = this.getChartColors();
    const chartConfig = this.createChartConfig(data, colors);

    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.setAttribute('height', CANVAS_CONFIG.HEIGHT);
    this.$refs.canvas.setAttribute('width', CANVAS_CONFIG.WIDTH);
  },

  getChartColors() {
    const style = getComputedStyle(this.$refs.canvas);
    const first = data[0];
    const last = data[data.length - 1];
    const isPositiveTrend = last > first;

    return {
      line: isPositiveTrend
        ? style.getPropertyValue('--tutor-border-success').trim()
        : style.getPropertyValue('--tutor-border-error').trim(),
      point: isPositiveTrend
        ? style.getPropertyValue('--tutor-actions-success-primary').trim()
        : style.getPropertyValue('--tutor-icon-critical').trim(),
      pointBorder: style.getPropertyValue('--tutor-surface-l2').trim(),
    };
  },

  calculatePointRadii(dataLength: number): number[] {
    return Array.from({ length: dataLength }, (_, index) => {
      const isEndPoint = index === 0 || index === dataLength - 1;
      return isEndPoint ? 0 : POINT_STYLE.RADIUS;
    });
  },

  createChartConfig(
    data: number[],
    colors: { line: string; point: string; pointBorder: string },
  ): ChartConfiguration<'line'> {
    const pointRadii = this.calculatePointRadii(data.length);

    return {
      type: 'line',
      data: {
        labels: Array.from({ length: data.length }, (_, i) => i),
        datasets: [
          {
            type: 'line',
            data: data,
            borderWidth: 1,
            tension: 0.4,
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

interface OverviewChartProps {
  earnings: number[];
  enrolled: number[];
  labels: string[];
}

interface OverviewChartColors {
  earnings: { line: string; point: string; pointBorder: string; linearGradient: string[] };
  enrolled: { line: string; point: string; pointBorder: string; linearGradient: string[] };
  tooltip: { background: string; title: string; subtitle: string };
  ticks: { color: string };
  border: string;
}

const CHART_STYLE = {
  LINE_WIDTH: 1.3,
  POINT_RADIUS: 6,
  POINT_BORDER_WIDTH: 3,
  TENSION: 0.4,
} as const;

const overviewChart = (data: OverviewChartProps) => ({
  $refs: {} as { canvas: HTMLCanvasElement },

  init() {
    if (!this.$refs.canvas) {
      return;
    }

    this.setupCanvas();
    const colors = this.getChartColors();
    const chartConfig = this.createChartConfig(data, colors);

    new Chart(this.$refs.canvas, chartConfig);
  },

  setupCanvas() {
    this.$refs.canvas.style.maxHeight = 179 + 'px';
    this.$refs.canvas.setAttribute('width', CANVAS_CONFIG.WIDTH);
  },

  getChartColors() {
    const style = getComputedStyle(this.$refs.canvas);

    return {
      earnings: {
        line: style.getPropertyValue('--tutor-border-brand').trim(),
        point: style.getPropertyValue('--tutor-actions-brand-primary').trim(),
        pointBorder: style.getPropertyValue('--tutor-actions-brand-secondary').trim(),
        linearGradient: ['#5E81F4', '#FFFFFF'],
      },
      enrolled: {
        line: style.getPropertyValue('--tutor-border-success').trim(),
        point: style.getPropertyValue('--tutor-actions-success-primary').trim(),
        pointBorder: style.getPropertyValue('--tutor-actions-success-secondary').trim(),
        linearGradient: ['#8AF1B9', '#FFFFFF'],
      },
      tooltip: {
        background: style.getPropertyValue('--tutor-surface-l1').trim(),
        title: style.getPropertyValue('--tutor-text-subdued').trim(),
        subtitle: style.getPropertyValue('--tutor-text-primary').trim(),
      },
      ticks: {
        color: style.getPropertyValue('--tutor-text-subdued').trim(),
      },
      border: style.getPropertyValue('--tutor-border-idle').trim(),
    };
  },

  getOrCreateTooltip(chart: Chart) {
    let tooltipEl = chart.canvas.parentNode?.querySelector('div[data-chart-tooltip]') as HTMLDivElement;

    if (!tooltipEl) {
      tooltipEl = document.createElement('div');
      tooltipEl.setAttribute('data-chart-tooltip', '');
      tooltipEl.style.background = 'transparent';
      tooltipEl.style.pointerEvents = 'none';
      tooltipEl.style.position = 'absolute';
      tooltipEl.style.transform = 'translate(-50%, 0)';
      tooltipEl.style.transition = 'all .1s ease';

      const table = document.createElement('table');
      table.style.margin = '0px';

      tooltipEl.appendChild(table);
      chart.canvas.parentNode?.appendChild(tooltipEl);
    }

    return tooltipEl;
  },

  externalTooltipHandler(context: { chart: Chart; tooltip: TooltipModel<'line'> }, colors: OverviewChartColors) {
    const { chart, tooltip } = context;

    const tooltipEl = this.getOrCreateTooltip(chart);

    if (tooltip.opacity === 0 || !tooltip.dataPoints || tooltip.dataPoints.length === 0) {
      tooltipEl.style.opacity = '0';
      return;
    }

    const dataPoint = tooltip.dataPoints[0];
    const datasetLength = dataPoint.dataset.data.length;
    const isFirstOrLast = dataPoint.dataIndex === 0 || dataPoint.dataIndex === datasetLength - 1;

    if (isFirstOrLast) {
      tooltipEl.style.opacity = '0';
      return;
    }

    if (tooltip.body) {
      const titleLines = tooltip.title || [];
      const bodyLines = tooltip.body.map((b) => b.lines);

      const tableHead = document.createElement('thead');
      titleLines.forEach((title: string) => {
        const tr = document.createElement('tr');
        tr.style.borderWidth = '0';

        const th = document.createElement('th');
        th.style.borderWidth = '0';
        th.style.fontSize = '10px';
        th.style.lineHeight = '1.6';
        th.style.fontWeight = 'normal';
        th.style.color = colors.tooltip.title;
        th.style.marginBottom = '4px';
        th.style.textAlign = 'start';

        const text = document.createTextNode(title);
        th.appendChild(text);
        tr.appendChild(th);
        tableHead.appendChild(tr);
      });

      const tableBody = document.createElement('tbody');
      bodyLines.forEach((body: string[]) => {
        const tr = document.createElement('tr');
        tr.style.backgroundColor = 'inherit';
        tr.style.borderWidth = '0';

        const td = document.createElement('td');
        td.style.borderWidth = '0';
        td.style.fontSize = '10px';
        td.style.lineHeight = '1.6';
        td.style.fontWeight = 'bold';
        td.style.color = colors.tooltip.subtitle;
        td.style.textAlign = 'start';

        const text = document.createTextNode(body[0]);
        td.appendChild(text);
        tr.appendChild(td);
        tableBody.appendChild(tr);
      });

      const tableRoot = tooltipEl.querySelector('table');
      if (tableRoot) {
        while (tableRoot.firstChild) {
          tableRoot.firstChild.remove();
        }

        tableRoot.appendChild(tableHead);
        tableRoot.appendChild(tableBody);

        tableRoot.style.background = colors.tooltip.background;
        tableRoot.style.borderRadius = '4px';
        tableRoot.style.padding = '4px 7px';
        tableRoot.style.minWidth = '70px';
        tableRoot.style.boxShadow = '0px 2px 4px -2px #1018280F, 0px 4px 8px -2px #1018281A';
        tableRoot.style.position = 'relative';
        tableRoot.style.direction = 'inherit';

        const existingCarets = tableRoot.querySelectorAll('[data-caret]');
        existingCarets.forEach((el) => el.remove());

        const caret = document.createElement('div');
        caret.setAttribute('data-caret', 'outer');
        caret.style.position = 'absolute';
        caret.style.left = '-6px';
        caret.style.top = '50%';
        caret.style.transform = 'translateY(-50%)';
        caret.style.width = '0';
        caret.style.height = '0';
        caret.style.borderTop = '6px solid transparent';
        caret.style.borderBottom = '6px solid transparent';
        caret.style.borderRight = '6px solid transparent';
        caret.style.zIndex = '1';

        // Create inner caret for fill
        const caretInner = document.createElement('div');
        caretInner.setAttribute('data-caret', 'inner');
        caretInner.style.position = 'absolute';
        caretInner.style.left = '-4px';
        caretInner.style.top = '50%';
        caretInner.style.transform = 'translateY(-50%)';
        caretInner.style.width = '0';
        caretInner.style.height = '0';
        caretInner.style.borderTop = '5px solid transparent';
        caretInner.style.borderBottom = '5px solid transparent';
        caretInner.style.borderRight = `5px solid ${colors.tooltip.background}`;
        caretInner.style.zIndex = '2';

        tableRoot.appendChild(caret);
        tableRoot.appendChild(caretInner);
      }
    }

    const { offsetLeft: positionX, offsetTop: positionY } = chart.canvas;

    tooltipEl.style.opacity = '1';
    tooltipEl.style.left = positionX + tooltip.caretX + 12 + 'px';
    tooltipEl.style.top = positionY + tooltip.caretY + 'px';
    tooltipEl.style.transform = 'translate(0, -50%)';
  },

  createLinearGradient(
    ctx: CanvasRenderingContext2D,
    chartArea: { top: number; bottom: number },
    colorTop: string,
    colorBottom: string = '#FFFFFF',
    topOffset: number = -0.95,
    opacity: number = 0.9,
  ): CanvasGradient {
    const height = chartArea.bottom - chartArea.top;
    const adjustedTop = chartArea.top + height * topOffset;

    const gradient = ctx.createLinearGradient(0, adjustedTop, 0, chartArea.bottom);

    const hexToRgba = (hex: string, alpha: number) => {
      const r = parseInt(hex.slice(1, 3), 16);
      const g = parseInt(hex.slice(3, 5), 16);
      const b = parseInt(hex.slice(5, 7), 16);
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    gradient.addColorStop(0, hexToRgba(colorTop, opacity));
    gradient.addColorStop(1, hexToRgba(colorBottom, 0));
    return gradient;
  },

  getPointRadius(context: ScriptableContext<'line'>, data: OverviewChartProps) {
    if (context.dataIndex === 0 || context.dataIndex === data.earnings.length - 1) {
      return 0;
    }
    return CHART_STYLE.POINT_RADIUS;
  },

  getPointHoverBackgroundColor(
    context: ScriptableContext<'line'>,
    data: OverviewChartProps,
    colors: OverviewChartColors,
  ) {
    if (context.dataIndex === 0 || context.dataIndex === data.earnings.length - 1) {
      return 'transparent';
    }
    return context.datasetIndex === 0 ? colors.earnings.point : colors.enrolled.point;
  },

  getPointHoverBorderColor(context: ScriptableContext<'line'>, data: OverviewChartProps, colors: OverviewChartColors) {
    if (context.dataIndex === 0 || context.dataIndex === data.earnings.length - 1) {
      return 'transparent';
    }
    return context.datasetIndex === 0 ? colors.earnings.pointBorder : colors.enrolled.pointBorder;
  },

  createChartConfig(data: OverviewChartProps, colors: OverviewChartColors): ChartConfiguration<'line'> {
    return {
      type: 'line',
      data: {
        labels: data.labels,
        datasets: [
          {
            label: 'Earnings',
            data: data.earnings,
            borderColor: colors.earnings.line,
            backgroundColor: (context) => {
              const chart = context.chart;
              const { ctx, chartArea } = chart;

              if (!chartArea) {
                return colors.earnings.line;
              }

              return this.createLinearGradient(
                ctx,
                chartArea,
                colors.earnings.linearGradient[0],
                colors.earnings.linearGradient[1],
              );
            },
            borderWidth: CHART_STYLE.LINE_WIDTH,
            tension: CHART_STYLE.TENSION,
            fill: true,
            pointRadius: (context) => this.getPointRadius(context, data),
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            pointBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
            pointHoverRadius: CHART_STYLE.POINT_RADIUS,
            pointHoverBackgroundColor: (context) => this.getPointHoverBackgroundColor(context, data, colors),
            pointHoverBorderColor: (context) => this.getPointHoverBorderColor(context, data, colors),
            pointHoverBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
          },
          {
            label: 'Enrolled',
            data: data.enrolled,
            borderColor: colors.enrolled.line,
            backgroundColor: (context) => {
              const chart = context.chart;
              const { ctx, chartArea } = chart;

              if (!chartArea) {
                return colors.enrolled.line;
              }

              return this.createLinearGradient(
                ctx,
                chartArea,
                colors.enrolled.linearGradient[0],
                colors.enrolled.linearGradient[1],
              );
            },
            borderWidth: CHART_STYLE.LINE_WIDTH,
            tension: CHART_STYLE.TENSION,
            fill: true,
            pointRadius: (context) => this.getPointRadius(context, data),
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            pointBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
            pointHoverRadius: CHART_STYLE.POINT_RADIUS,
            pointHoverBackgroundColor: (context) => this.getPointHoverBackgroundColor(context, data, colors),
            pointHoverBorderColor: (context) => this.getPointHoverBorderColor(context, data, colors),
            pointHoverBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
          },
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
          legend: {
            display: false,
          },
          tooltip: {
            enabled: false,
            position: 'nearest',
            filter: (tooltipItem) => {
              const index = tooltipItem.dataIndex;
              const datasetLength = tooltipItem.dataset.data.length;
              return !(index === 0 || index === datasetLength - 1);
            },
            external: (context) => this.externalTooltipHandler(context, colors),
          },
        },
        scales: {
          x: {
            display: true,
            grid: {
              display: true,
              tickWidth: 0,
              color: (context) => {
                const index = context.index;
                const ticksLength = context.chart.scales.x.ticks.length;
                if (index === 0 || index === ticksLength - 1) {
                  return 'transparent';
                }
                return colors.border;
              },
            },
            border: {
              color: colors.border,
              dash: [3, 3],
            },
            ticks: {
              color: colors.ticks.color,
              font: {
                size: 10,
              },
            },
          },
          y: {
            display: false,
            grace: '10%',
            grid: {
              display: false,
            },
            border: {
              display: false,
            },
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
