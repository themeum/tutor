import { useEffect, useState } from 'react';

export const useIsShiftHolding = () => {
  const [shiftHolding, setShiftHolding] = useState(false);

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key.toLowerCase() === 'shift') {
      setShiftHolding(true);
    }
  };
  const handleKeyUp = (event: KeyboardEvent) => {
    if (event.key.toLowerCase() === 'shift') {
      setShiftHolding(false);
    }
  };

  useEffect(() => {
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('keyup', handleKeyUp);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      window.removeEventListener('keyup', handleKeyUp);
    };
  }, []);

  return shiftHolding;
};
