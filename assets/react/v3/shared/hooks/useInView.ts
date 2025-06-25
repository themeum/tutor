import { useCallback, useEffect, useRef, useState } from 'react';

const useInView = () => {
  const ref = useRef<HTMLDivElement | null>(null);
  const [isInView, setIsInView] = useState(false);

  const handleScroll = useCallback(() => {
    if (ref.current) {
      const { top, bottom } = ref.current.getBoundingClientRect();
      const isVisible = top >= 0 && bottom <= window.innerHeight;
      setIsInView(isVisible);
    }
  }, []);

  useEffect(() => {
    handleScroll();

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, [handleScroll]);

  return { ref, isInView };
};

export default useInView;
