import AiButton from '@Atoms/AiButton';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import { AnimationType } from '@Hooks/useAnimation';
import Popover from '@Molecules/Popover';
import { downloadBase64Image } from '@Utils/magic-ai';
import { styleUtils } from '@Utils/style-utils';
import type { Option } from '@Utils/types';
import { nanoid } from '@Utils/util';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { type DropdownState, useMagicImageGeneration } from './ImageContext';

const options: Option<DropdownState>[] = [
  {
    label: 'Magic fill',
    value: 'magic-fill',
    icon: <SVGIcon name="magicWand" width={24} height={24} />,
  },
  {
    label: 'Object eraser',
    value: 'magic-erase',
    icon: <SVGIcon name="eraser" width={24} height={24} />,
  },
  {
    label: 'Variations',
    value: 'variations',
    icon: <SVGIcon name="reload" width={24} height={24} />,
  },
  {
    label: 'Download',
    value: 'download',
    icon: <SVGIcon name="download" width={24} height={24} />,
  },
];

export const AiImageItem = ({ src }: { src: string }) => {
  const ref = useRef<HTMLButtonElement>(null);
  const [isOpen, setIsOpen] = useState(false);
  const { onDropdownMenuChange, setCurrentImage, onCloseModal, field } = useMagicImageGeneration();
  return (
    <>
      <div css={styles.image}>
        <img src={src} alt={__('Generated Image', 'tutor')} />
        <div data-actions>
          <div css={styles.useButton}>
            <AiButton
              variant="primary"
              onClick={() => {
                field.onChange({ url: src });
                onCloseModal();
              }}
            >
              <SVGIcon name="download" width={24} height={24} />
              {__('Use this', 'tutor')}
            </AiButton>
          </div>
          <AiButton variant="primary" size="icon" css={styles.threeDots} ref={ref} onClick={() => setIsOpen(true)}>
            <SVGIcon name="threeDotsVertical" width={24} height={24} />
          </AiButton>
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

const styles = {
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
