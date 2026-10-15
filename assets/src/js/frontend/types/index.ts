type PlyrYouTubeInstance = Plyr & {
  embed: {
    seekTo(time: number): void;
  };
};

type PlyrVimeoInstance = Plyr & {
  mute?: () => void;
  unmute?: () => void;
};

export function isYouTubePlyr(instance: Plyr): instance is PlyrYouTubeInstance {
  return instance.provider === 'youtube';
}

export function isVimeoPlyr(instance: Plyr): instance is PlyrVimeoInstance {
  return instance.provider === 'vimeo';
}
