import { ref, watch, type Ref } from 'vue';

export interface ValidationError {
    type: 'render' | 'parse' | 'unknown' | 'fatal';
    message: string;
    line: number | null;
    column: number | null;
}

export interface ValidationResult {
    valid: boolean;
    errors: ValidationError[];
}

export function useShortcodeValidation(content: Ref<string>, debounceMs = 500) {
    const validating = ref(false);
    const validationErrors = ref<ValidationError[]>([]);
    const isValid = ref(true);
    let debounceTimeout: NodeJS.Timeout | null = null;

    const validateContent = async (markdown: string): Promise<ValidationResult> => {
        if (!markdown || markdown.trim() === '') {
            return { valid: true, errors: [] };
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const response = await fetch('/admin/pages/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    markdown_content: markdown,
                }),
            });

            // Try to read the response body in a resilient way.
            // Prefer parsing as JSON when the server declares JSON.
            const contentType = response.headers.get('content-type') || '';

            let text: string | null = null;

            if (contentType.includes('application/json')) {
                try {
                    // If the body was already consumed, this will throw and be handled below.
                    const json = await response.json();
                    return json as ValidationResult;
                } catch (jsonErr) {
                    console.warn('Failed to parse response as JSON directly, falling back to text():', jsonErr);
                    // fallthrough to text-based parsing
                }
            }

            // Attempt to read text via a clone first to avoid interfering with other consumers.
            try {
                text = await response.clone().text();
                // eslint-disable-next-line @typescript-eslint/no-unused-vars
            } catch (cloneErr) {
                // If clone failed (possibly because body already used) try reading from the original.
                try {
                    text = await response.text();
                } catch (textErr) {
                    console.error('Validation endpoint response body could not be read:', textErr);
                    return {
                        valid: false,
                        errors: [{
                            type: 'fatal',
                            message: `Validation endpoint error (${response.status}). Response body unavailable.`,
                            line: null,
                            column: null,
                        }],
                    };
                }
            }

            // Try to parse the text as JSON result
            try {
                const result = JSON.parse(text);
                return result as ValidationResult;
                // eslint-disable-next-line @typescript-eslint/no-unused-vars
            } catch (parseError) {
                // Response wasn't valid JSON
                console.error('Validation endpoint returned non-JSON response:', text);
                return {
                    valid: false,
                    errors: [{
                        type: 'fatal',
                        message: `Validation endpoint error (${response.status}). Check console for details.`,
                        line: null,
                        column: null,
                    }],
                };
            }
        } catch (error) {
            console.error('Validation error:', error);
            return {
                valid: false,
                errors: [{
                    type: 'fatal',
                    message: `Failed to validate content: ${error instanceof Error ? error.message : 'Unknown error'}`,
                    line: null,
                    column: null,
                }],
            };
        }
    };

    const validate = async () => {
        validating.value = true;

        const result = await validateContent(content.value);

        validationErrors.value = result.errors;
        isValid.value = result.valid;
        validating.value = false;
    };

    const debouncedValidate = () => {
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        debounceTimeout = setTimeout(async () => {
            await validate();
        }, debounceMs);
    };

    // Watch for content changes and validate
    watch(content, () => {
        debouncedValidate();
    }, { immediate: true });

    return {
        validating,
        validationErrors,
        isValid,
        validate,
    };
}
