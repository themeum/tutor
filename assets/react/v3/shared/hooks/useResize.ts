import { useEffect, useState } from 'react';

import { isDefined } from '@Utils/types';
import { throttle } from '@Utils/util';

interface IResizeProps {
	resizeDivRef?: React.RefObject<HTMLDivElement>;
	options?: {
		minWidth?: number;
		minHeight?: number;
		maxWidth?: number;
		maxHeight?: number;
	};
}

interface IPosition {
	x: number;
	y: number;
}

interface ISize {
	width: number;
	height: number;
}

export type TResizeType =
	| 'top'
	| 'bottom'
	| 'left'
	| 'right'
	| 'top-left'
	| 'top-right'
	| 'bottom-left'
	| 'bottom-right';

export const useResize = ({ resizeDivRef, options }: IResizeProps) => {
	const [isResizing, setIsResizing] = useState<boolean>(false);
	const [resizeType, setResizeType] = useState<TResizeType | null>(null);
	const [position, setPosition] = useState<IPosition>({
		x: 0,
		y: 0,
	});
	const [size, setSize] = useState<ISize>({ width: 0, height: 0 });

	const handleResize = (e: React.MouseEvent, type: TResizeType) => {
		e.preventDefault();
		e.stopPropagation();

		setIsResizing(true);

		if (!isDefined(resizeDivRef?.current)) return;

		const resizeDivRect = resizeDivRef.current?.getBoundingClientRect();
		const maxSize = {
			width: options?.maxWidth || window.innerWidth,
			height: options?.maxHeight || window.innerHeight,
		};
		const minSize = {
			width: options?.minWidth || 0,
			height: options?.minHeight || 0,
		};

		const getBoundedSize = (width: number, height: number) => {
			const boundedWidth = Math.min(Math.max(width, minSize.width), maxSize.width);
			const boundedHeight = Math.min(Math.max(height, minSize.height), maxSize.height);

			return {
				width: boundedWidth,
				height: boundedHeight,
			};
		};

		const currentPositon: IPosition = {
			x: resizeDivRect.x || 0,
			y: resizeDivRect.y || 0,
		};

		const startSize: ISize = {
			width: resizeDivRect.width || 0,
			height: resizeDivRect.height || 0,
		};
		const startPosition = {
			x: e.pageX,
			y: e.pageY,
		};

		const handleMouseMove = throttle((e: MouseEvent) => {
			const deltaX = e.pageX - startPosition.x;
			const deltaY = e.pageY - startPosition.y;

			setResizeType(type);

			switch (type) {
				case 'top':
					if (minSize.height > startSize.height - deltaY) {
						setPosition((prev) => ({
							x: prev.x,
							y: resizeDivRect.bottom - minSize.height,
						}));
						setSize(getBoundedSize(startSize.width, minSize.height));
						return;
					}
					if (e.pageY < resizeDivRect.top - maxSize.height) {
						setPosition((prev) => ({
							x: prev.x,
							y: resizeDivRect.bottom - maxSize.height,
						}));
					}
					setPosition({
						x: currentPositon.x,
						y: currentPositon.y + deltaY,
					});
					setSize(getBoundedSize(startSize.width, startSize.height - deltaY));
					break;

				case 'bottom':
					if (minSize.height > startSize.height + deltaY) {
						setSize(getBoundedSize(startSize.width, minSize.height));
					}

					setSize(getBoundedSize(startSize.width, startSize.height + deltaY));
					break;

				case 'left':
					if (e.pageX > resizeDivRect.right - minSize.width) {
						setPosition((prev) => ({
							x: resizeDivRect.right - minSize.width,
							y: prev.y,
						}));
						setSize(getBoundedSize(minSize.width, startSize.height));
						return;
					}

					if (minSize.width > startSize.width - deltaX) {
						return;
					}
					setPosition({
						x: currentPositon.x + deltaX,
						y: currentPositon.y,
					});
					setSize(getBoundedSize(startSize.width - deltaX, startSize.height));
					return;

				case 'right':
					setSize(getBoundedSize(startSize.width + deltaX, startSize.height));
					return;

				case 'top-left':
					if (e.pageX > resizeDivRect.right - minSize.width) {
						setPosition((prev) => ({
							x: resizeDivRect.right - minSize.width,
							y: prev.y,
						}));
						setSize((prev) => getBoundedSize(minSize.width, prev.height));
					} else {
						setPosition({
							x: currentPositon.x + deltaX,
							y: currentPositon.y + deltaY,
						});
						setSize(getBoundedSize(startSize.width - deltaX, startSize.height - deltaY));
					}
					if (minSize.height > startSize.height - deltaY) {
						setPosition((prev) => ({
							x: prev.x,
							y: resizeDivRect.bottom - minSize.height,
						}));
						setSize((prev) => getBoundedSize(prev.width, minSize.height));
					} else {
						setPosition({
							x: Math.min(currentPositon.x + deltaX, resizeDivRect.right - minSize.width),
							y: currentPositon.y + deltaY,
						});
						setSize(getBoundedSize(startSize.width - deltaX, startSize.height - deltaY));
					}
					return;

				case 'top-right':
					if (minSize.height > startSize.height - deltaY) {
						setPosition((prev) => ({
							x: prev.x,
							y: resizeDivRect.bottom - minSize.height,
						}));
						setSize(getBoundedSize(startSize.width + deltaX, minSize.height));
					} else {
						setPosition({
							x: currentPositon.x,
							y: currentPositon.y + deltaY,
						});
						setSize(getBoundedSize(startSize.width + deltaX, startSize.height - deltaY));
					}
					if (e.pageY < resizeDivRect.top - maxSize.height) {
						setPosition((prev) => ({
							x: prev.x,
							y: resizeDivRect.bottom - maxSize.height,
						}));
					}
					return;

				case 'bottom-left':
					if (minSize.height > startSize.height + deltaY) {
						setSize(getBoundedSize(startSize.width, minSize.height));
					}
					if (e.pageX > resizeDivRect.right - minSize.width) {
						setPosition((prev) => ({
							x: resizeDivRect.right - minSize.width,
							y: prev.y,
						}));
						setSize(getBoundedSize(minSize.width, startSize.height + deltaY));
						return;
					}

					if (minSize.width > startSize.width - deltaX) {
						return;
					}
					setPosition({
						x: currentPositon.x + deltaX,
						y: currentPositon.y,
					});
					setSize(getBoundedSize(startSize.width - deltaX, startSize.height + deltaY));
					return;

				case 'bottom-right':
					setSize(getBoundedSize(startSize.width + deltaX, startSize.height + deltaY));
					return;
			}
		}, 10);

		const handleMouseUp = () => {
			setIsResizing(false);

			document.body.removeEventListener('mousemove', handleMouseMove);
			document.body.removeEventListener('mouseup', handleMouseUp);
			setResizeType(null);
		};

		document.body.addEventListener('mousemove', handleMouseMove);
		document.body.addEventListener('mouseup', handleMouseUp);
	};

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (isDefined(resizeDivRef?.current) && isResizing) {
			resizeDivRef.current.style.left = `${position.x}px`;
			resizeDivRef.current.style.top = `${position.y}px`;
		}
	}, [position]);

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (isDefined(resizeDivRef?.current) && isResizing) {
			resizeDivRef.current.style.width = `${size.width}px`;
			resizeDivRef.current.style.height = `${size.height}px`;
		}
	}, [size]);

	useEffect(() => {
		if (isDefined(resizeDivRef?.current)) {
			setSize({
				width: resizeDivRef.current.offsetWidth,
				height: resizeDivRef.current.offsetHeight,
			});
			setPosition({
				x: resizeDivRef.current.offsetLeft,
				y: resizeDivRef.current.offsetTop,
			});
		}
	}, [resizeDivRef]);

	return {
		isResizing,
		resizeType,
		handleResize,
		size,
		position,
	};
};
