import { Alpine, TutorComponentRegistry } from '@Core/ts';
import { formatPrice } from '@TutorShared/utils/currency';
import { Chart, type ChartConfiguration } from 'chart.js/auto';

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

  createLinearGradient(
    ctx: CanvasRenderingContext2D,
    chartArea: { top: number; bottom: number },
    colorTop: string,
    colorBottom: string = '#FFFFFF',
    topOffset: number = -0.95,
  ): CanvasGradient {
    const height = chartArea.bottom - chartArea.top;
    const adjustedTop = chartArea.top + height * topOffset;

    const gradient = ctx.createLinearGradient(0, adjustedTop, 0, chartArea.bottom);
    gradient.addColorStop(0, colorTop);
    gradient.addColorStop(1, colorBottom);
    return gradient;
  },

  createChartConfig(
    data: OverviewChartProps,
    colors: {
      earnings: { line: string; point: string; pointBorder: string; linearGradient: string[] };
      enrolled: { line: string; point: string; pointBorder: string; linearGradient: string[] };
      tooltip: { background: string; title: string; subtitle: string };
      ticks: { color: string };
      border: string;
    },
  ): ChartConfiguration<'line'> {
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
            pointRadius: CHART_STYLE.POINT_RADIUS,
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            pointBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
            pointHoverRadius: CHART_STYLE.POINT_RADIUS,
            pointHoverBackgroundColor: colors.earnings.point,
            pointHoverBorderColor: colors.earnings.pointBorder,
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
            pointRadius: CHART_STYLE.POINT_RADIUS,
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            pointBorderWidth: CHART_STYLE.POINT_BORDER_WIDTH,
            pointHoverRadius: CHART_STYLE.POINT_RADIUS,
            pointHoverBackgroundColor: colors.enrolled.point,
            pointHoverBorderColor: colors.enrolled.pointBorder,
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
            enabled: true,
            backgroundColor: colors.tooltip.background,
            titleColor: colors.tooltip.title,
            titleMarginBottom: 4,
            titleFont: {
              size: 10,
              lineHeight: 1.6,
              weight: 'normal',
            },
            bodyFont: {
              size: 10,
              lineHeight: 1.6,
              weight: 'bold',
            },
            borderWidth: 1,
            borderColor: colors.border,
            bodyColor: colors.tooltip.subtitle,
            padding: {
              x: 7,
              y: 4,
            },
            callbacks: {
              title: (context) => {
                return context[0].label;
              },
              label: (context) => {
                const value = context.parsed.y as number;
                if (context.datasetIndex === 0) {
                  return formatPrice(value);
                }
                return value.toString();
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
              color: colors.border,
            },
            border: {
              color: colors.border,
              dash: [5, 5],
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
