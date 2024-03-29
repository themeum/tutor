import { useEffect, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { typography } from '@Config/typography';
import { LocalStorageKeys, notebook } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { isDefined } from '@Utils/types';
import { jsonParse, throttle } from '@Utils/util';
import { styleUtils } from '@Utils/style-utils';
import Show from '@Controls/Show';
import { getFromLocalStorage, setToLocalStorage } from '@Utils/localStorage';

import { useResize } from '@Hooks/useResize';

interface Position {
  x: number;
  y: number;
}

interface NotebookData {
  position: {
    left: string;
    top: string;
    height: string;
    width: string;
  };
  content: string;
}

const Notebook = () => {
  const [isCollapsed, setIsCollapsed] = useState(true);
  const [isDragging, setIsDragging] = useState(false);
  const [isFloating, setIsFloating] = useState(false);
  const [offset, setOffset] = useState<Position>({ x: 0, y: 0 });
  const [contentEditable, setContentEditable] = useState(false);
  const [content, setContent] = useState('');

  const wrapperRef = useRef<HTMLDivElement>(null);
  const notebookRef = useRef<HTMLDivElement>(null);

  const expandAnimation = useSpring({
    height: !isCollapsed ? notebook.MIN_NOTEBOOK_HEIGHT : notebook.NOTEBOOK_HEADER,
    config: {
      duration: 300,
      easing: (t) => t * (2 - t),
    },
  });

  const { handleResize } = useResize({
    resizeDivRef: wrapperRef,
    options: {
      minHeight: notebook.MIN_NOTEBOOK_HEIGHT,
      minWidth: notebook.MIN_NOTEBOOK_WIDTH,
    },
  });

  const onContentBlur = (event: React.FocusEvent) => {
    setContentEditable(false);

    const notebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) ?? '{}');
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = event.currentTarget.innerHTML;
    const text = tempDiv.innerText ?? '';

    setToLocalStorage(
      LocalStorageKeys.notebook,
      JSON.stringify({
        ...notebookData,
        content: text,
      })
    );
  };

  const onContentPaste = (event: React.ClipboardEvent<HTMLDivElement>) => {
    event.preventDefault();

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = event.clipboardData?.getData('text/plain') ?? '';
    const text = tempDiv.innerText ?? '';

    const selection = window.getSelection();
    const selectedRange = selection?.getRangeAt(0);
    selectedRange?.deleteContents();

    const textNode = document.createTextNode(text);
    selectedRange?.insertNode(textNode);

    if (textNode && selection) {
      const newRange = document.createRange();
      newRange.setStartAfter(textNode);
      newRange.collapse(true);
      selection.removeAllRanges();
      selection.addRange(newRange);
    }
  };

  const handleMouseDown = (event: React.MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();

    if (isCollapsed) {
      return;
    }

    if (isDefined(notebookRef.current)) {
      notebookRef.current.blur();
    }

    setIsDragging(true);
    const { clientX, clientY } = event;
    const wrapper = wrapperRef.current;

    if (wrapper) {
      const rect = wrapper.getBoundingClientRect();
      setOffset({ x: clientX - rect.left, y: clientY - rect.top });
    }
  };

  const saveAfterResize = () => {
    if (isDefined(wrapperRef.current)) {
      const wrapper = wrapperRef.current;
      const { left, top, height, width } = wrapper.style;
      const notebookData: NotebookData = jsonParse<NotebookData>(
        getFromLocalStorage(LocalStorageKeys.notebook) || '{}'
      );

      setToLocalStorage(
        LocalStorageKeys.notebook,
        JSON.stringify({
          ...notebookData,
          position: {
            left,
            top,
            height,
            width,
          },
        })
      );
    }
  };

  useEffect(() => {
    const handleMouseMove = throttle((event: MouseEvent) => {
      if (!isDragging || !isDefined(wrapperRef.current)) {
        return;
      }
      setIsFloating(true);

      const warpper = wrapperRef.current;
      const { offsetWidth: notebookWidth, offsetHeight: notebookHeight } = warpper;
      const { innerWidth: windowWidth, innerHeight: windowHeight } = window;

      const newX = Math.min(Math.max(event.clientX - offset.x, 0), windowWidth - notebookWidth);
      const newY = Math.min(Math.max(event.clientY - offset.y, 0), windowHeight - notebookHeight);

      warpper.style.left = `${newX}px`;
      warpper.style.top = `${newY}px`;
    }, 10);

    const handleMouseUp = () => {
      setIsDragging(false);

      if (!isDefined(wrapperRef.current)) {
        return;
      }

      const wrapper = wrapperRef.current;
      const { left, top, height, width } = wrapper.style;
      const currentNotebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
      if (left === 'auto' || top === 'auto') {
        return;
      }

      setToLocalStorage(
        LocalStorageKeys.notebook,
        JSON.stringify({
          ...currentNotebookData,
          position: {
            left,
            top,
            height,
            width,
          },
        })
      );
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);

    return () => {
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };
  }, [isDragging, offset]);

  useEffect(() => {
    if (!isDefined(wrapperRef.current)) {
      return;
    }
    if (isCollapsed && !isFloating) {
      setIsFloating(false);
      const wrapper = wrapperRef.current;

      wrapper.style.left = 'auto';
      wrapper.style.top = 'auto';
      wrapper.style.width = '360px';
    }

    if (!isCollapsed && isFloating) {
      const notebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) ?? '{}');
      const { left, top, height, width } = notebookData.position || {
        left: 'auto',
        top: 'auto',
      };

      const wrapper = wrapperRef.current;

      wrapper.style.left = left === 'auto' ? `${window.innerWidth / 2 - notebook.MIN_NOTEBOOK_WIDTH}px` : left;
      wrapper.style.top = top === 'auto' ? `${window.innerHeight / 2 - notebook.MIN_NOTEBOOK_HEIGHT}px` : top;
      wrapper.style.height = isDefined(height) ? height : `${2 * notebook.MIN_NOTEBOOK_HEIGHT}px`;
      wrapper.style.width = isDefined(width) ? width : `${2 * notebook.MIN_NOTEBOOK_WIDTH}px`;
    }
  }, [isCollapsed, isFloating]);

  useEffect(() => {
    const notebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');

    if (notebookData.content) {
      setContent(notebookData.content);
    }
  }, []);

  return (
    <animated.div
      ref={wrapperRef}
      css={styles.wrapper({ isCollapsed, isFloating })}
      style={!isFloating ? { ...expandAnimation } : {}}
    >
      <div css={styles.header({ isCollapsed })} onMouseDown={handleMouseDown}>
        <span css={styleUtils.text.ellipsis(1)}>{__('Notebook', 'tutor')}</span>

        <div css={styles.actions}>
          <Button
            variant="text"
            size="small"
            onClick={() => {
              setIsCollapsed((previous) => !previous);
              setIsFloating(false);
            }}
            buttonCss={styles.collapseButton({ isCollapsed })}
          >
            <SVGIcon name="plusMinus" height={24} width={24} />
          </Button>
          <Button
            variant="text"
            size="small"
            onClick={() => {
              setIsCollapsed((previouState) => {
                if (isFloating) {
                  return true;
                }

                return previouState ? false : previouState;
              });
              setIsFloating((previous) => !previous);
            }}
          >
            <SVGIcon name="arrowsIn" height={24} width={24} />
          </Button>
          <Show when={isFloating}>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                setIsFloating(false);
                setIsCollapsed(true);
              }}
            >
              <SVGIcon name="cross" height={24} width={24} />
            </Button>
          </Show>
        </div>
      </div>
      <div css={styles.notebookWrapper}>
        <div
          ref={notebookRef}
          css={styles.notebook}
          contentEditable={contentEditable}
          onBlur={(event) => onContentBlur(event)}
          onPaste={(event) => onContentPaste(event)}
          onClick={() => setContentEditable(true)}
          onKeyDown={(event) => {
            if (event.key === 'Escape') {
              event.preventDefault();
              event.currentTarget.blur();
            }
          }}
          dangerouslySetInnerHTML={{ __html: content }}
        />
      </div>

      <Show when={isFloating && !isCollapsed}>
        <button
          type="button"
          css={[styleUtils.resetButton, styles.textFieldExpand]}
          onMouseDown={(event) => handleResize(event, 'bottom-right')}
          onMouseUp={saveAfterResize}
        >
          <SVGIcon name="textFieldExpand" height={16} width={16} />
        </button>
      </Show>
    </animated.div>
  );
};

