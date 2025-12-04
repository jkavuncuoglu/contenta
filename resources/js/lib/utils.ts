import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}


export function ucWords(value: unknown, allWords = false): string {
    if (value === null || value === undefined) return '';

    const s = String(value)
        .replace(/[-_]+/g, ' ')
        .trim()
        .replace(/\s+/g, ' ');

    if (s.length === 0) return '';

    const words = s.split(' ');

    if (allWords) {
        return words
            .map((w) => (w.length > 0 ? w.charAt(0).toUpperCase() + w.slice(1) : w))
            .join(' ');
    }

    const first = words.shift() as string;
    return (first.charAt(0).toUpperCase() + first.slice(1)) + (words.length ? ' ' + words.join(' ') : '');
}

