import { isDefined, TableSelectedItems } from '@Utils/types';
import { range } from '@Utils/util';
import { useState } from 'react';

import { useIsShiftHolding } from './useIsShiftHolding';

interface OnSelectItem {
  itemId: number;
  itemIndex: number;
  isChecked: boolean;
}

interface UseTableItemsSelectionProps<T> {
  totalItemsList: T[] | undefined;
}

const initialSelectedTableItems: TableSelectedItems = {
  ids: [],
  indexes: [],
};

export const useTableItemsSelection = <T extends { id?: number }>({
  totalItemsList = [],
}: UseTableItemsSelectionProps<T>) => {
  const [selectedItems, setSelectedItems] = useState<TableSelectedItems>(initialSelectedTableItems);
  const [lastSelectedIndex, setLastSelectedIndex] = useState(-1);

  const isShiftHolding = useIsShiftHolding();

  const totalSelected = selectedItems.indexes.length;
  const totalItems = totalItemsList.length || 0;

  const onSelectItem = ({ itemId, itemIndex, isChecked }: OnSelectItem) => {
    let updatedItemIds: number[] = [];
    let updatedItemIndexes: number[] = [];

    if (!isShiftHolding || lastSelectedIndex === -1) {
      if (!isChecked) {
        updatedItemIds = selectedItems.ids.filter((productId) => productId !== itemId);
        updatedItemIndexes = selectedItems.indexes.filter((productIndex) => productIndex !== itemIndex);

        setSelectedItems({
          ids: updatedItemIds,
          indexes: updatedItemIndexes,
        });
        setLastSelectedIndex(itemIndex);
        return;
      }

      updatedItemIds = [...selectedItems.ids, itemId];
      updatedItemIndexes = [...selectedItems.indexes, itemIndex];

      setSelectedItems({
        ids: updatedItemIds,
        indexes: updatedItemIndexes,
      });
      setLastSelectedIndex(itemIndex);
      return;
    }

    const minimumIndex = Math.min(lastSelectedIndex, itemIndex);
    const maximumIndex = Math.max(lastSelectedIndex, itemIndex);

    const itemIds = totalItemsList.slice(minimumIndex, maximumIndex + 1).map((item) => item.id);
    const itemIndexes = range(maximumIndex + 1).filter((itemIndex) => itemIndex >= minimumIndex);

    if (!isChecked) {
      updatedItemIds = selectedItems.ids.filter((item) => !itemIds.includes(item));
      updatedItemIndexes = selectedItems.indexes.filter((itemIndex) => !itemIndexes.includes(itemIndex));

      setSelectedItems({
        ids: updatedItemIds,
        indexes: updatedItemIndexes,
      });
      setLastSelectedIndex(itemIndex);
      return;
    }

    updatedItemIds = [...new Set([...selectedItems.ids, ...itemIds])] as number[];
    updatedItemIndexes = [...new Set([...selectedItems.indexes, ...itemIndexes])];

    setSelectedItems({
      ids: updatedItemIds,
      indexes: updatedItemIndexes,
    });
    setLastSelectedIndex(itemIndex);
  };

  const onFireSingleItemAction = ({ itemId, itemIndex }: { itemId: number; itemIndex: number }) => {
    const updatedItemIds = selectedItems.ids.filter((previousSelectedId) => previousSelectedId !== itemId);
    const updatedItemIndexes = [];

    for (const currentIndex of selectedItems.indexes) {
      if (currentIndex === itemIndex) {
        continue;
      }

      const updatedIndex = currentIndex > itemIndex ? currentIndex - 1 : currentIndex;

      updatedItemIndexes.push(updatedIndex);
    }

    setSelectedItems({
      ids: updatedItemIds,
      indexes: updatedItemIndexes,
    });
    setLastSelectedIndex(itemIndex);
  };

  const toggleAllItems = () => {
    const selectedItemsIds =
      totalSelected === totalItems ? [] : totalItemsList.map((item, index) => (isDefined(item.id) ? item.id : index));

    const selectedIndex = totalSelected === totalItems ? [] : range(totalItems);

    setSelectedItems({
      ids: selectedItemsIds,
      indexes: selectedIndex,
    });
    setLastSelectedIndex(-1);
  };

  const resetSelections = () => {
    setSelectedItems(initialSelectedTableItems);
    setLastSelectedIndex(-1);
  };

  return {
    selectedItems,
    totalItems,
    totalSelected,
    onSelectItem,
    onFireSingleItemAction,
    toggleAllItems,
    resetSelections,
  };
};
