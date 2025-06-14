import {
  borderRadius,
  colorTokens,
  containerMaxWidth,
  fontFamily,
  fontSize,
  shadow,
  spacing,
} from '@TutorShared/config/styles';
import { css } from '@emotion/react';
import { typography } from '../config/typography';

export const createGlobalCss = () => css`
  body:not(.tutor-screen-backend-settings, .tutor-backend-tutor-tools) {
    #wpcontent {
      padding-left: 0;
    }
  }

  *,
  ::after,
  ::before {
    box-sizing: border-box;
  }

  html {
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
  }
  body {
    margin: 0;
    font-family: ${fontFamily.inter};
    height: 100%;
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

    &:hover {
      color: inherit;
    }
  }

  li {
    list-style: none;
    margin: 0;
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
    height: auto;
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
    text-transform: unset;
  }

  table {
    th {
      text-align: -webkit-match-parent;
    }
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
  boxReset: css`
    padding: 0;
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
    box-shadow: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    font-family: ${fontFamily.inter};
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
    scrollbar-gutter: stable;

    ::-webkit-scrollbar {
      background-color: ${colorTokens.primary[40]};
      width: 3px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorTokens.design.brand};
      border-radius: ${borderRadius[30]};
    }
  `,
  overflowXAuto: css`
    overflow-x: auto;
    scrollbar-gutter: stable;

    ::-webkit-scrollbar {
      background-color: ${colorTokens.primary[40]};
      height: 3px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorTokens.design.brand};
      border-radius: ${borderRadius[30]};
    }
  `,

  textEllipsis: css`
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  `,
  container: css`
    width: ${containerMaxWidth}px;
    margin: 0 auto;
  `,
  display: {
    flex: (direction: 'row' | 'column' | 'row-reverse' | 'column-reverse' = 'row') => css`
      display: flex;
      flex-direction: ${direction};
    `,
    inlineFlex: (direction: 'row' | 'column' | 'row-reverse' | 'column-reverse' = 'row') => css`
      display: inline-flex;
      flex-direction: ${direction};
    `,
    none: css`
      display: none;
    `,
    block: css`
      display: block;
    `,
    inlineBlock: css`
      display: inline-block;
    `,
  },
  text: {
    ellipsis: (lines = 1) => css`
      white-space: normal;
      display: -webkit-box;
      -webkit-line-clamp: ${lines};
      -webkit-box-orient: vertical;
      overflow: hidden;
      -webkit-box-pack: end;
    `,
    align: {
      center: css`
        text-align: center;
      `,
      left: css`
        text-align: left;
      `,
      right: css`
        text-align: right;
      `,
      justify: css`
        text-align: justify;
      `,
    },
  },
  inputFocus: css`
    box-shadow: none;
    border-color: ${colorTokens.stroke.default};
    outline: 2px solid ${colorTokens.stroke.brand};
    outline-offset: 1px;
  `,
  dateAndTimeWrapper: css`
    display: grid;
    grid-template-columns: 5.5fr 4.5fr;
    border-radius: ${borderRadius[6]};

    &:focus-within {
      box-shadow: none;
      border-color: ${colorTokens.stroke.default};
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

    > div {
      &:first-of-type {
        input {
          border-top-right-radius: 0;
          border-bottom-right-radius: 0;
          &:focus {
            box-shadow: none;
            outline: none;
          }
        }
      }

      &:last-of-type {
        input {
          border-top-left-radius: 0;
          border-bottom-left-radius: 0;
          border-left: none;

          &:focus {
            box-shadow: none;
            outline: none;
          }
        }
      }
    }
  `,
  inputCurrencyStyle: css`
    font-size: ${fontSize[18]};
    color: ${colorTokens.icon.subdued};
  `,
  crossButton: css`
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background: ${colorTokens.background.white};
    transition: opacity 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  aiGradientText: css`
    background: ${colorTokens.text.ai.gradient};
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  `,
  actionButton: css`
    background: none;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    color: ${colorTokens.icon.default};
    display: flex;
    cursor: pointer;
    transition: color 0.3s ease-in-out;

    :hover:not(:disabled),
    :focus:not(:disabled),
    :active:not(:disabled) {
      background: none;
      color: ${colorTokens.icon.brand};
    }

    :disabled {
      color: ${colorTokens.icon.disable.background};
      cursor: not-allowed;
    }

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
      border-radius: ${borderRadius[2]};
    }
  `,
  backButton: css`
    background-color: transparent;
    width: 32px;
    height: 32px;
    padding: 0;
    margin: 0;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid ${colorTokens.border.neutral};
    border-radius: ${borderRadius[4]};
    outline: none;
    color: ${colorTokens.icon.default};
    transition: color 0.3s ease-in-out;
    cursor: pointer;

    :hover {
      color: ${colorTokens.icon.hover};
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }
  `,
  optionCheckButton: css`
    background: none;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    font-family: ${fontFamily.inter};
    cursor: pointer;
    height: 32px;
    width: 32px;
    border-radius: ${borderRadius.circle};
    opacity: 0;

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
    }
  `,
  optionCounter: ({ isEditing, isSelected = false }: { isEditing: boolean; isSelected?: boolean }) => css`
    height: ${spacing[24]};
    width: ${spacing[24]};
    border-radius: ${borderRadius.min};
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    background-color: ${colorTokens.background.default};
    text-align: center;
    ${isSelected &&
    !isEditing &&
    css`
      background-color: ${colorTokens.bg.white};
    `}
  `,
  optionDragButton: ({ isOverlay }: { isOverlay: boolean }) => css`
    background: none;
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    text-align: inherit;
    font-family: ${fontFamily.inter};
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(90deg);
    color: ${colorTokens.icon.default};
    cursor: grab;
    place-self: center center;
    border-radius: ${borderRadius[2]};

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.icon.default};
    }

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

    ${isOverlay &&
    css`
      cursor: grabbing;
    `}
  `,
  optionInputWrapper: css`
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: ${spacing[12]};

    input,
    textarea {
      background: none;
      border: none;
      outline: none;
      padding: 0;
      margin: 0;
      text-align: inherit;
      font-family: ${fontFamily.inter};
      ${typography.caption()};
      flex: 1;
      color: ${colorTokens.text.subdued};
      padding: ${spacing[4]} ${spacing[10]};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius[6]};
      resize: vertical;
      cursor: text;

      &:focus {
        box-shadow: none;
        border-color: ${colorTokens.stroke.default};
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
    }
  `,
  objectFit: (
    {
      fit,
      position,
    }: {
      fit: 'cover' | 'contain' | 'fill' | 'none' | 'scale-down';
      position: 'center' | 'top' | 'bottom' | 'left' | 'right';
    } = {
      fit: 'cover',
      position: 'center',
    },
  ) => css`
    object-fit: ${fit};
    object-position: ${position};
  `,
};
