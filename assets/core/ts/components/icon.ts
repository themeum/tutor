import { type AlpineComponentMeta } from '@Core/types';

const iconCache = new Map<string, string>();
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const baseUrl = (window as any)._tutorobject?.tutor_url || '';

export interface IconProps {
  name: string; // Use PHP Icon::<name> to get the name.
  width?: number;
  height?: number;
  from: 'php' | 'ts';
}

const createSvg = ({
  width,
  height,
  viewBox,
  content = '',
}: {
  width: number;
  height: number;
  viewBox?: string;
  content?: string;
}) =>
  `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="${viewBox || '0 0 ' + width + ' ' + height}">${content}</svg>`;

async function fetchSVG(name: string, width: number, height: number, from: 'php' | 'ts' = 'ts') {
  if (iconCache.has(name)) {
    return iconCache.get(name)!;
  }

  const fileName = from === 'php' ? name : name.trim().replace(/[A-Z]/g, (m) => '-' + m.toLowerCase());
  const url = `${baseUrl}assets/icons/${fileName}.svg`;
  const defaultViewBox = `0 0 ${width} ${height}`;

  try {
    const response = await fetch(url);
    const svgText = await response.text();

    const parser = new DOMParser();
    const doc = parser.parseFromString(svgText, 'image/svg+xml');
    const svgEl = doc.querySelector('svg');
    const viewBox = svgEl?.getAttribute('viewBox') || defaultViewBox;
    const content = svgEl?.innerHTML || '';

    const svgMarkup = createSvg({ width, height, viewBox, content });

    iconCache.set(fileName, svgMarkup);
    return svgMarkup;
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(`Failed to load icon: ${fileName}`, error);
    return createSvg({ width, height });
  }
}

export const icon = (props: IconProps) => ({
  name: props.name,
  width: props.width || 16,
  height: props.height || 16,
  from: props.from || 'php',

  async init() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    $el.innerHTML = createSvg({ width: this.width, height: this.height });
    $el.classList.add('tutor-icon');

    if (!this.name) {
      return;
    }

    const svg = await fetchSVG(this.name, this.width, this.height);

    $el.innerHTML = svg;
  },

  async updateIcon(newName: string) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    this.name = newName;

    $el.innerHTML = createSvg({ width: this.width, height: this.height });

    const svg = await fetchSVG(this.name, this.width, this.height);
    $el.innerHTML = svg;
  },
});

export const iconMeta: AlpineComponentMeta<IconProps> = {
  name: 'icon',
  component: icon,
  global: true,
};
