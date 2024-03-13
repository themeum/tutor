import { rgba } from 'polished';

export const headerHeight = 64;
export const sidebarWidth = 355;
export const footerHeight = 56;

export const fontFamily = {
  roboto: "'Roboto', sans-serif;",
  sfProDisplay: "'SF Pro Display', sans-serif;",
} as const;

export const colorPalate = {
  basic: {
    surface: '#F0F4FA',
    onSurface: '#111213',
    onSurface06: rgba(17, 18, 19, 0.6),
    primary: { default: '#3366FF', fill60: '#BDCAF1', fill100: '#28408E' },
    secondary: '#C9D8ED',
    interactive: '#2E57D2',
    critical: '#E62305',
    success: { default: '#079874', fill100: '#075A2A' },
    highlight: '#5FD9FF',
    white: '#FFFFFF',
    black: { default: '#000000', fill70: '#41454F' },
    warning: {
      default: '#FFC046',
      fill50: '#FCE7C7',
    },
    danger: {
      fill30: '#FFF7F7',
      fill40: '#FEECEB',
      fill50: '#FDD9D7',
      fill80: '#EF5350',
      fill100: '#C62828',
    },
    dark: {
      fill60: '#5C5C69',
    },
  },
  // some other required
  header: {
    default: '#7A7E92',
    secondary: '#5A5D71',
  },
  tertiary: {
    default: '#EFF4FB',
  },
  // end of other required
  navigation: {
    surface: '#4C4F5F',
    expand: '#515467',
    hover: '#73768A',
  },
  background: {
    default: '#F6F6F7',
    hover: '#F1F2F3',
    pressed: '#EDEEEF',
    selected: '#EDEEEF',
  },
  text: {
    default: '#202123',
    neutral: '#6D6F75',
    disabled: '#8C8F96',
    navigation: '#E0E8FF',
    critical: '#E62305',
    warning: '#916A00',
    success: '#068464',
    highlight: '#347C84',
    dark: '#020B53',
    lightDark: '#73779B',
  },
  actions: {
    primary: {
      default: '#3366FF',
      hover: '#003DF5',
      pressed: '#002EB8',
      depressed: '#001F7A',
      disabled: '#F1F1F1',
      outlined: '#4490FF',
    },
    critical: {
      default: '#E62305',
      hover: '#BE1D04',
      pressed: '#961703',
      depressed: '#6E1102',
      disabled: '#F1F1F1',
    },
    secondary: {
      default: '#FFFFFF',
      hover: '#F6F6F7',
      pressed: '#F1F2F3',
      depressed: '#6D6F75',
      disabled: '#FFFFFF',
    },
  },
  icon: {
    default: '#5C5E62',
    neutral: '#8C8F96',
    hover: '#1A1B1D',
    pressed: '#44464A',
    disabled: '#BABCC3',
    critical: '#F02C09',
    warning: '#F2B300',
    success: '#079874',
    highlight: '#009ABF',
  },
  interactive: {
    default: '#2E57D2',
    hover: '#2649B0',
    depressed: '#1F3B8F',
    disabled: '#BDC1CC',
    critical: {
      default: '#E62305',
      hover: '#BE1D04',
      depressed: '#961703',
      disabled: '#FD9B8C',
    },
  },
  border: {
    default: '#8C8F96',
    neutral: '#C9CBCF',
    hover: '#999CA4',
    depressed: '#575859',
    disabled: '#D2D4D8',
    critical: {
      default: '#FB5339',
      neutral: '#EFA99E',
      disabled: '#FFAFA3',
    },
    success: {
      default: '#09C395',
      neutral: '#95C9BC',
    },
    highlight: {
      default: '#4493A7',
      neutral: '#98C2CD',
    },
    shadow: {
      neutral: '#BABDC4',
    },
  },
  surface: {
    default: '#FFFFFF',
    neutral: { default: '#FAFBFB', hover: '#DBDCDF', pressed: '#C9CBD0' },
    hover: '#F6F6F7',
    pressed: '#F1F2F3',
    pressed05: rgba('#F1F2F3', 0.5),
    depressed: '#EDEEEF',
    disabled: '#FAFBFB',
    selected: {
      default: '#F2F7FE',
      neutral: '#EDF4FE',
      pressed: '#E5EFFD',
    },
    warning: {
      default: '#FFE59D',
      neutral: '#FFF9EA',
      neutralHover: '#FFF7E2',
      subDuedPressed: '#FFF3D3',
    },
    critical: {
      default: '#FED8D1',
      neutral: '#FFF6F4',
      neutralHover: '#FFF2F0',
      neutralPressed: '#FFEBE8',
      subDuedDepressed: '#FEC3B9',
    },
    success: {
      default: '#AEE9DA',
      neutral: '#F1F8F6',
      subDuedHover: '#ECF6F3',
      subDuedPressed: '#E2F1ED',
    },
    highlight: {
      default: '#A4E8F2',
      neutral: '#EBF9FC',
      subDuedHover: '#E4F7FA',
      subDuedPressed: '#D5F3F8',
    },
    primary: {
      selected: '#F1F8F6',
      hover: '#B3D0C9',
      pressed: '#A2BCB5',
    },
  },
  chart: {
    barGraph: {
      pv: '#2D8EFF',
      uv: '#D5D7E0',
    },
  },
};

