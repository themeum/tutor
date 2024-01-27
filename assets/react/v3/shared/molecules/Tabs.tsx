import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { createRef, useEffect, useRef, useState } from 'react';

export interface TabItem<T> {
  label: string;
  value: T;
  count?: number;
  disabled?: boolean;
}

interface TabsProps<T> {
  activeTab: T;
  onChange: (value: T) => void;
  tabList: TabItem<T>[];
  disabled?: boolean;
  wrapperCss?: SerializedStyles;
}

interface ItemProperty {
  width: number;
  left: number;
}

const Tabs = <T extends string | number>({
  activeTab,
  onChange,
  tabList,
  disabled = false,
  wrapperCss,
}: TabsProps<T>) => {
  const refs = useRef(tabList.map(() => createRef<HTMLButtonElement>()));
  const [properties, setProperties] = useState<Record<T, ItemProperty>>();

  useEffect(() => {
    const temptProperties = tabList.reduce((accumulate, current, index) => {
      const ref = refs.current[index];

      const dimension = {
        width: ref.current?.offsetWidth || 0,
        left: ref.current?.offsetLeft || 0,
      };

      accumulate[current.value] = dimension;
      return accumulate;
    }, {} as Record<T, ItemProperty>);

    setProperties(temptProperties);
  }, [tabList]);

  return (
    <div css={styles.container}>
      <div css={[styles.wrapper, wrapperCss]} role="tablist">
        {tabList.map((tab, index) => {
          return (
            <button
              key={index}
              onClick={() => {
                onChange(tab.value);
              }}
              css={styles.tabButton({ isActive: activeTab === tab.value })}
              tabIndex={activeTab === tab.value ? 0 : -1}
              disabled={disabled || tab.disabled}
              type="button"
              role="tab"
              aria-selected={activeTab === tab.value ? 'true' : 'false'}
              ref={refs.current[index]}
            >
              {tab.label}
              {tab.count !== undefined && (
                <span> ({tab.count < 10 && tab.count > 0 ? `0${tab.count}` : tab.count})</span>
              )}
            </button>
          );
        })}
      </div>
      <span css={styles.indicator(properties?.[activeTab] || { width: 0, left: 0 })} />
    </div>
  );
};

export default Tabs;

const styles = {
  container: css`
    position: relative;
    width: 100%;
  `,
  wrapper: css`
    ${typography.body()}
    width: 100%;
    display: flex;
    justify-items: left;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: ${shadow.tabs};
  `,
  indicator: (property: ItemProperty) => css`
    width: ${property.width}px;
    height: 3px;
    position: absolute;
    left: ${property.left}px;
    bottom: 0;
    background: ${colorPalate.actions.primary.default};
    border-radius: ${borderRadius[4]} ${borderRadius[4]} 0 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0ms;
  `,
  tabButton: ({ isActive }: { isActive: boolean }) => css`
    ${styleUtils.resetButton};
    padding: ${spacing[16]} ${spacing[20]};
    color: ${colorPalate.text.neutral};
    min-width: 130px;
    position: relative;
    transition: color 0.3s ease-in-out;

    ${isActive &&
    css`
      color: ${colorPalate.text.default};

      & > span {
        color: ${colorPalate.text.neutral};
      }
    `}

    &:disabled {
      color: ${colorPalate.text.neutral};

      &::before {
        background: ${colorPalate.text.neutral};
      }
    }
  `,
};
