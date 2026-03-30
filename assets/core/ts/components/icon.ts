import { type AlpineComponentMeta } from '@Core/ts/types';

const iconCache = new Map<string, string>();
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const tutorConfig = (window as any)._tutorobject || {};
const baseUrl = tutorConfig?.tutor_url || '';

type LearningMode = 'kids' | 'classic' | 'legacy';

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
  fill,
  content = '',
}: {
  width: number;
  height: number;
  viewBox?: string;
  fill?: string;
  content?: string;
}) =>
  `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="${viewBox || '0 0 ' + width + ' ' + height}" fill="${fill}">${content}</svg>`;

const getLearningMode = (): LearningMode => {
  return tutorConfig?.settings?.learning_mode || 'classic';
};

const getCacheKey = (fileName: string, learningMode: LearningMode) => `${learningMode}:${fileName}`;

async function fetchSVG(name: string, width: number, height: number, from: 'php' | 'ts' = 'ts') {
  const fileName = from === 'php' ? name : name.trim().replace(/[A-Z]/g, (m) => '-' + m.toLowerCase());
  const learningMode = getLearningMode();
  const cacheKey = getCacheKey(fileName, learningMode);

  if (iconCache.has(cacheKey)) {
    return iconCache.get(cacheKey)!;
  }

  const defaultViewBox = `0 0 ${width} ${height}`;
  const urls =
    learningMode === 'kids'
      ? [`${baseUrl}assets/icons/kids/${fileName}.svg`, `${baseUrl}assets/icons/${fileName}.svg`]
      : [`${baseUrl}assets/icons/${fileName}.svg`];

  try {
    for (const url of urls) {
      const response = await fetch(url);
      if (!response.ok) {
        continue;
      }

      const svgText = await response.text();

      const parser = new DOMParser();
      const doc = parser.parseFromString(svgText, 'image/svg+xml');
      const svgEl = doc.querySelector('svg');
      const viewBox = svgEl?.getAttribute('viewBox') || defaultViewBox;
      const fill = svgEl?.getAttribute('fill') || 'none';
      const content = svgEl?.innerHTML || '';

      const svgMarkup = createSvg({ width, height, viewBox, fill, content });

      iconCache.set(cacheKey, svgMarkup);
      return svgMarkup;
    }

    throw new Error(`Failed to load icon: ${fileName}`);
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