export const colorTokens = {
  brand: {
    blue: '#0049f8',
    black: '#092844',
  },
  text: {
    primary: '#212327',
    title: '#41454f',
    subdued: '#5b616f',
    hints: '#767c8e',
    disable: '#a4a8b2',
    white: '#ffffff',
    brand: '#3a62e0',
    success: '#239c46',
    warning: '#bd7e00',
    error: '#f44337',
    status: {
      processing: '#007a66',
      pending: '#a8710d',
      failed: '#cc1213',
      completed: '#097336',
      onHold: '#ac0640',
      cancelled: '#6f7073',
    },
  },
  surface: {
    tutor: '#ffffff',
    wordpress: '#f1f1f1',
    navbar: '#F5F5F5',
    courseBuilder: '#F8F8F8',
  },
  background: {
    brand: '#3e64de',
    white: '#ffffff',
    default: '#f4f6f9',
    hover: '#f5f6fa',
    active: '#f0f1f5',
    disable: '#ebecf0',
    modal: '#161616',
    dark10: '#212327',
    dark20: '#31343b',
    dark30: '#41454f',
    null: '#ffffff',
    status: {
      success: '#e5f5eb',
      warning: '#fdf4e3',
      drip: '#e9edfb',
      onHold: '#fae8ef',
      processing: '#e5f9f6',
      errorFail: '#ffebeb',
      cancelled: '#eceef2',
      refunded: '#e5f5f5',
    },
  },
  icon: {
    default: '#9197a8',
    hover: '#4b505c',
    subdued: '#7e838f',
    hints: '#b6b9c2',
    disable: {
      default: '#b8bdcc',
      background: '#cbced6',
    },
    white: '#ffffff',
    brand: '#446ef5',
    wp: '#007cba',
    error: '#f55e53',
    warning: '#ffb505',
    success: '#22a848',
    drop: '#4761b8',
    processing: '#00a388',
  },
  stroke: {
    default: '#c3c5cb',
    hover: '#9095a3',
    bold: '#41454f',
    disable: '#dcdfe5',
    divider: '#e0e2ea',
    border: '#cdcfd5',
    white: '#ffffff',
    brand: '#577fff',
    neutral: '#7391f0',
    success: '#4eba6d',
    warning: '#f5ba63',
    danger: '#ff9f99',
    status: {
      success: '#c8e5d2',
      warning: '#fae5c5',
      processing: '#c3e5e0',
      onHold: '#f1c1d2',
      cancelled: '#e1e1e8',
      refunded: '#ccebea',
      fail: '#fdd9d7',
    },
  },
  action: {
    primary: {
      default: '#3e64de',
      hover: '#3a5ccc',
      focus: '#00cceb',
      active: '#3453b8',
      disable: '#e3e6eb',
      wp: '#2271b1',
    },
    secondary: {
      default: '#e9edfb',
      hover: '#d6dffa',
      active: '#d0d9f2',
    },
    outline: {
      default: '#ffffff',
      hover: '#e9edfb',
      active: '#e1e7fa',
      disable: '#cacfe0',
    },
  },
  wordpress: {
    primary: '#2271b1',
    primaryLight: '#007cba',
    hoverShape: '#7faee6',
    sidebarChildText: '#4ea2e6',
    childBg: '#2d3337',
    mainBg: '#1e2327',
    text: '#b5bcc2',
  },
  design: {
    dark: '#1a1b1e',
    grey: '#41454f',
    white: '#ffffff',
    brand: '#3e64de',
    success: '#24a148',
    warning: '#ed9700',
    error: '#f44337',
  },
  primary: {
    main: '#3e64de',
    100: '#28408e',
    90: '#395bca',
    80: '#6180e4',
    70: '#95aaed',
    60: '#bdcaf1',
    50: '#d2dbf5',
    40: '#e9edfb',
    30: '#f6f8fd',
  },
  color: {
    black: {
      main: '#212327',
      100: '#0b0c0e',
      90: '#1a1b1e',
      80: '#31343b',
      70: '#41454f',
      60: '#5b616f',
      50: '#727889',
      40: '#9ca0ac',
      30: '#b4b7c0',
      20: '#c0c3cb',
      10: '#cdcfd5',
      8: '#e3e6eb',
      5: '#eff1f6',
      3: '#f4f6f9',
      2: '#fcfcfd',
      0: '#ffffff',
    },
    danger: {
      main: '#f44337',
      100: '#c62828',
      90: '#e53935',
      80: '#ef5350',
      70: '#e57373',
      60: '#fbb4af',
      50: '#fdd9d7',
      40: '#feeceb',
      30: '#fff7f7',
    },
    success: {
      main: '#24a148',
      100: '#075a2a',
      90: '#007a38',
      80: '#3aaa5a',
      70: '#6ac088',
      60: '#99d4ae',
      50: '#cbe9d5',
      40: '#e5f5eb',
      30: '#f5fbf7',
    },
    warning: {
      main: '#ed9700',
      100: '#895800',
      90: '#e08e00',
      80: '#f3a33c',
      70: '#f5ba63',
      60: '#f9d093',
      50: '#fce7c7',
      40: '#fdf4e3',
      30: '#fefbf4',
    },
  },
  bg: {
    gray20: '#e3e5eb',
    white: '#ffffff',
    error: '#f46363',
    success: '#24a148',
    light: '#f9fafc',
  },
  ribbon: {
    red: 'linear-gradient(to bottom, #ee0014 0%,#c10010 12.23%,#ee0014 100%)',
    orange: 'linear-gradient(to bottom, #ff7c02 0%,#df6c00 12.23%,#f78010 100%)',
    green: 'linear-gradient(to bottom, #02ff49 0%,#00bb35 12.23%,#04ca3c 100%)',
    blue: 'linear-gradient(to bottom, #0267ff 3.28%,#004bbb 12.23%,#0453ca 100%)',
  },
  additionals: {
    lightMint: '#ebfffb',
    lightPurple: '#f4e8f8',
    lightRed: '#ffebeb',
    lightYellow: '#fffaeb',
    lightCoffee: '#fcf4ee',
    lightPurple2: '#f7ebfe',
    lightBlue: '#edf1fd',
  },
};

