export function getRequiredComponents(): string[] {
  const names = new Set<string>();

  const collectComponentName = (el: Element) => {
    const expression = el.getAttribute('x-data') ?? '';

    const componentExpression = /\btutor([A-Z][A-Za-z0-9]*)\b/g;
    let match: RegExpExecArray | null;

    while ((match = componentExpression.exec(expression)) !== null) {
      const componentName = match[1].charAt(0).toLowerCase() + match[1].slice(1);

      names.add(componentName);
    }
  };

  document.querySelectorAll('[x-data]').forEach(collectComponentName);

  document.querySelectorAll('template').forEach((template) => {
    template.content.querySelectorAll('[x-data]').forEach(collectComponentName);
  });

  return [...names];
}
