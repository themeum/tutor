export function getRequiredComponents(): string[] {
  const names = new Set<string>();

  document.querySelectorAll('[x-data]').forEach((el) => {
    const expression = el.getAttribute('x-data') ?? '';

    const match = expression.match(/^tutor([A-Z][a-zA-Z0-9]*)\s*\(/);

    if (!match) {
      return;
    }

    const componentName = match[1].charAt(0).toLowerCase() + match[1].slice(1);

    names.add(componentName);
  });

  return [...names];
}
