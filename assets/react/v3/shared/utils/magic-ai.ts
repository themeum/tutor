export function drawGradientLine(context: CanvasRenderingContext2D, x1: number, y1: number, x2: number, y2: number) {
  context.lineTo(x2, y2);

  const gradient = context.createLinearGradient(x1, y1, x2, y2);
  gradient.addColorStop(0, 'rgba(255, 150, 69, 0.4)');
  gradient.addColorStop(0.25, 'rgba(255, 100, 113, 0.4)');
  gradient.addColorStop(0.5, 'rgba(207, 110, 189, 0.4)');
  gradient.addColorStop(0.75, 'rgba(164, 119, 209, 0.4)');
  gradient.addColorStop(1, 'rgba(62, 100, 222, 0.4)');

  context.strokeStyle = gradient;
  context.stroke();
}
