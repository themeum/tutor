import { useEffect, useState } from 'react';

function parseValue<T>(value: string | null): T | null {
	try {
		return value === 'undefined' || value === 'null' ? null : JSON.parse(value ?? '');
	} catch {
		return null;
	}
}

function stringifyValue<T>(value: T | null | undefined): string | null {
	return value === undefined || value === null ? null : JSON.stringify(value);
}

export function useLocalStorage<T>(key: string, initialValue: T | (() => T)) {
	const getSavedValue = (): T | null => {
		const savedValue = localStorage.getItem(key);
		return parseValue(savedValue);
	};

	const [storedValue, setStoredValue] = useState<T | null>(
		getSavedValue() ??
			parseValue(stringifyValue(typeof initialValue === 'function' ? (initialValue as () => T)() : initialValue))
	);

	useEffect(() => {
		const stringifiedValue = stringifyValue(storedValue);
		if (stringifiedValue === null) {
			localStorage.removeItem(key);
		} else {
			localStorage.setItem(key, stringifiedValue);
		}
		localStorage.setItem(key, JSON.stringify(storedValue));
	}, [storedValue, key]);

	const setValue = (value: T | null) => {
		setStoredValue(value);
	};

	return [storedValue, setValue] as const;
}
