import type { Option } from '@Utils/types';
import { useCallback, useEffect, useState } from 'react';

interface UseSelectKeyboardNavigationProps<T> {
  options: Option<T>[];
  isOpen: boolean;
  onSelect: (option: Option<T>) => void;
  onClose: () => void;
  selectedValue?: T | null;
}

export const useSelectKeyboardNavigation = <T>({
  options,
  isOpen,
  onSelect,
  onClose,
  selectedValue,
}: UseSelectKeyboardNavigationProps<T>) => {
  const [activeIndex, setActiveIndex] = useState<number>(-1);

  const handleKeyDown = useCallback(
    (event: KeyboardEvent) => {
      if (!isOpen) return;

      const findNextEnabledOption = (currentIndex: number, direction: 'up' | 'down'): number => {
        let nextIndex = currentIndex;
        const increment = direction === 'down' ? 1 : -1;

        do {
          nextIndex += increment;
          if (nextIndex < 0) nextIndex = options.length - 1;
          if (nextIndex >= options.length) nextIndex = 0;
        } while (nextIndex >= 0 && nextIndex < options.length && options[nextIndex].disabled);

        if (options[nextIndex]?.disabled) {
          return currentIndex;
        }

        return nextIndex;
      };

      switch (event.key) {
        case 'ArrowDown':
          event.preventDefault();
          setActiveIndex((prev) => {
            const next = findNextEnabledOption(prev === -1 ? 0 : prev, 'down');
            return next;
          });
          break;

        case 'ArrowUp':
          event.preventDefault();
          setActiveIndex((prev) => {
            const next = findNextEnabledOption(prev === -1 ? 0 : prev, 'up');
            return next;
          });
          break;

        case 'Enter':
          event.preventDefault();
          event.stopPropagation();

          if (activeIndex >= 0 && activeIndex < options.length) {
            const selectedOption = options[activeIndex];
            if (!selectedOption.disabled) {
              onClose();
              onSelect(selectedOption);
            }
          }
          break;

        case 'Escape':
          event.preventDefault();
          event.stopPropagation();
          onClose();
          break;

        default:
          break;
      }
    },
    [isOpen, options, activeIndex, onSelect, onClose],
  );

  useEffect(() => {
    if (isOpen) {
      if (activeIndex === -1) {
        const selectedIndex = options.findIndex((option) => option.value === selectedValue);
        const initialIndex = selectedIndex >= 0 ? selectedIndex : options.findIndex((option) => !option.disabled);
        setActiveIndex(initialIndex);
      }

      document.addEventListener('keydown', handleKeyDown, true);
      return () => document.removeEventListener('keydown', handleKeyDown, true);
    }
  }, [isOpen, handleKeyDown, options, selectedValue, activeIndex]);

  useEffect(() => {
    if (!isOpen) {
      setActiveIndex(-1);
    }
  }, [isOpen]);

  const handleMouseOver = useCallback(
    (index: number) => {
      if (!options[index]?.disabled) {
        setActiveIndex(index);
      }
    },
    [options],
  );

  return { activeIndex, setActiveIndex: handleMouseOver };
};