export default Notebook;

const styles = {
  wrapper: ({
    isCollapsed,
    isFloating,
  }: {
    isCollapsed: boolean;
    isFloating: boolean;
  }) => css`
		position: fixed;
		background-color: ${colorTokens.background.active};
		bottom: 0;
		right: 0;
		width: 360px;
		border-radius: ${borderRadius.card} 0 ${borderRadius.card} 0;
		transition: box-shadow background 0.3s ease-in-out;
		box-shadow: ${shadow.notebook};
		z-index: ${zIndex.highest};
		
		${
      !isCollapsed &&
      css`
				border-radius: ${borderRadius.card};
				background-color: ${colorTokens.background.white};
				box-shadow: ${shadow.dropList};
			`
    }

		${
      isFloating &&
      css`
				bottom: auto;
			`
    }
	`,
  header: ({
    isCollapsed,
  }: {
    isCollapsed: boolean;
  }) => css`
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: ${spacing[12]} ${spacing[16]};
		${typography.body('medium')};
		color: ${colorTokens.text.title};

		${
      !isCollapsed &&
      css`
				border-bottom: 1px solid ${colorTokens.stroke.divider};
				padding: ${spacing[8]} ${spacing[12]};
				cursor: grab;
			`
    }
	`,
  actions: css`
		display: flex;
	`,
  collapseButton: ({
    isCollapsed,
  }: {
    isCollapsed: boolean;
  }) => css`
		transition: all 0.3s ease-in-out;
	
		${
      !isCollapsed &&
      css`
				transform: rotate(180deg);
			`
    }
	`,
  notebookWrapper: css`
	  padding-block: ${spacing[16]};
		width: 100%;
		height: calc(100% - ${notebook.NOTEBOOK_HEADER}px);
		background: url('data:image/svg+xml,<svg width="9" height="9" viewBox="0 0 9 9" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="%23D9D9D9"/></svg>') repeat;
	`,
  notebook: css`
		padding-inline: ${spacing[16]};
		outline: none;
		word-wrap: break-word;
		overflow-y: auto;
		height: 100%;
    white-space: pre-wrap;
	`,
  textFieldExpand: css`
		position: absolute;
		bottom: ${spacing[4]};
		right: ${spacing[4]};
		user-select: none;
		color: ${colorTokens.icon.hints};
		cursor: nwse-resize;
	`,
};
