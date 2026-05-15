export type TutorCorePackName = 'core-base' | 'core-form-controls' | 'core-media-editor' | 'core-learning';

export type OptionalTutorCorePackName = Exclude<TutorCorePackName, 'core-base'>;
