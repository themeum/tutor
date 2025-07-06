import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type SerializedStyles, css } from '@emotion/react';
import { type ReactNode, createRef, useEffect, useRef, useState } from 'react';

type OrientationType = 'horizontal' | 'vertical';

export interface TabItem<T> {
  label: string;
  value: T;
  icon?: ReactNode;
  count?: number;
  disabled?: boolean;
  activeBadge?: boolean;
}

interface TabsProps<T> {
  activeTab: T;
  onChange: (value: T) => void;
  tabList: TabItem<T>[];
  orientation?: OrientationType;
  disabled?: boolean;
  wrapperCss?: SerializedStyles;
}

interface ItemProperty {
  width: number;
  height: number;
  left: number;
  top: number;
}

const Tabs = <T extends string | number>({
  activeTab,
  onChange,
  tabList,
  orientation = 'horizontal',
  disabled = false,
  wrapperCss,
}: TabsProps<T>) => {
  const refs = useRef(tabList.map(() => createRef<HTMLButtonElement>()));
  const [properties, setProperties] = useState<Record<T, ItemProperty>>();

  useEffect(() => {
    const temptProperties = tabList.reduce(
      (accumulate, current, index) => {
        const ref = refs.current[index];

        const dimension = {
          width: ref.current?.offsetWidth || 0,
          height: ref.current?.offsetHeight || 0,
          left: ref.current?.offsetLeft || 0,
          top: ref.current?.offsetTop || 0,
        };

        accumulate[current.value] = dimension;
        return accumulate;
      },
      {} as Record<T, ItemProperty>,
    );

    setProperties(temptProperties);
  }, [tabList]);

  return (
    <div css={styles.container}>
      <div css={[styles.wrapper(orientation), wrapperCss]} role="tablist">
        {tabList.map((tab, index) => {
          return (
            <button
              key={index}
              onClick={() => {
                onChange(tab.value);
              }}
              css={styles.tabButton({ isActive: activeTab === tab.value, orientation })}
              disabled={disabled || tab.disabled}
              type="button"
              role="tab"
              aria-selected={activeTab === tab.value ? 'true' : 'false'}
              ref={refs.current[index]}
            >
              {tab.icon}
              {tab.label}
              {tab.count !== undefined && (
                <span> ({tab.count < 10 && tab.count > 0 ? `0${tab.count}` : tab.count})</span>
              )}
              {tab.activeBadge && <span css={styles.activeBadge} />}
            </button>
          );
        })}
      </div>
      <span css={styles.indicator(properties?.[activeTab] || { width: 0, height: 0, left: 0, top: 0 }, orientation)} />
    </div>
  );
};

export default Tabs;

const styles = {
  container: css`
    position: relative;
    width: 100%;
  `,
  wrapper: (orientation: OrientationType) => css`
    width: 100%;
    display: flex;
    justify-items: left;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: ${shadow.tabs};

    ${orientation === 'vertical' &&
    css`
      flex-direction: column;
      align-items: start;
      box-shadow: none;
    `}
  `,
  indicator: (property: ItemProperty, orientation: OrientationType) => css`
    width: ${property.width}px;
    height: 3px;
    position: absolute;
    left: ${property.left}px;
    bottom: 0;
    background: ${colorTokens.brand.blue};
    border-radius: ${borderRadius[4]} ${borderRadius[4]} 0 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0ms;

    :dir(rtl) {
      left: auto;
      right: ${property.left}px;
    }

    ${orientation === 'vertical' &&
    css`
      width: 3px;
      height: ${property.height}px;
      top: ${property.top}px;
      bottom: auto;
      border-radius: 0 ${borderRadius[4]} ${borderRadius[4]} 0;
    `}
  `,
  tabButton: ({ isActive, orientation }: { isActive: boolean; orientation: OrientationType }) => css`
    ${styleUtils.resetButton};
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[20]};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[6]};
    padding: ${spacing[12]} ${spacing[20]};
    color: ${colorTokens.text.subdued};
    min-width: 130px;
    position: relative;
    transition: color 0.3s ease-in-out;
    border-radius: 0px;

    &:hover,
    &:focus,
    &:active {
      background-color: transparent;
      color: ${colorTokens.text.subdued};
      box-shadow: none;
    }

    & > svg {
      color: ${colorTokens.icon.default};
    }

    ${orientation === 'vertical' &&
    css`
      width: 100%;
      border-bottom: 1px solid ${colorTokens.stroke.border};
      justify-content: flex-start;

      &:hover,
      &:focus,
      &:active {
        border-bottom: 1px solid ${colorTokens.stroke.border};
      }
    `}

    ${isActive &&
    css`
      &,
      &:hover,
      &:focus,
      &:active {
        background-color: ${colorTokens.background.white};
        color: ${colorTokens.text.primary};
      }

      & > span {
        color: ${colorTokens.text.subdued};
      }

      & > svg {
        color: ${colorTokens.icon.brand};
      }
    `}

    &:disabled {
      color: ${colorTokens.text.disable};

      &::before {
        background: ${colorTokens.text.disable};
      }
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: -2px;
      border-radius: ${borderRadius[4]};
    }
  `,
  activeBadge: css`
    display: inline-block;
    height: 8px;
    width: 8px;
    border-radius: ${borderRadius.circle};
    background-color: ${colorTokens.color.success[80]};
  `,
};
