export type PlayerProvider = 'html5' | 'external' | 'youtube' | 'vimeo';

export type PlayerEvent =
  | 'ready'
  | 'play'
  | 'pause'
  | 'ended'
  | 'timeupdate'
  | 'seek'
  | 'volumechange'
  | 'enterfullscreen'
  | 'exitfullscreen'
  | 'error';

export interface PlayerEventPayload {
  player: TutorPlayer;
  originalEvent?: Event;
  currentTime: number;
  duration: number;
}

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
  on(event: PlayerEvent, handler: (payload: PlayerEventPayload) => void): void;
  off(event: PlayerEvent, handler?: (payload: PlayerEventPayload) => void): void;
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
