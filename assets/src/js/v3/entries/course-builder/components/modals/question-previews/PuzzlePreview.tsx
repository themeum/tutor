import { __ } from '@wordpress/i18n';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';

import { type QuizQuestionOption } from '@TutorShared/utils/types';

const clampGridSize = (raw: number | undefined) => {
  const n = typeof raw === 'number' && !Number.isNaN(raw) ? raw : 4;
  return Math.max(2, Math.min(7, n));
};

interface PuzzlePreviewProps {
  answers: QuizQuestionOption[];
  gridSize?: number;
}

type Point = { x: number; y: number };

type PieceBounds = { minX: number; minY: number; maxX: number; maxY: number; width: number; height: number };

/** Aligned with Tutor Pro `puzzle-question.js` Side class. */
class Side {
  type: string;
  points: Point[];

  constructor(type = 'd', points: Point[] = []) {
    this.type = type;
    this.points = points;
  }

  reversed(): Side {
    if (!this.points.length) {
      return new Side(this.type, []);
    }

    if (this.type === 'd') {
      return new Side('d', [this.points[1], this.points[0]]);
    }

    const segments: { start: Point; c1: Point; c2: Point; end: Point }[] = [];
    for (let i = 0; i + 3 < this.points.length; i += 3) {
      segments.push({
        start: this.points[i],
        c1: this.points[i + 1],
        c2: this.points[i + 2],
        end: this.points[i + 3],
      });
    }

    const reversedPoints: Point[] = [segments[segments.length - 1].end];
    for (let i = segments.length - 1; i >= 0; i--) {
      const seg = segments[i];
      reversedPoints.push(seg.c2, seg.c1, seg.start);
    }

    return new Side('z', reversedPoints);
  }

  drawSrcPath(ctx: CanvasRenderingContext2D, isFirst = false): void {
    if (!this.points.length) {
      return;
    }

    const start = this.points[0];
    if (isFirst) {
      ctx.moveTo(start.x, start.y);
    } else {
      ctx.lineTo(start.x, start.y);
    }

    if (this.type === 'z') {
      for (let i = 1; i + 2 < this.points.length; i += 3) {
        const cp1 = this.points[i];
        const cp2 = this.points[i + 1];
        const end = this.points[i + 2];
        ctx.bezierCurveTo(cp1.x, cp1.y, cp2.x, cp2.y, end.x, end.y);
      }
      return;
    }

    const end = this.points[this.points.length - 1];
    ctx.lineTo(end.x, end.y);
  }
}

/** Aligned with Tutor Pro `puzzle-question.js` Piece class + generatePieceModels output. */
class PieceModel {
  row: number;
  col: number;
  ts: Side;
  rs: Side;
  bs: Side;
  ls: Side;
  x0: number;
  y0: number;
  x1: number;
  y1: number;
  slotId: string;
  bounds: PieceBounds;

  constructor(params: {
    row: number;
    col: number;
    ts: Side;
    rs: Side;
    bs: Side;
    ls: Side;
    x0: number;
    y0: number;
    x1: number;
    y1: number;
    slotId: string;
  }) {
    this.row = params.row;
    this.col = params.col;
    this.ts = params.ts;
    this.rs = params.rs;
    this.bs = params.bs;
    this.ls = params.ls;
    this.x0 = params.x0;
    this.y0 = params.y0;
    this.x1 = params.x1;
    this.y1 = params.y1;
    this.slotId = params.slotId;
    this.bounds = this.calculateBounds();
  }

  allSides(): Side[] {
    return [this.ts, this.rs, this.bs, this.ls];
  }

  calculateBounds(): PieceBounds {
    let minX = Number.POSITIVE_INFINITY;
    let minY = Number.POSITIVE_INFINITY;
    let maxX = Number.NEGATIVE_INFINITY;
    let maxY = Number.NEGATIVE_INFINITY;

    this.allSides().forEach((side) => {
      side.points.forEach((point) => {
        minX = Math.min(minX, point.x);
        minY = Math.min(minY, point.y);
        maxX = Math.max(maxX, point.x);
        maxY = Math.max(maxY, point.y);
      });
    });

    return {
      minX,
      minY,
      maxX,
      maxY,
      width: maxX - minX,
      height: maxY - minY,
    };
  }
}

