import { css } from '@emotion/react';

export enum ImageLayout {
	Fill = 'Fill',
	Responsive = 'Responsive',
	Fixed = 'Fixed',
	Intrinsic = 'Intrinsic',
}

export enum Placeholder {
	Blur = 'Blur',
	Empty = 'Empty',
}

export enum ImageLoading {
	Lazy = 'Lazy',
	Eager = 'Eager',
}

const styles = {
	wrapper: (layout: ImageLayout, width?: number | string, height?: number | string) => css`
    display: block;
    overflow: hidden;
    margin: 0;
    position: relative;

    ${
			layout === ImageLayout.Fill &&
			css`
      position: absolute;
      inset: 0;
    `
		}

    ${
			layout === ImageLayout.Intrinsic &&
			css`
      max-width: 100%;
    `
		}

    ${
			width &&
			css`
      width: ${width}px;
    `
		}

    ${
			height &&
			css`
      height: ${height}px;
    `
		}
		

    ${
			layout === ImageLayout.Fixed &&
			css`
      display: inline-block;
      position: relative;
      width: ${width}px;
      height: ${height}px;
    `
		}
  `,
	image: css`
    position: absolute;
    inset: 0;
    padding: 0;
    border: none;
    margin: auto;
    display: block;
    width: 0;
    height: 0;
    min-width: 100%;
    max-width: 100%;
    min-height: 100%;
    max-height: 100%;
  `,
};

type ImageProps = Omit<JSX.IntrinsicElements['img'], 'src' | 'srcSet' | 'alt' | 'width' | 'height' | 'loading'> & {
	src: string;
	alt: string;
	width?: number;
	height?: number;
	layout?: ImageLayout;
};

const Image = ({ src, alt, width = 0, height = 0, layout = ImageLayout.Intrinsic, ...otherProps }: ImageProps) => {
	if (layout !== ImageLayout.Fill) {
		if (!width && !height) {
			throw Error(
				`Image with src "${src}" must use "width" and "height" properties or layout={ImageLayout.Fill} property.`
			);
		}
	}

	return (
		<div css={styles.wrapper(layout, width, height)}>
			{/* biome-ignore lint/a11y/useAltText: <explanation> */}
			<img css={styles.image} src={src} srcSet={src} alt={alt} {...otherProps} />
		</div>
	);
};

export default Image;
