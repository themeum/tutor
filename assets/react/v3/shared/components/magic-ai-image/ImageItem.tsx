import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import { useStoreAIGeneratedImageMutation } from '@CourseBuilderServices/magic-ai';
import { AnimationType } from '@Hooks/useAnimation';
import Popover from '@Molecules/Popover';
import { downloadBase64Image } from '@Utils/magic-ai';
import { styleUtils } from '@Utils/style-utils';
import type { Option } from '@Utils/types';
import { nanoid } from '@Utils/util';
import { css, keyframes } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { type DropdownState, useMagicImageGeneration } from './ImageContext';

const options: Option<DropdownState>[] = [
  {
    label: __('Magic fill', 'tutor'),
    value: 'magic-fill',
    icon: <SVGIcon name="magicWand" width={24} height={24} />,
  },
  // @TODO: will be implemented in the future
  // {
  //   label: __('Object eraser', 'tutor'),
  //   value: 'magic-erase',
  //   icon: <SVGIcon name="eraser" width={24} height={24} />,
  // },
  // {
  //   label: __('Variations', 'tutor'),
  //   value: 'variations',
  //   icon: <SVGIcon name="reload" width={24} height={24} />,
  // },
  {
    label: __('Download', 'tutor'),
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
      <div css={styles.image}>
        <img src={src} alt={__('Generated Image', 'tutor')} />
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
              {__('Use this', 'tutor')}
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
		background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
		position: relative;
		width: 100%;
		height: 100%;
		background-size: 612px 612px;
		opacity: 0.3;
		transition: opacity 0.5s ease;
		animation: ${loader} 2s linear infinite;

		${
      index === 1 &&
      css`
			background-position: top left;
		`
    }
		${
      index === 2 &&
      css`
			background-position: top right;
			animation-delay: 0.5s;
		`
    }
		${
      index === 3 &&
      css`
			background-position: bottom left;
			animation-delay: 1.5s;
		`
    }
		${
      index === 4 &&
      css`
			background-position: bottom right;
			animation-delay: 1s;
		`
    }
	`,
  image: css`
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