const createSeededPrng = (seed: number): (() => number) => {
  let state = seed >>> 0;
  return () => {
    state += 0x6d2b79f5;
    let t = state;
    t = Math.imul(t ^ (t >>> 15), t | 1);
    t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
};

const seedFromUrl = (url: string): number => {
  let hash = 2166136261;
  for (let i = 0; i < url.length; i++) {
    hash ^= url.charCodeAt(i);
    hash = Math.imul(hash, 16777619);
  }
  return hash >>> 0;
};

const twist0 = (start: Point, end: Point, prng: () => number, normalSign: number): Side => {
  const dx = end.x - start.x;
  const dy = end.y - start.y;
  const len = Math.sqrt(dx * dx + dy * dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const nx = -uy * normalSign;
  const ny = ux * normalSign;
  const jitter = (min: number, max: number) => min + (max - min) * prng();
  const radius = len * jitter(0.16, 0.21);
  const center = len * jitter(0.45, 0.55);
  const shoulderLeft = Math.max(len * 0.2, center - radius);
  const shoulderRight = Math.min(len * 0.8, center + radius);
  const adjustedRadius = (shoulderRight - shoulderLeft) / 2;
  const adjustedCenter = shoulderLeft + adjustedRadius;
  const kappa = 0.5522847498307936;

  const toWorld = (u: number, v: number): Point => ({
    x: start.x + ux * u + nx * v,
    y: start.y + uy * u + ny * v,
  });

  const p0 = toWorld(0, 0);
  const p1 = toWorld(shoulderLeft, 0);
  const p2 = toWorld(adjustedCenter, adjustedRadius);
  const p3 = toWorld(shoulderRight, 0);
  const p4 = toWorld(len, 0);

  const c1 = toWorld(shoulderLeft * 0.35, 0);
  const c2 = toWorld(shoulderLeft * 0.75, 0);

  const c3 = toWorld(shoulderLeft, adjustedRadius * kappa);
  const c4 = toWorld(adjustedCenter - adjustedRadius * kappa, adjustedRadius);

  const c5 = toWorld(adjustedCenter + adjustedRadius * kappa, adjustedRadius);
  const c6 = toWorld(shoulderRight, adjustedRadius * kappa);

  const tail = len - shoulderRight;
  const c7 = toWorld(shoulderRight + tail * 0.25, 0);
  const c8 = toWorld(shoulderRight + tail * 0.7, 0);

  return new Side('z', [p0, c1, c2, p1, c3, c4, p2, c5, c6, p3, c7, c8, p4]);
};

const generatePieceModels = (
  gridSize: number,
  sourcePieceWidth: number,
  sourcePieceHeight: number,
  imageUrl: string,
): PieceModel[] => {
  const prng = createSeededPrng(seedFromUrl(imageUrl) + gridSize * 97);
  const carryTop = new Map<string, Side>();
  const carryLeft = new Map<string, Side>();
  const pieces: PieceModel[] = [];
  const max = gridSize - 1;

  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      const x0 = col * sourcePieceWidth;
      const y0 = row * sourcePieceHeight;
      const x1 = x0 + sourcePieceWidth;
      const y1 = y0 + sourcePieceHeight;
      const key = `${row}:${col}`;

      const ts =
        row === 0
          ? new Side('d', [
              { x: x0, y: y0 },
              { x: x1, y: y0 },
            ])
          : carryTop.get(key)!;
      const ls =
        col === 0
          ? new Side('d', [
              { x: x0, y: y1 },
              { x: x0, y: y0 },
            ])
          : carryLeft.get(key)!;

      let rs: Side;
      if (col === max) {
        rs = new Side('d', [
          { x: x1, y: y0 },
          { x: x1, y: y1 },
        ]);
      } else {
        rs = twist0({ x: x1, y: y0 }, { x: x1, y: y1 }, prng, prng() > 0.5 ? 1 : -1);
        carryLeft.set(`${row}:${col + 1}`, rs.reversed());
      }

      let bs: Side;
      if (row === max) {
        bs = new Side('d', [
          { x: x1, y: y1 },
          { x: x0, y: y1 },
        ]);
      } else {
        bs = twist0({ x: x1, y: y1 }, { x: x0, y: y1 }, prng, prng() > 0.5 ? 1 : -1);
        carryTop.set(`${row + 1}:${col}`, bs.reversed());
      }

      pieces.push(
        new PieceModel({
          row,
          col,
          ts,
          rs,
          bs,
          ls,
          x0,
          y0,
          x1,
          y1,
          slotId: `slot-r${row}-c${col}`,
        }),
      );
    }
  }

  return pieces;
};

