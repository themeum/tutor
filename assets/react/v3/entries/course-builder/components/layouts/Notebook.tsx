import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { LocalStorageKeys, notebook } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { getFromLocalStorage, setToLocalStorage } from '@Utils/localStorage';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { jsonParse, throttle } from '@Utils/util';

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
  const [content, setContent] = useState('');

  const wrapperRef = useRef<HTMLDivElement>(null);
  const notebookRef = useRef<HTMLDivElement>(null);

  const expandAnimation = useSpring({
    width: !isCollapsed ? notebook.MIN_NOTEBOOK_WIDTH : notebook.NOTEBOOK_HEADER,
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

  const onContentBlur = (event: React.FocusEvent<HTMLDivElement, Element>) => {
    event.currentTarget.contentEditable = 'false';

    const notebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) ?? '{}');

    setToLocalStorage(
      LocalStorageKeys.notebook,
      JSON.stringify({
        ...notebookData,
        content: event.currentTarget.innerHTML,
      }),
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

    if (isCollapsed || !isFloating) {
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
        getFromLocalStorage(LocalStorageKeys.notebook) || '{}',
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
        }),
      );
    }
  };

  useEffect(() => {
    const handleMouseMove = throttle((event: MouseEvent) => {
      if (!isDragging || !isDefined(wrapperRef.current)) {
        return;
      }
      setIsFloating(true);

      const wrapper = wrapperRef.current;
      const { offsetWidth: notebookWidth, offsetHeight: notebookHeight } = wrapper;
      const { innerWidth: windowWidth, innerHeight: windowHeight } = window;

      const newX = Math.min(Math.max(event.clientX - offset.x, 0), windowWidth - notebookWidth);
      const newY = Math.min(Math.max(event.clientY - offset.y, 0), windowHeight - notebookHeight);

      wrapper.style.left = `${newX}px`;
      wrapper.style.top = `${newY}px`;
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
        }),
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

    const wrapper = wrapperRef.current;

    if (!isFloating) {
      wrapper.style.left = 'auto';
      wrapper.style.top = 'auto';
      wrapper.style.height = `${notebook.MIN_NOTEBOOK_HEIGHT}px`;

      return;
    }

    if (!isCollapsed && isFloating) {
      const notebookData = jsonParse<NotebookData>(getFromLocalStorage(LocalStorageKeys.notebook) ?? '{}');
      const { left, top, height, width } = notebookData.position || {
        left: 'auto',
        top: 'auto',
      };

      wrapper.style.left = left === 'auto' ? `${window.innerWidth / 2 - notebook.MIN_NOTEBOOK_WIDTH}px` : left;
      wrapper.style.top = top === 'auto' ? `${window.innerHeight / 2 - notebook.MIN_NOTEBOOK_HEIGHT}px` : top;
      wrapper.style.height = isDefined(height) ? height : `${2 * notebook.MIN_NOTEBOOK_HEIGHT}px`;
      wrapper.style.width = isDefined(width) ? width : `${2 * notebook.MIN_NOTEBOOK_WIDTH}px`;

      return;
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
      <Show
        when={!isCollapsed}
        fallback={
          <div css={styles.verticalTitleWrapper}>
            <button
              type="button"
              css={[styleUtils.resetButton, styles.verticalButton]}
              onClick={() => setIsCollapsed(false)}
            >
              {__('Notebook', 'tutor')}
            </button>
          </div>
        }
      >
        <div css={styles.header({ isCollapsed, isFloating })} onMouseDown={handleMouseDown}>
          <span css={styleUtils.text.ellipsis(1)}>{__('Notebook', 'tutor')}</span>

          <div css={styles.actions}>
            <Show when={!isFloating}>
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
            </Show>
            <Button variant="text" size="small" onClick={() => setIsFloating((previous) => !previous)}>
              <SVGIcon name={isFloating ? 'arrowsIn' : 'arrowsOut'} height={24} width={24} />
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
      </Show>
      <div css={styles.notebookWrapper}>
        <div
          ref={notebookRef}
          css={styles.notebook({
            isCollapsed,
          })}
          onBlur={(event) => onContentBlur(event)}
          onPaste={(event) => onContentPaste(event)}
          onClick={(event) => {
            event.stopPropagation();
            event.currentTarget.contentEditable = 'true';
            event.currentTarget.focus();
          }}
          onKeyDown={(event) => {
            event.stopPropagation();
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
          css={styles.textFieldExpand}
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
		height: ${notebook.MIN_NOTEBOOK_HEIGHT}px;
		border-radius: ${borderRadius.card} 0 ${borderRadius.card} 0;
		transition: box-shadow background 0.3s ease-in-out;
		box-shadow: ${shadow.notebook};
		z-index: ${zIndex.notebook};
    overflow: hidden;
		
		${
      !isCollapsed &&
      css`
				border-top-left-radius: ${borderRadius.card};
				background-color: ${colorTokens.background.white};
				box-shadow: ${shadow.dropList};
			`
    }

		${
      isFloating &&
      css`
				bottom: auto;
        border-radius: ${borderRadius.card};
			`
    }
	`,
  verticalTitleWrapper: css`
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-90deg);
    background-color: ${colorTokens.background.active};
    color: ${colorTokens.text.title};
  `,
  verticalButton: css`
    text-align: center;
    width: ${notebook.MIN_NOTEBOOK_HEIGHT}px;
    height: ${notebook.NOTEBOOK_HEADER}px;
    ${typography.body('bold')};
  `,
  header: ({
    isCollapsed,
    isFloating,
  }: {
    isCollapsed: boolean;
    isFloating: boolean;
  }) => css`
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: ${spacing[12]} ${spacing[16]};
		${typography.body('medium')};
    color: transparent;

    ${
      isFloating &&
      css`
        cursor: grab;
        color: ${colorTokens.text.title};
      `
    }

		${
      !isCollapsed &&
      css`
				border-bottom: 1px solid ${colorTokens.stroke.divider};
				padding: ${spacing[8]} ${spacing[12]};
        color: ${colorTokens.text.title};
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
    transition: all 0.3s ease-in-out;
	`,
  notebook: ({
    isCollapsed,
  }: {
    isCollapsed: boolean;
  }) => css`
    ${styleUtils.overflowYAuto};
		padding-inline: ${spacing[16]};
		outline: none;
		word-wrap: break-word;
		height: 100%;
    white-space: pre-wrap;

    ${
      isCollapsed &&
      css`
        display: none;
      `
    }
	`,
  textFieldExpand: css`
    ${styleUtils.resetButton};
		position: absolute;
		bottom: ${spacing[4]};
		right: ${spacing[4]};
		user-select: none;
		color: ${colorTokens.icon.hints};
		cursor: nwse-resize;
	`,
};
