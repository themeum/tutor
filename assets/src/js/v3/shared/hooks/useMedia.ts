import useWindowDimensions from '@TutorShared/hooks/useWindowDimensions';

const useMedia = (dimensions: number) => {
  const { innerWidth } = useWindowDimensions();

  return innerWidth < dimensions;
};

export default useMedia;
