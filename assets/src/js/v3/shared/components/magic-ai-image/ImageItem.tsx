import { css, keyframes } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import MagicButton from '@TutorShared/atoms/MagicButton';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useStoreAIGeneratedImageMutation } from '@TutorShared/services/magic-ai';
import { downloadBase64Image } from '@TutorShared/utils/magic-ai';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { Option } from '@TutorShared/utils/types';
import { nanoid } from '@TutorShared/utils/util';

import { type DropdownState, useMagicImageGeneration } from './ImageContext';

const options: Option<DropdownState>[] = [
  {
    label: __('Magic Fill', __TUTOR_TEXT_DOMAIN__),
    value: 'magic-fill',
    icon: <SVGIcon name="magicWand" width={24} height={24} />,
  },
  // @TODO: will be implemented in the future
  // {
  //   label: __('Object eraser', __TUTOR_TEXT_DOMAIN__),
  //   value: 'magic-erase',
  //   icon: <SVGIcon name="eraser" width={24} height={24} />,
  // },
  // {
  //   label: __('Variations', __TUTOR_TEXT_DOMAIN__),
  //   value: 'variations',
  //   icon: <SVGIcon name="reload" width={24} height={24} />,
  // },
  {
    label: __('Download', __TUTOR_TEXT_DOMAIN__),
    value: 'download',
    icon: <SVGIcon name="download" width={24} height={24} />,
  },
];

export const AiImageItem = ({ src, loading, index }: { src: string | null; loading: boolean; index: number }) => {
  const ref = useRef<HTMLButtonElement>(null);
  const [isOpen, setIsOpen] = useState(false);
  const { onDropdownMenuChange, setCurrentImage, onCloseModal, field } = useMagicImageGeneration();
  const storeAIGeneratedImageMutation = useStoreAIGeneratedImageMutation();

  if (loading || !src) {
    return <div css={styles.loader(index + 1)} />;
  }

  return (
    <>
      <div
        css={styles.image({
          isActive: storeAIGeneratedImageMutation.isPending,
        })}
      >
        <img src={src} alt={__('Generated Image', __TUTOR_TEXT_DOMAIN__)} />
        <div data-actions>
          <div css={styles.useButton}>
            <MagicButton
              variant="primary"
              disabled={storeAIGeneratedImageMutation.isPending}
              onClick={async () => {
                if (!src) {
                  return;
                }
                const response = await storeAIGeneratedImageMutation.mutateAsync({ image: src });

                if (response.data) {
                  field.onChange(response.data);
                  onCloseModal();
                }
              }}
              loading={storeAIGeneratedImageMutation.isPending}
            >
              <SVGIcon name="download" width={24} height={24} />
              {__('Use This', __TUTOR_TEXT_DOMAIN__)}
            </MagicButton>
          </div>
          <MagicButton variant="primary" size="icon" css={styles.threeDots} ref={ref} onClick={() => setIsOpen(true)}>
            <SVGIcon name="threeDotsVertical" width={24} height={24} />
          </MagicButton>
        </div>
      </div>
      <Popover
        triggerRef={ref}
        isOpen={isOpen}
        arrow={true}
        closePopover={() => {
          setIsOpen(false);
        }}
        animationType={AnimationType.slideDown}
        maxWidth="160px"
      >
        <div css={styles.dropdownOptions}>
          <For each={options}>
            {(option, index) => (
              <button
                type="button"
                key={index}
                css={styles.dropdownItem}
                onClick={() => {
                  switch (option.value) {
                    case 'magic-fill': {
                      setCurrentImage(src);
                      onDropdownMenuChange(option.value);
                      break;
                    }

                    case 'download': {
                      const filename = `${nanoid()}.png`;
                      downloadBase64Image(src, filename);
                      break;
                    }
                    default:
                      break;
                  }
                  setIsOpen(false);
                }}
              >
                {option.icon}
                {option.label}
              </button>
            )}
          </For>
        </div>
      </Popover>
    </>
  );
};

const loader = keyframes`
		0% {
      opacity: 0.3;
    }
		25% {
			opacity: 0.5;
		}
    50% {
      opacity: 0.7;
    }
		75% {
			opacity: 0.5;
		}
    100% {
      opacity: 0.3;
    }
`;

const styles = {
  loader: (index: number) => css`
    border-radius: ${borderRadius[12]};
    background: linear-gradient(
      73.09deg,
      #ff9645 18.05%,
      #ff6471 30.25%,
      #cf6ebd 55.42%,
      #a477d1 71.66%,
      #3e64de 97.9%
    );
    position: relative;
    width: 100%;
    height: 100%;
    background-size: 612px 612px;
    opacity: 0.3;
    transition: opacity 0.5s ease;
    animation: ${loader} 2s linear infinite;

    ${index === 1 &&
    css`
      background-position: top left;
    `}
    ${index === 2 &&
    css`
      background-position: top right;
      animation-delay: 0.5s;
    `}
		${index === 3 &&
    css`
      background-position: bottom left;
      animation-delay: 1.5s;
    `}
		${index === 4 &&
    css`
      background-position: bottom right;
      animation-delay: 1s;
    `}
  `,
  image: ({ isActive }: { isActive: boolean }) => css`
    width: 100%;
    height: 100%;
    overflow: hidden;
    border-radius: ${borderRadius[12]};
    position: relative;
    outline: 2px solid transparent;
    outline-offset: 2px;
    transition: border-radius 0.3s ease;

    [data-actions] {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    ${isActive &&
    css`
      outline-color: ${colorTokens.stroke.brand};

      [data-actions] {
        opacity: 1;
      }
    `}

    &:hover, &:focus-within {
      outline-color: ${colorTokens.stroke.brand};
      [data-actions] {
        opacity: 1;
      }
    }
  `,
  threeDots: css`
    position: absolute;
    top: ${spacing[8]};
    right: ${spacing[8]};
    border-radius: ${borderRadius[4]};
  `,
  useButton: css`
    position: absolute;
    left: 50%;
    bottom: ${spacing[12]};
    transform: translateX(-50%);

    button {
      display: inline-flex;
      align-items: center;
      gap: ${spacing[4]};
    }
  `,
  dropdownOptions: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[8]};
  `,
  dropdownItem: css`
    ${typography.small()};
    ${styleUtils.resetButton};
    height: 40px;
    display: flex;
    gap: ${spacing[10]};
    align-items: center;
    transition: background-color 0.3s ease;
    color: ${colorTokens.text.title};
    padding-inline: ${spacing[8]};
    cursor: pointer;

    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
};
