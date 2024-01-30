import { borderRadius, colorPalate, fontFamily, shadow, spacing, zIndex } from '@Config/styles';
import { css } from '@emotion/react';

export const createGlobalCss = () => css`
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

  *,
  ::after,
  ::before {
    box-sizing: border-box;
    font-family: ${fontFamily.roboto};
  }

  html {
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
  }
  body {
    margin: 0;
    font-family: ${fontFamily.roboto};
  }

  main {
    display: block;
  }

  h1 {
    font-size: 2em;
    margin: 0.67em 0;
  }

  hr {
    box-sizing: content-box;
    height: 0;
    overflow: visible;
  }

  pre {
    font-family: monospace, monospace;
    font-size: 1em;
  }

  a {
    background-color: transparent;
  }

  abbr[title] {
    border-bottom: none;
    text-decoration: underline;
    text-decoration: underline dotted;
  }

  b,
  strong {
    font-weight: bolder;
  }

  code,
  kbd,
  samp {
    font-family: monospace, monospace;
    font-size: 1em;
  }

  small {
    font-size: 80%;
  }

  sub,
  sup {
    font-size: 75%;
    line-height: 0;
    position: relative;
    vertical-align: baseline;
  }

  sub {
    bottom: -0.25em;
  }

  sup {
    top: -0.5em;
  }

  img {
    border-style: none;
  }

  button,
  input,
  optgroup,
  select,
  textarea {
    font-family: inherit;
    font-size: 100%;
    line-height: 1.15;
    margin: 0;
  }

  button,
  input {
    overflow: visible;
  }

  button,
  select {
    text-transform: none;
  }

  button,
  [type='button'],
  [type='reset'],
  [type='submit'] {
    -webkit-appearance: button;
  }

  button::-moz-focus-inner,
  [type='button']::-moz-focus-inner,
  [type='reset']::-moz-focus-inner,
  [type='submit']::-moz-focus-inner {
    border-style: none;
    padding: 0;
  }

  button:-moz-focusring,
  [type='button']:-moz-focusring,
  [type='reset']:-moz-focusring,
  [type='submit']:-moz-focusring {
    outline: 1px dotted ButtonText;
  }

  fieldset {
    padding: 0.35em 0.75em 0.625em;
  }

  legend {
    box-sizing: border-box;
    color: inherit;
    display: table;
    max-width: 100%;
    padding: 0;
    white-space: normal;
  }

  progress {
    vertical-align: baseline;
  }

  textarea {
    overflow: auto;
  }

  [type='checkbox'],
  [type='radio'] {
    box-sizing: border-box;
    padding: 0;
  }

  [type='number']::-webkit-inner-spin-button,
  [type='number']::-webkit-outer-spin-button {
    height: auto;
  }

  [type='search'] {
    -webkit-appearance: textfield;
    outline-offset: -2px;
  }

  [type='search']::-webkit-search-decoration {
    -webkit-appearance: none;
  }

  ::-webkit-file-upload-button {
    -webkit-appearance: button;
    font: inherit;
  }

  details {
    display: block;
  }

  summary {
    display: list-item;
  }

  template {
    display: none;
  }

  [hidden] {
    display: none;
  }

  :is(h1, h2, h3, h4, h5, h6, p) {
    padding: 0;
    margin: 0;
  }
`;

export const styleUtils = {
  centeredFlex: css`
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
  `,
  flexCenter: (direction: 'row' | 'column' = 'row') => css`
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: row;

    ${direction === 'column' &&
    css`
      flex-direction: column;
    `}
  `,
  ulReset: css`
    list-style: none;
    padding: 0;
    margin: 0;
  `,
  resetButton: css`
    background: none;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    font-family: ${fontFamily.roboto};
    cursor: pointer;
  `,
  cardInnerSection: css`
    padding: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  fieldGroups: (gap: keyof typeof spacing) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[gap]};
  `,
  addAnotherOptionButton: css`
    background: none;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    font-family: ${fontFamily.roboto};
    display: flex;
    gap: ${spacing[8]};
    align-items: end;
    color: ${colorPalate.actions.primary.default};
    :hover {
      text-decoration: underline;
    }
  `,
  titleAliasWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  inlineSwitch: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
  `,
  overflowYAuto: css`
    overflow-y: auto;

    ::-webkit-scrollbar {
      background-color: ${colorPalate.basic.white};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorPalate.basic.secondary};
      border-radius: ${borderRadius[6]};
    }
  `,
  textEllipsis: css`
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  `,
};
