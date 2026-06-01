export function getRequiredComponents(): string[] {
  const names = new Set<string>();

  const collectComponentName = (el: Element) => {
    const expression = el.getAttribute('x-data') ?? '';

    const match = expression.match(/^tutor([A-Z][a-zA-Z0-9]*)\s*\(/);

    if (!match) {
      return;
    }

    const componentName = match[1].charAt(0).toLowerCase() + match[1].slice(1);

    names.add(componentName);
  };

  document.querySelectorAll('[x-data]').forEach(collectComponentName);

  document.querySelectorAll('template').forEach((template) => {
    template.content.querySelectorAll('[x-data]').forEach(collectComponentName);
  });

  return [...names];
}