function shufflePieceModels<T>(models: T[], imageUrl: string, gridSize: number): T[] {
  const copy = [...models];
  const prng = createSeededPrng(seedFromUrl(imageUrl) + gridSize * 424242);
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(prng() * (i + 1));
    const t = copy[i];
    copy[i] = copy[j];
    copy[j] = t;
  }
  return copy;
}

/** Same as TutorPuzzleQuestion.renderPieceVisual(..., false) for scatter-sized cells. */
const renderJigsawPieceDataUrl = (
  model: PieceModel,
  sourceImage: CanvasImageSource,
  sourceWidth: number,
  sourceHeight: number,
  sourcePieceWidth: number,
  sourcePieceHeight: number,
  displayCellWidth: number,
  displayCellHeight: number,
): { dataUrl: string; width: number; height: number } => {
  if (!sourcePieceWidth || !sourcePieceHeight) {
    return { dataUrl: '', width: displayCellWidth, height: displayCellHeight };
  }

  const scaleX = displayCellWidth / sourcePieceWidth;
  const scaleY = displayCellHeight / sourcePieceHeight;
  const renderWidth = Math.max(8, Math.round(model.bounds.width * scaleX));
  const renderHeight = Math.max(8, Math.round(model.bounds.height * scaleY));

  const canvas = document.createElement('canvas');
  canvas.width = renderWidth;
  canvas.height = renderHeight;

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    return { dataUrl: '', width: renderWidth, height: renderHeight };
  }

  ctx.clearRect(0, 0, renderWidth, renderHeight);
  ctx.save();
  ctx.scale(scaleX, scaleY);
  ctx.translate(-model.bounds.minX, -model.bounds.minY);
  ctx.beginPath();
  model.ts.drawSrcPath(ctx, true);
  model.rs.drawSrcPath(ctx, false);
  model.bs.drawSrcPath(ctx, false);
  model.ls.drawSrcPath(ctx, false);
  ctx.closePath();
  ctx.clip();
  ctx.drawImage(sourceImage, 0, 0, sourceWidth, sourceHeight);

  ctx.beginPath();
  model.ts.drawSrcPath(ctx, true);
  model.rs.drawSrcPath(ctx, false);
  model.bs.drawSrcPath(ctx, false);
  model.ls.drawSrcPath(ctx, false);
  ctx.closePath();
  ctx.lineWidth = 1 / Math.max(scaleX, scaleY, 0.001);
  ctx.lineJoin = 'round';
  ctx.lineCap = 'round';
  ctx.strokeStyle = '#000000';
  ctx.stroke();
  ctx.restore();

  let dataUrl = '';
  try {
    dataUrl = canvas.toDataURL('image/png');
  } catch {
    dataUrl = '';
  }

  return { dataUrl, width: renderWidth, height: renderHeight };
};

type PieceEntry = { key: string; src: string; width: number; height: number };

