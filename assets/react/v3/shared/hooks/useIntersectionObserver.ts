import { isDefined } from '@Utils/types';
import { useEffect, useRef, useState } from 'react';

type Args = IntersectionObserverInit & {
	freezeOnceVisible?: boolean;
};

function useIntersectionObserver<T extends HTMLElement>({
	threshold = 0,
	root = null,
	rootMargin = '0%',
	freezeOnceVisible = false,
}: Args = {}) {
	const intersectionElementRef = useRef<T>(null);
	const [intersectionEntry, setIntersectionEntry] = useState<IntersectionObserverEntry>();

	const frozen = intersectionEntry?.isIntersecting && freezeOnceVisible;

	const updateEntry = ([entry]: IntersectionObserverEntry[]): void => {
		setIntersectionEntry(entry);
	};

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		const node = intersectionElementRef.current;
		const hasIOSupport = isDefined(window.IntersectionObserver);

		if (!hasIOSupport || frozen || !node) return;

		const observerParams = { threshold, root, rootMargin };
		const observer = new IntersectionObserver(updateEntry, observerParams);

		observer.observe(node);

		return () => observer.disconnect();
	}, [intersectionElementRef.current, threshold, root, rootMargin, frozen]);

	return { intersectionEntry, intersectionElementRef };
}

export default useIntersectionObserver;
