// Minimal WebAuthn helper utilities used by the front-end code.
// Provides base64url <-> ArrayBuffer conversions.

export function base64urlToArrayBuffer(base64url: string): ArrayBuffer {
    // Replace URL-safe chars
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    // Pad with '='
    const pad = base64.length % 4;
    const padded =
        base64 + (pad === 2 ? '==' : pad === 3 ? '=' : pad === 1 ? '===' : '');
    const binary = atob(padded);
    const len = binary.length;
    const buffer = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        buffer[i] = binary.charCodeAt(i);
    }
    return buffer.buffer;
}

export function arrayBufferToBase64url(
    buffer: ArrayBuffer | Uint8Array,
): string {
    const bytes =
        buffer instanceof Uint8Array ? buffer : new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    const base64 = btoa(binary);
    // Convert to base64url
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
}

export default { base64urlToArrayBuffer, arrayBufferToBase64url };
