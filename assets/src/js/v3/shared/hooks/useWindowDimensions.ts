import { useEffect, useState } from 'react';

const hdDimensions = {
  innerHeight: 1080,
  innerWidth: 1920,
  outerHeight: 1080,
  outerWidth: 1920,
};

function getSize() {
  if (typeof window !== 'undefined') {
    return {
      innerHeight: window.innerHeight,
      innerWidth: window.innerWidth,
      outerHeight: window.outerHeight,
      outerWidth: window.outerWidth,
    };
  }

  return hdDimensions;
}

const useWindowDimensions = () => {
  const [dimensions, setDimensions] = useState(hdDimensions);

  useEffect(() => {
    const measure = () => setDimensions(getSize());
    measure();

    window.addEventListener('resize', measure);

    return () => {
      window.removeEventListener('resize', measure);
    };
  }, []);

  return dimensions;
};

export default useWindowDimensions;
