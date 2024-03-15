import { headerHeight } from '@Config/styles';
import { useEffect, useRef, useState } from 'react';

export const useSticky = (stickyGap = 0) => {
	const stickyRef = useRef<HTMLDivElement>(null);
	const [isSticky, setIsSticky] = useState(false);

	useEffect(() => {
		const handleScroll = () => {
			if (!stickyRef.current) {
				return;
			}

			if (window.scrollY > stickyRef.current.offsetTop - headerHeight - stickyGap) {
				setIsSticky(true);
			} else {
				setIsSticky(false);
			}
		};

		window.addEventListener('scroll', handleScroll);

		return () => {
			window.removeEventListener('scroll', handleScroll);
		};
	}, [stickyGap]);

	return { stickyRef, isSticky };
};