/**
 * Static preview for Puzzle questions — same DOM/classes as
 * tutor-pro/templates/learning-area/quiz/questions/puzzle.php (no hidden input / Alpine).
 * Scatter pieces use the same jigsaw geometry + canvas rendering as `puzzle-question.js`.
 */
const PuzzlePreview = ({ answers, gridSize: gridSizeProp }: PuzzlePreviewProps) => {
  const answer = answers[0];
  const imageUrl = answer?.image_url || answer?.answer_two_gap_match || '';
  const gridSize = clampGridSize(gridSizeProp);

  const outerClassName = 'quiz-question-ans-choice-area tutor-mt-40 tutor-puzzle-question question-type-puzzle';
  const playgroundRef = useRef<HTMLDivElement>(null);
  const [scatterCell, setScatterCell] = useState(60);
  const [pieces, setPieces] = useState<PieceEntry[]>([]);

  useLayoutEffect(() => {
    const el = playgroundRef.current;
    if (!el || typeof ResizeObserver === 'undefined') {
      return;
    }
    const update = () => {
      const side = Math.min(el.clientWidth, el.clientHeight);
      const targetPiece = gridSize > 0 && side > 0 ? side / gridSize : 0;
      const scatter = targetPiece > 0 ? Math.max(56, Math.min(80, targetPiece * 0.6)) : 60;
      setScatterCell(scatter);
    };
    update();
    const ro = new ResizeObserver(update);
    ro.observe(el);
    return () => ro.disconnect();
  }, [gridSize]);

  useEffect(() => {
    if (!imageUrl) {
      setPieces([]);
      return;
    }

    let cancelled = false;
    const img = new Image();

    img.onload = () => {
      if (cancelled) {
        return;
      }
      const sw = img.naturalWidth || img.width;
      const sh = img.naturalHeight || img.height;
      if (!sw || !sh) {
        setPieces([]);
        return;
      }

      const spw = sw / gridSize;
      const sph = sh / gridSize;
      const models = generatePieceModels(gridSize, spw, sph, imageUrl);
      const ordered = shufflePieceModels(models, imageUrl, gridSize);
      const next: PieceEntry[] = [];

      for (const model of ordered) {
        const { dataUrl, width, height } = renderJigsawPieceDataUrl(
          model,
          img,
          sw,
          sh,
          spw,
          sph,
          scatterCell,
          scatterCell,
        );
        if (!dataUrl) {
          continue;
        }
        next.push({ key: `${model.row}-${model.col}`, src: dataUrl, width, height });
      }

      if (!cancelled) {
        setPieces(next);
      }
    };

    img.onerror = () => {
      if (!cancelled) {
        setPieces([]);
      }
    };

    img.src = imageUrl;

    return () => {
      cancelled = true;
    };
  }, [imageUrl, gridSize, scatterCell]);

  if (!imageUrl) {
    return (
      <div className={outerClassName}>
        <p className="tutor-fs-7 tutor-color-secondary">
          {__('No source image configured for this Puzzle question.', 'tutor')}
        </p>
      </div>
    );
  }

  return (
    <div className={outerClassName}>
      <div ref={playgroundRef} className="tutor-puzzle-playground">
        <img
          className="tutor-puzzle-reference-image"
          src={imageUrl}
          alt={__('Puzzle reference image', 'tutor')}
          style={{ opacity: 0.3 }}
        />
        <div className="tutor-puzzle-slots" />
      </div>
      <div className="tutor-puzzle-scatter">
        {pieces.map((p) => (
          <div
            key={p.key}
            className="tutor-puzzle-piece is-small tutor-puzzle-piece--preview-static"
            style={{
              flex: '0 0 auto',
              width: p.width,
              height: p.height,
              boxSizing: 'border-box',
            }}
          >
            <img src={p.src} alt="" width={p.width} height={p.height} style={{ display: 'block' }} />
          </div>
        ))}
      </div>
    </div>
  );
};

export default PuzzlePreview;
