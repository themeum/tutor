import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { playerMeta } from '@Core/ts/components/player';

export const registerCoreLearningPack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [playerMeta],
  });
};
