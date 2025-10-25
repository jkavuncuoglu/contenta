export function getInitials(firstName?: string, lastName?: string): string {
    if (!firstName || !lastName) return '';

    return `${firstName[0].charAt(0)}${lastName.charAt(0)}`.toUpperCase();
}

export function useInitials() {
    return { getInitials };
}
