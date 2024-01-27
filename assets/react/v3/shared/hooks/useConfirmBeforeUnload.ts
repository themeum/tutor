export const useConfirmBeforeUnload = (
  callback: () => boolean,
  message = 'Are you sure to reload the window? You will lose all of your changes.',
) => {
  const response = callback();

  window.addEventListener(
    'beforeunload',
    (event) => {
      if (response) {
        event.preventDefault();
        event.returnValue = message;
      }
    },
    { capture: true },
  );
};
