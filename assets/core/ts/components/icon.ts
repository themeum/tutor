import { type AlpineComponentMeta } from '@Core/ts/types';

import { tutorConfig } from '@TutorShared/config/config';

interface IconCacheEntry {
  svg?: string;
  loading?: boolean;
  promise?: Promise<string>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  error?: any;
}

const iconCache: Record<string, IconCacheEntry> = {};

export interface IconProps {
  name: string; // Use PHP Icon::<name> to get the name.
  width?: number;
  height?: number;
  from: 'php' | 'ts';
  ignoreKids?: boolean;
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

function fetchSVG(
  name: string,
  width: number,
  height: number,
  from: 'php' | 'ts' = 'ts',
  ignoreKids = false,
): Promise<string> {
  const fileName = from === 'php' ? name : name.trim().replace(/[A-Z]/g, (m) => '-' + m.toLowerCase());
  const hasKidsVariant = !ignoreKids && tutorConfig.is_kids_mode && tutorConfig.kids_icons_registry?.includes(fileName);

  const basePath = hasKidsVariant ? 'assets/icons/kids/' : 'assets/icons/';
  const url = `${tutorConfig.tutor_url}${basePath}${fileName}.svg`;
  const defaultViewBox = `0 0 ${width} ${height}`;

  if (iconCache[url]?.svg) {
    return Promise.resolve(iconCache[url].svg!);
  }

  if (iconCache[url]?.promise) {
    return iconCache[url].promise!;
  }

  const promise = fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then((svgText) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(svgText, 'image/svg+xml');
      const svgEl = doc.querySelector('svg');
      const viewBox = svgEl?.getAttribute('viewBox') || defaultViewBox;
      const fill = svgEl?.getAttribute('fill') || 'none';
      const content = svgEl?.innerHTML || '';

      const svgMarkup = createSvg({ width, height, viewBox, fill, content });
      iconCache[url] = { svg: svgMarkup };
      return svgMarkup;
    })
    .catch((error) => {
      iconCache[url] = { error };
      // eslint-disable-next-line no-console
      console.error(`Failed to load icon: ${fileName}`, error);
      return createSvg({ width, height });
    });

  iconCache[url] = { loading: true, promise };
  return promise;
}

export const icon = (props: IconProps) => ({
  name: props.name,
  width: props.width || 16,
  height: props.height || 16,
  from: props.from || 'php',
  ignoreKids: props.ignoreKids || false,

  async init() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    $el.innerHTML = createSvg({ width: this.width, height: this.height });
    $el.classList.add('tutor-icon');

    if (!this.name) {
      return;
    }

    const svg = await fetchSVG(this.name, this.width, this.height, this.from, this.ignoreKids);

    $el.innerHTML = svg;
  },

  async updateIcon(newName: string) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    this.name = newName;

    $el.innerHTML = createSvg({ width: this.width, height: this.height });

    const svg = await fetchSVG(this.name, this.width, this.height, this.from, this.ignoreKids);
    $el.innerHTML = svg;
  },
});

export const iconMeta: AlpineComponentMeta<IconProps> = {
  name: 'icon',
  component: icon,
  global: true,
};
