import { isDefined } from '@Utils/types';
import { useEffect, useRef, useState } from 'react';

interface Options {
	defaultValue?: boolean;
}

const defaultOptions = {
	defaultValue: false,
};

export const useIsScrolling = <TRef extends HTMLElement = HTMLDivElement>(options?: Options) => {
	const ref = useRef<TRef>(null);
	const mergedOptions = { ...defaultOptions, ...options };
	const [isScrolling, setIsScrolling] = useState(mergedOptions.defaultValue);

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (!isDefined(ref.current)) {
			return;
		}

		if (ref.current.scrollHeight <= ref.current.clientHeight) {
			setIsScrolling(false);
			return;
		}

		// biome-ignore lint/suspicious/noExplicitAny: <explanation>
		const handleScroll = (event: any) => {
			const element = event.target;

			if (element.scrollTop + element.clientHeight >= element.scrollHeight) {
				setIsScrolling(false);
				return;
			}

			setIsScrolling(element.scrollTop >= 0);
		};

		ref.current.addEventListener('scroll', handleScroll);

		return () => {
			ref.current?.removeEventListener('scroll', handleScroll);
		};
	}, [ref.current]);

	return { ref, isScrolling };
};
