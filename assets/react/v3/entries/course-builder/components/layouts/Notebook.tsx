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

import { type TResizeType, useResize } from '@Hooks/useResize';
import { getFromLocalStorage, setToLocalStorage } from '@Utils/localstorage';
import { LocalStorageKeys, notebook } from '@Config/constants';

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
	notes: string;
}

const Notebook = () => {
	const [isCollapsed, setIsCollapsed] = useState<boolean>(true);
	const [isDragging, setIsDragging] = useState<boolean>(false);
	const [isFloating, setIsFloating] = useState<boolean>(false);
	const [offset, setOffset] = useState<Position>({ x: 0, y: 0 });
	const [contentEditable, setContentEditable] = useState<boolean>(false);
	const [content, setContent] = useState<string>('');

	const wrapperRef = useRef<HTMLDivElement>(null);

	const expandAnimation = useSpring({
		height: !isCollapsed ? notebook.MIN_NOTEBOOK_HEIGHT : notebook.NOTEBOOK_HEADER,
		config: {
			duration: 300,
			easing: (t) => t * (2 - t),
		},
	});

	const { handleResize, isResizing, size } = useResize({
		resizeDivRef: wrapperRef,
		options: {
			minHeight: notebook.MIN_NOTEBOOK_HEIGHT,
			minWidth: notebook.MIN_NOTEBOOK_WIDTH,
		},
	});

	const onContentBlur = (event: React.FocusEvent) => {
		setContentEditable(false);
		const notebookData: NotebookData = JSON.parse(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
		const tempDiv = document.createElement('div');
		tempDiv.innerHTML = event.currentTarget.innerHTML;
		const text = tempDiv.innerText || '';
		setToLocalStorage(
			LocalStorageKeys.notebook,
			JSON.stringify({
				...notebookData,
				notes: text,
			})
		);
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
		}, 10);

		const handleMouseUp = () => {
			setIsDragging(false);

			if (isDefined(wrapperRef.current)) {
				const wrapper = wrapperRef.current;
				const { left, top, height, width } = wrapper.style;
				const currentNotebookData: NotebookData = JSON.parse(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
				if (left === 'auto' || top === 'auto') return;
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
			}
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
			const notebookData: NotebookData = JSON.parse(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
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
	}, [isResizing, size]);

	useEffect(() => {
		if (isCollapsed && isDefined(wrapperRef.current) && !isFloating) {
			setIsFloating(false);
			const wrapper = wrapperRef.current;

			wrapper.style.left = 'auto';
			wrapper.style.top = 'auto';
			wrapper.style.width = '360px';
		}

		if (!isCollapsed && isFloating && isDefined(wrapperRef.current)) {
			const notebookData: NotebookData = JSON.parse(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
			const { left, top, height, width } = notebookData.position || {
				left: 'auto',
				top: 'auto',
			};

			if (left && top) {
				const wrapper = wrapperRef.current;

				wrapper.style.left = left === 'auto' ? `${window.innerWidth - notebook.MIN_NOTEBOOK_WIDTH}px` : left;
				wrapper.style.top = top === 'auto' ? `${window.innerHeight - notebook.MIN_NOTEBOOK_HEIGHT}px` : top;
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
		const notebookData: NotebookData = JSON.parse(getFromLocalStorage(LocalStorageKeys.notebook) || '{}');
		if (notebookData.notes) {
			setContent(notebookData.notes);
		}
	}, []);

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
				<button
					type="button"
					css={[styleUtils.resetButton, styles.textFieldExpand]}
					onMouseDown={(event) => handleResize(event, 'bottom-right')}
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
		word-wrap: break-word;
	`,
	textFieldExpand: css`
		position: absolute;
		bottom: 10px;
		right: 10px;
		user-select: none;
		color: ${colorTokens.icon.hints};
		cursor: nwse-resize;
	`,
};