export const spacing = {
  0: '0',
  2: '2px',
  4: '4px',
  6: '6px',
  8: '8px',
  10: '10px',
  12: '12px',
  16: '16px',
  20: '20px',
  24: '24px',
  28: '28px',
  32: '32px',
  36: '36px',
  40: '40px',
  48: '48px',
  56: '56px',
  64: '64px',
  72: '72px',
  96: '96px',
  128: '128px',
  256: '256px',
  512: '512px',
} as const;

export const fontSize = {
  10: '0.625rem',
  11: '0.688rem',
  12: '0.75rem',
  13: '0.813rem',
  14: '0.875rem',
  15: '0.938rem',
  16: '1rem',
  18: '1.125rem',
  20: '1.25rem',
  24: '1.5rem',
  28: '1.75rem',
  30: '1.875rem',
  32: '2rem',
  36: '2.25rem',
  40: '2.5rem',
  48: '3rem',
  56: '3.5rem',
  60: '3.75rem',
  64: '4rem',
  80: '5rem',
} as const;

export const fontWeight = {
  thin: 100,
  extraLight: 200,
  light: 300,
  regular: 400,
  medium: 500,
  semiBold: 600,
  bold: 700,
  extraBold: 800,
  black: 900,
} as const;

export const lineHeight = {
  12: '0.5rem',
  14: '0.75rem',
  15: '0.90rem',
  16: '1rem',
  18: '1.125rem',
  20: '1.25rem',
  21: '1.313rem',
  24: '1.5rem',
  26: '1.625rem',
  28: '1.75rem',
  32: '2rem',
  30: '1.875rem',
  34: '2.125rem',
  36: '2.25rem',
  40: '2.5rem',
  44: '2.75rem',
  48: '3rem',
  56: '3.5rem',
  58: '3.625rem',
  64: '4rem',
  70: '4.375rem',
  81: '5.063rem',
} as const;

