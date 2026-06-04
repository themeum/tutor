export const decodeHtmlEntities = (text: string) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(text, 'text/html');
  return doc.body.textContent || '';
};
