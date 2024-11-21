import { colorTokens, fontFamily, fontSize, fontWeight, lineHeight } from '@Config/styles';
import { css } from '@emotion/react';

type TypefaceKeys = 'regular' | 'medium' | 'semiBold' | 'bold';

export const typography = {
  heading1: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[80]};
    line-height: ${lineHeight[81]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  heading2: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[60]};
    line-height: ${lineHeight[70]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  heading3: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[40]};
    line-height: ${lineHeight[48]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  heading4: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[30]};
    line-height: ${lineHeight[40]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  heading5: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[24]};
    line-height: ${lineHeight[34]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  heading6: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[20]};
    line-height: ${lineHeight[30]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  body: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[26]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  caption: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[24]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  small: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[13]};
    line-height: ${lineHeight[18]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
  tiny: (typeface: TypefaceKeys = 'regular') => css`
    font-size: ${fontSize[11]};
    line-height: ${lineHeight[16]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight[typeface]};
    font-family: ${fontFamily.sfProDisplay};
  `,
};