export const letterSpacing = {
  tight: '-0.05em',
  normal: '0',
  wide: '0.05em',
  extraWide: '0.1em',
} as const;

export const shadow = {
  focus: '0px 0px 0px 1px #FFFFFF, 0px 0px 0px 3px #0049f8',
  button: '0px 1px 0.25px rgba(17, 18, 19, 0.08), inset 0px -1px 0.25px rgba(17, 18, 19, 0.24)',
  combinedButton:
    '0px 1px 0px rgba(0, 0, 0, 0.05), inset 0px -1px 0px #bcbfc3, inset 1px 0px 0px #bbbfc3, inset 0px 1px 0px #bbbfc3',
  combinedButtonExtend:
    '0px 1px 0px rgba(0, 0, 0, 0.05), inset 0px -1px 0px #bcbfc3, inset 1px 0px 0px #bbbfc3, inset 0px 1px 0px #bbbfc3, inset -1px 0px 0px #bbbfc3',
  insetButtonPressed: 'inset 0px 2px 0px rgba(17, 18, 19, 0.08)',
  card: '0px 2px 1px rgba(17, 18, 19, 0.05), 0px 0px 1px rgba(17, 18, 19, 0.25)',
  popover: '0px 6px 22px rgba(17, 18, 19, 0.08), 0px 4px 10px rgba(17, 18, 19, 0.1)',
  modal: '0px 0px 2px rgba(17, 18, 19, 0.2), 0px 30px 72px rgba(17, 18, 19, 0.2)',
  base: '0px 1px 3px rgba(17, 18, 19, 0.15)',
  input: '0px 1px 0px rgba(17, 18, 19, 0.05)',
  switch: '0px 2px 4px 0px #0000002A',
  tabs: 'inset 0px -1px 0px #dbdcdf',
  dividerTop: 'inset 0px 1px 0px #E4E5E7',
  underline: '0px 1px 0px #C9CBCF',
  drag: '3px 7px 8px 0px #00000014',
  scrollable: '0px -1px 4px 0px #00000014',
} as const;

export const borderRadius = {
  2: '2px',
  4: '4px',
  5: '5px',
  6: '6px',
  8: '8px',
  10: '10px',
  14: '14px',
  20: '20px',
  24: '24px',
  30: '30px',
  40: '40px',
  50: '50px',
  circle: '50%',
  card: '8px',
} as const;

export const zIndex = {
  negative: -1,
  positive: 1,
  dropdown: 2,
  level: 0,
  sidebar: 9,
  header: 10,
  footer: 10,
  modal: 25,
  highest: 9999999,
} as const;

export const SmallMobileBreakpoint = 480;
export const MobileBreakpoint = 768;
export const TabletBreakpoint = 992;
export const DesktopBreakpoint = 1280;

export const Breakpoint = {
  smallMobile: `@media(max-width: ${SmallMobileBreakpoint}px)`,
  mobile: `@media(max-width: ${MobileBreakpoint}px)`,
  smallTablet: `@media(max-width: ${TabletBreakpoint - 1}px)`,
  tablet: `@media(max-width: ${DesktopBreakpoint - 1}px)`,
  desktop: `@media(min-width: ${DesktopBreakpoint}px)`,
};

export const containerMaxWidth = 1006;
