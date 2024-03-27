import { useCallback, useEffect, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { isDefined } from '@Utils/types';
import { throttle } from '@Utils/util';
import { styleUtils } from '@Utils/style-utils';
import Show from '@Controls/Show';

import { useLocalStorage } from '@Hooks/useLocalStorage';
import { type TResizeType, useResize } from '@Hooks/useResize';

interface Position {
	x: number;
	y: number;
}

const MIN_NOTEBOOK_HEIGHT = 430;
const MIN_NOTEBOOK_WIDTH = 360;
const NOTEBOOK_HEADER = 50;

const Notebook = () => {
	const [isCollapsed, setIsCollapsed] = useState<boolean>(true);
	const [isDragging, setIsDragging] = useState<boolean>(false);
	const [isFloating, setIsFloating] = useState<boolean>(false);
	const [offset, setOffset] = useState<Position>({ x: 0, y: 0 });
	const [contentEditable, setContentEditable] = useState<boolean>(false);
	const [content, setContent] = useState<string>('');

	const wrapperRef = useRef<HTMLDivElement>(null);

	const expandAnimation = useSpring({
		height: !isCollapsed ? MIN_NOTEBOOK_HEIGHT : NOTEBOOK_HEADER,
		opacity: 1,
		config: {
			duration: 300,
			easing: (t) => t * (2 - t),
		},
	});

	const [notebookData, setNotebookData] = useLocalStorage<{
		left: string;
		top: string;
		height?: string;
		width?: string;
		notes?: string;
	}>('notebook', {
		left: `${window.innerWidth - MIN_NOTEBOOK_WIDTH}px`,
		top: `${window.innerHeight - MIN_NOTEBOOK_HEIGHT}px`,
		width: `${MIN_NOTEBOOK_WIDTH}px`,
		height: `${MIN_NOTEBOOK_HEIGHT}px`,
	});

	const { handleResize, isResizing, size } = useResize({
		resizeDivRef: wrapperRef,
		options: {
			minHeight: MIN_NOTEBOOK_HEIGHT,
			minWidth: MIN_NOTEBOOK_WIDTH,
		},
	});

	const onContentBlur = (event: React.FocusEvent) => {
		setContent(event.currentTarget.innerHTML);
		setContentEditable(false);
		setNotebookData({
			left: notebookData?.left || `${window.innerWidth - MIN_NOTEBOOK_WIDTH}px`,
			top: notebookData?.top || `${window.innerHeight - MIN_NOTEBOOK_HEIGHT}px`,
			height: notebookData?.height,
			width: notebookData?.width,
			notes: event.currentTarget.innerHTML,
		});
	};

	const handleMouseDown = (event: React.MouseEvent) => {
		event.preventDefault();
		event.stopPropagation();

		if (isCollapsed) return;

		setIsDragging(true);
		const { clientX, clientY } = event;
		const wrapper = wrapperRef.current;

		if (wrapper) {
			const rect = wrapper.getBoundingClientRect();
			setOffset({ x: clientX - rect.left, y: clientY - rect.top });
		}
	};

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		const handleMouseMove = throttle((event: MouseEvent) => {
			if (!isDragging || !isDefined(wrapperRef.current)) return;

			setIsFloating(true);

			const warpper = wrapperRef.current;
			const { offsetWidth: notebookWidth, offsetHeight: notebookHeight } = warpper;
			const { innerWidth: windowWidth, innerHeight: windowHeight } = window;

			const newX = Math.min(Math.max(event.clientX - offset.x, 0), windowWidth - notebookWidth);
			const newY = Math.min(Math.max(event.clientY - offset.y, 0), windowHeight - notebookHeight);

			warpper.style.left = `${newX}px`;
			warpper.style.top = `${newY}px`;

			setNotebookData({
				left: warpper.style.left,
				top: warpper.style.top,
				height: notebookData?.height,
				width: notebookData?.width,
			});
		}, 10);

		const handleMouseUp = () => {
			setIsDragging(false);
		};

		document.addEventListener('mousemove', handleMouseMove);
		document.addEventListener('mouseup', handleMouseUp);

		return () => {
			document.removeEventListener('mousemove', handleMouseMove);
			document.removeEventListener('mouseup', handleMouseUp);
		};
	}, [isDragging, offset]);

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (isResizing && isDefined(wrapperRef.current)) {
			const wrapper = wrapperRef.current;
			const { left, top, height, width } = wrapper.style;

			setNotebookData({ left, top, height, width });
		}
	}, [isResizing, size]);

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (isCollapsed && isDefined(wrapperRef.current) && !isFloating) {
			setIsFloating(false);
			const wrapper = wrapperRef.current;

			wrapper.style.left = 'auto';
			wrapper.style.top = 'auto';
			wrapper.style.width = '360px';
		}

		if (!isCollapsed && isFloating && isDefined(wrapperRef.current)) {
			const { left, top, height, width } = notebookData || {};

			if (left && top) {
				const wrapper = wrapperRef.current;

				wrapper.style.left = left === 'auto' ? `${window.innerWidth - MIN_NOTEBOOK_WIDTH}px` : left;
				wrapper.style.top = top === 'auto' ? `${window.innerHeight - MIN_NOTEBOOK_HEIGHT}px` : top;
				if (isDefined(height)) {
					wrapper.style.height = height;
				}
				if (isDefined(width)) {
					wrapper.style.width = width;
				}
			}
		}
	}, [isCollapsed, isFloating]);

	useEffect(() => {
		if (notebookData?.notes) {
			setContent(notebookData.notes);
		}
	}, [notebookData]);

	return (
		<animated.div ref={wrapperRef} css={styles.wrapper({ isCollapsed, isFloating })} style={{ ...expandAnimation }}>
			<div css={styles.header({ isCollapsed })} onMouseDown={handleMouseDown}>
				<span css={styleUtils.text.ellipsis(1)}>Notebook</span>
				<div css={styles.actions}>
					<Button
						variant="text"
						size="small"
						onClick={() => {
							setIsCollapsed(!isCollapsed);
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
								if (previouState) {
									return false;
								}

								return previouState;
							});
							setIsFloating(!isFloating);
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
			<div
				css={styles.notebook}
				contentEditable={contentEditable}
				onBlur={(event) => onContentBlur(event)}
				onClick={() => setContentEditable(true)}
				onKeyDown={(event) => {
					if (event.key === 'Escape') {
						event.preventDefault();
						event.currentTarget.blur();
					}
				}}
				dangerouslySetInnerHTML={{ __html: content }}
			/>

			<Show when={isFloating && !isCollapsed}>
				<SVGIcon name="textFieldExpand" height={16} width={16} style={styles.textFieldExpand} />
			</Show>
			<Show when={isFloating}>
				<div css={styles.resizeHandle({ direction: 'left' })} onMouseDown={(event) => handleResize(event, 'left')} />
				<div css={styles.resizeHandle({ direction: 'right' })} onMouseDown={(event) => handleResize(event, 'right')} />
				<div
					css={styles.resizeHandle({ direction: 'bottom' })}
					onMouseDown={(event) => handleResize(event, 'bottom')}
				/>
				<div css={styles.resizeHandle({ direction: 'top' })} onMouseDown={(event) => handleResize(event, 'top')} />
				<div
					css={styles.resizeHandle({ direction: 'bottom-right' })}
					onMouseDown={(event) => handleResize(event, 'bottom-right')}
				/>
				<div
					css={styles.resizeHandle({ direction: 'bottom-left' })}
					onMouseDown={(event) => handleResize(event, 'bottom-left')}
				/>
				<div
					css={styles.resizeHandle({ direction: 'top-right' })}
					onMouseDown={(event) => handleResize(event, 'top-right')}
				/>
				<div
					css={styles.resizeHandle({ direction: 'top-left' })}
					onMouseDown={(event) => handleResize(event, 'top-left')}
				/>
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
		box-shadow: 0 0 4px 0 rgba(0, 30, 43, 0.16);
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
	notebook: css`
		padding: ${spacing[16]};
		width: 100%;
		height: calc(100% - 50px);
		background: url('data:image/svg+xml,<svg width="9" height="9" viewBox="0 0 9 9" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="%23D9D9D9"/></svg>') repeat;
		outline: none;
	`,
	textFieldExpand: css`
		position: absolute;
		bottom: 10px;
		right: 10px;
		user-select: none;
		color: ${colorTokens.icon.hints};
	`,
	resizeHandle: ({
		direction,
	}: {
		direction: TResizeType;
	}) => css`
		position: absolute;
		${
			direction === 'top' &&
			css`
				height: 2px;
				width: 100%;
				top: 0;
				left: 0;
				cursor: ns-resize;
			`
		}

		${
			direction === 'bottom' &&
			css`
				height: 2px;
				width: 100%;
				bottom: 0;
				left: 0;
				cursor: ns-resize;
			`
		}

		${
			direction === 'left' &&
			css`
				height: 100%;
				width: 2px;
				top: 0;
				left: 0;
				cursor: ew-resize;
			`
		}

		${
			direction === 'right' &&
			css`
				height: 100%;
				width: 2px;
				top: 0;
				right: 0;
				cursor: ew-resize;
			`
		}

		${
			direction === 'bottom-right' &&
			css`
				height: 6px;
				width: 6px;
				bottom: 0;
				right: 0;
				cursor: nwse-resize;
			`
		}

		${
			direction === 'bottom-left' &&
			css`
				height: 6px;
				width: 6px;
				bottom: 0;
				left: 0;
				cursor: nesw-resize;
			`
		}

		${
			direction === 'top-right' &&
			css`
				height: 6px;
				width: 6px;
				top: 0;
				right: 0;
				cursor: nesw-resize;
			`
		}

		${
			direction === 'top-left' &&
			css`
				height: 6px;
				width: 6px;
				top: 0;
				left: 0;
				cursor: nwse-resize;
			`
		}
	`,
};
