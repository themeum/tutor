export type PlayerProvider = 'html5' | 'external' | 'youtube' | 'vimeo';

export interface PlayerEventMap {
  ready: { player: TutorPlayer };
  play: { player: TutorPlayer };
  pause: { player: TutorPlayer };
  ended: { player: TutorPlayer };
  timeupdate: { player: TutorPlayer; currentTime: number; duration: number };
  seek: { player: TutorPlayer; currentTime: number; duration: number };
  volumechange: { player: TutorPlayer; volume: number; muted: boolean };
  enterfullscreen: { player: TutorPlayer };
  exitfullscreen: { player: TutorPlayer };
  error: { player: TutorPlayer; error: Error };
}

export type PlayerEvent = keyof PlayerEventMap;

export type PlayerEventPayload<E extends PlayerEvent = PlayerEvent> = PlayerEventMap[E];

export interface PlayerOptions {
  provider?: PlayerProvider;
  src?: string;
  autoplay?: boolean;
  muted?: boolean;
  loop?: boolean;
  controls?: boolean;
  poster?: string;
  startTime?: number;
}

export interface TutorPlayer {
  readonly provider: PlayerProvider;
  readonly element: HTMLElement;
  readonly media: HTMLVideoElement | HTMLIFrameElement | null;

  currentTime: number;
  readonly duration: number;
  readonly paused: boolean;
  volume: number;
  muted: boolean;

  play(): Promise<void>;
  pause(): void;
  seek(time: number): void;
  enterFullscreen(): Promise<void>;
  exitFullscreen(): Promise<void>;
  on<E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): void;
  off<E extends PlayerEvent>(event: E, handler?: (payload: PlayerEventMap[E]) => void): void;
  dispose(): void;
}

export interface PlayerService {
  readonly active: TutorPlayer | null;
  create(target: HTMLElement | string, options?: PlayerOptions): TutorPlayer;
  getActive(): TutorPlayer | null;
  play(): void;
  pause(): void;
  seek(time: number): void;
  enterFullscreen(): Promise<void>;
  exitFullscreen(): Promise<void>;
}
