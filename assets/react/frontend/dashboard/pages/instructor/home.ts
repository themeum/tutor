import { Alpine, TutorComponentRegistry } from '@Core/ts';
import { Chart, type ChartConfiguration } from 'chart.js/auto';

const POINT_STYLE = {
  RADIUS: 4,
  BORDER_WIDTH: 2,
} as const;

const CANVAS_CONFIG = {
  HEIGHT: '33px',
  WIDTH: '100%',
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
            tension: 0.5,
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

export const initializeHome = () => {
  TutorComponentRegistry.registerAll({
    components: [statCardMeta],
  });

  TutorComponentRegistry.initWithAlpine(Alpine);

  // eslint-disable-next-line no-console
  console.log('Dashboard home page initialized');
};
