export type PlayerProvider = 'html5' | 'external' | 'youtube' | 'vimeo';

export interface PlayerEventMap {
  ready: { player: TutorPlayer };
  play: { player: TutorPlayer };
  pause: { player: TutorPlayer };
  ended: { player: TutorPlayer };
  timeupdate: { player: TutorPlayer; currentTime: number; duration: number };
  volumechange: { player: TutorPlayer; volume: number; muted: boolean };
  fullscreenchange: { player: TutorPlayer; fullscreen: boolean; element: HTMLElement };
  enterfullscreen: { player: TutorPlayer; srcElement: HTMLElement; target?: HTMLElement };
  exitfullscreen: { player: TutorPlayer; srcElement: HTMLElement; target?: HTMLElement };
  error: { player: TutorPlayer; error: Error };
}

export type PlayerEvent = keyof PlayerEventMap;

export type PlayerUnsubscribe = () => void;

export interface TutorPlayer {
  readonly provider: PlayerProvider;
  readonly element: HTMLElement;
  readonly media: Element | null;

  getCurrentTime(): number;
  getDuration(): number;
  isPaused(): boolean;
  getVolume(): number;
  setVolume(volume: number): void;
  isMuted(): boolean;
  setMuted(muted: boolean): void;
  isFullscreen(): boolean;

  play(): Promise<void>;
  pause(): void;
  seek(time: number): void;

  on<E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): PlayerUnsubscribe;
  dispose(): void;
}

export interface PlayerService {
  create(target: HTMLElement): TutorPlayer;
  getActive(): TutorPlayer | null;

  getCurrentTime(): number;
  seek(time: number): void;
  on<E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): PlayerUnsubscribe;
}
