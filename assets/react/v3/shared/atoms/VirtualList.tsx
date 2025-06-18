import { css } from '@emotion/react';
import { styleUtils } from '@TutorShared/utils/style-utils';
import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';

interface VirtualListProps<T> {
  items: T[];
  height: number;
  itemHeight: number;
  renderItem: (item: T, index: number, style: React.CSSProperties) => React.ReactNode;
}

const DEFAULT_ITEM_HEIGHT = 40;
const DEFAULT_BUFFER = 8;

const VirtualList = <T,>({ items, height, itemHeight = DEFAULT_ITEM_HEIGHT, renderItem }: VirtualListProps<T>) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const [scrollTop, setScrollTop] = useState(0);
  const scrollingTimeoutRef = useRef<number>();

  useEffect(() => {
    if (containerRef.current) {
      setScrollTop(containerRef.current.scrollTop);
    }

    return () => {
      if (scrollingTimeoutRef.current) {
        cancelAnimationFrame(scrollingTimeoutRef.current);
      }
    };
  }, []);

  const handleScroll = useCallback((e: React.UIEvent<HTMLDivElement>) => {
    const target = e.target as HTMLDivElement;
    if (!target) return;

    if (scrollingTimeoutRef.current) {
      cancelAnimationFrame(scrollingTimeoutRef.current);
    }

    scrollingTimeoutRef.current = requestAnimationFrame(() => {
      setScrollTop(target.scrollTop);
    });
  }, []);

  const { visibleItems, startIndex, totalHeight } = useMemo(() => {
    const buffer = DEFAULT_BUFFER;
    const startIdx = Math.max(0, Math.floor(scrollTop / itemHeight) - buffer);
    const visibleCount = Math.ceil(height / itemHeight) + buffer * 2;
    const endIdx = Math.min(startIdx + visibleCount, items.length);

    return {
      visibleItems: items.slice(startIdx, endIdx),
      startIndex: startIdx,
      totalHeight: items.length * itemHeight,
    };
  }, [items, scrollTop, height, itemHeight]);

  if (height <= 0 || itemHeight <= 0) {
    // eslint-disable-next-line no-console
    console.warn('VirtualList: Invalid height or itemHeight provided');
    return null;
  }

  if (!items?.length) {
    return <div ref={containerRef} style={{ height }} />;
  }

  return (
    <div
      ref={containerRef}
      css={styles.wrapper}
      style={{
        height,
        width: '100%',
        position: 'relative',
      }}
      onScroll={handleScroll}
    >
      <div style={{ height: totalHeight, position: 'relative' }}>
        {visibleItems.map((item, index) => (
          <React.Fragment key={startIndex + index}>
            {renderItem(item, startIndex + index, {
              position: 'absolute',
              top: (startIndex + index) * itemHeight,
              height: itemHeight,
              width: '100%',
            })}
          </React.Fragment>
        ))}
      </div>
    </div>
  );
};

export default VirtualList;

const styles = {
  wrapper: css`
    ${styleUtils.overflowYAuto}
    scrollbar-gutter: auto;
  `,
};
