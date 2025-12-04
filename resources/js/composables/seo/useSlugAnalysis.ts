import { computed, type Ref } from 'vue';

export interface SlugAnalysis {
    slug: string;
    wordCount: number;
    length: number;
    hasKeyword: boolean;
    hasSpecialChars: boolean;
    hasUppercase: boolean;
    hasUnderscores: boolean;
    hasStopWords: boolean;
    stopWords: string[];
    isOptimal: boolean;
    score: number; // 0-100
    status: 'excellent' | 'good' | 'needs-improvement' | 'poor';
    recommendations: string[];
    optimizedSlug: string;
}

export function useSlugAnalysis(slug: Ref<string>, targetKeyword: Ref<string>) {
    /**
     * Common stop words to avoid in URLs
     */
    const stopWordsList = [
        'a',
        'an',
        'and',
        'are',
        'as',
        'at',
        'be',
        'by',
        'for',
        'from',
        'has',
        'he',
        'in',
        'is',
        'it',
        'its',
        'of',
        'on',
        'that',
        'the',
        'to',
        'was',
        'will',
        'with',
    ];

    /**
     * Get slug word count
     */
    const wordCount = computed(() => {
        if (!slug.value) return 0;
        const words = slug.value.split(/[-_]/).filter((w) => w.length > 0);
        return words.length;
    });

    /**
     * Get slug character length
     */
    const length = computed(() => {
        return slug.value.length;
    });

    /**
     * Check if slug contains target keyword
     */
    const hasKeyword = computed(() => {
        if (!targetKeyword.value.trim() || !slug.value) return false;

        const normalizedSlug = slug.value.toLowerCase().replace(/[-_]/g, ' ');
        const normalizedKeyword = targetKeyword.value.toLowerCase().trim();

        return normalizedSlug.includes(normalizedKeyword);
    });

    /**
     * Check if slug has special characters (excluding hyphens)
     */
    const hasSpecialChars = computed(() => {
        // Allow only lowercase letters, numbers, and hyphens
        return !/^[a-z0-9-]+$/.test(slug.value);
    });

    /**
     * Check if slug has uppercase letters
     */
    const hasUppercase = computed(() => {
        return /[A-Z]/.test(slug.value);
    });

    /**
     * Check if slug uses underscores instead of hyphens
     */
    const hasUnderscores = computed(() => {
        return slug.value.includes('_');
    });

    /**
     * Get stop words in slug
     */
    const stopWordsInSlug = computed(() => {
        const words = slug.value.toLowerCase().split(/[-_]/);
        return words.filter((word) => stopWordsList.includes(word));
    });

    /**
     * Check if slug has stop words
     */
    const hasStopWords = computed(() => {
        return stopWordsInSlug.value.length > 0;
    });

    /**
     * Check if slug length is optimal (3-5 words, 30-60 characters)
     */
    const isOptimalLength = computed(() => {
        return wordCount.value >= 3 && wordCount.value <= 5 && length.value >= 30 && length.value <= 60;
    });

    /**
     * Calculate slug SEO score (0-100)
     */
    const score = computed(() => {
        if (!slug.value) return 0;

        let points = 0;

        // Keyword presence (30 points)
        if (hasKeyword.value) points += 30;

        // Word count (20 points)
        if (wordCount.value >= 3 && wordCount.value <= 5) {
            points += 20;
        } else if (wordCount.value >= 2 && wordCount.value <= 6) {
            points += 15;
        } else if (wordCount.value > 0) {
            points += 10;
        }

        // Length (15 points)
        if (length.value >= 30 && length.value <= 60) {
            points += 15;
        } else if (length.value >= 20 && length.value <= 70) {
            points += 10;
        } else if (length.value > 0) {
            points += 5;
        }

        // No special characters (10 points)
        if (!hasSpecialChars.value) points += 10;

        // Lowercase only (10 points)
        if (!hasUppercase.value) points += 10;

        // Uses hyphens not underscores (10 points)
        if (!hasUnderscores.value) points += 10;

        // No stop words (5 points)
        if (!hasStopWords.value) points += 5;

        return points;
    });

    /**
     * Get slug status
     */
    const status = computed<SlugAnalysis['status']>(() => {
        if (score.value >= 85) return 'excellent';
        if (score.value >= 70) return 'good';
        if (score.value >= 50) return 'needs-improvement';
        return 'poor';
    });

    /**
     * Generate optimized slug suggestion
     */
    const optimizedSlug = computed(() => {
        if (!slug.value) return '';

        let optimized = slug.value
            .toLowerCase() // Convert to lowercase
            .replace(/_/g, '-') // Replace underscores with hyphens
            .replace(/[^a-z0-9-]/g, '-') // Remove special characters
            .replace(/--+/g, '-') // Replace multiple hyphens with single
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens

        // Remove stop words
        const words = optimized.split('-');
        const filteredWords = words.filter((word) => !stopWordsList.includes(word) || word.length === 0);

        // If we removed all words, keep the original
        if (filteredWords.length === 0) {
            return optimized;
        }

        optimized = filteredWords.join('-');

        // Trim to reasonable length if needed (max 60 chars)
        if (optimized.length > 60) {
            const trimmedWords = optimized.split('-');
            let trimmed = '';
            for (const word of trimmedWords) {
                if ((trimmed + word).length <= 60) {
                    trimmed += (trimmed ? '-' : '') + word;
                } else {
                    break;
                }
            }
            optimized = trimmed;
        }

        return optimized;
    });

    /**
     * Check if slug needs optimization
     */
    const needsOptimization = computed(() => {
        return slug.value !== optimizedSlug.value && optimizedSlug.value.length > 0;
    });

    /**
     * Get recommendations for slug improvement
     */
    const recommendations = computed<string[]>(() => {
        const tips: string[] = [];

        if (!slug.value) {
            tips.push('Add a URL slug for your content.');
            return tips;
        }

        if (!hasKeyword.value && targetKeyword.value.trim()) {
            tips.push('Include your target keyword in the URL slug for better SEO.');
        }

        if (hasUppercase.value) {
            tips.push('Use lowercase letters only in URL slugs.');
        }

        if (hasUnderscores.value) {
            tips.push('Use hyphens (-) instead of underscores (_) to separate words.');
        }

        if (hasSpecialChars.value && !hasUppercase.value && !hasUnderscores.value) {
            tips.push('Remove special characters from the URL slug.');
        }

        if (wordCount.value < 3) {
            tips.push('URL slug is too short. Use 3-5 descriptive words.');
        } else if (wordCount.value > 6) {
            tips.push('URL slug is too long. Keep it concise with 3-5 words.');
        }

        if (length.value > 60) {
            tips.push('URL slug is too long. Keep it under 60 characters.');
        } else if (length.value < 20 && wordCount.value >= 2) {
            tips.push('URL slug could be more descriptive.');
        }

        if (hasStopWords.value && stopWordsInSlug.value.length > 2) {
            tips.push(`Remove stop words (${stopWordsInSlug.value.join(', ')}) to keep the URL concise.`);
        }

        if (needsOptimization.value) {
            tips.push(`Suggested optimized slug: ${optimizedSlug.value}`);
        }

        if (tips.length === 0) {
            tips.push('Excellent URL slug! SEO-friendly and well-optimized.');
        }

        return tips;
    });

    /**
     * Get complete slug analysis
     */
    const analysis = computed<SlugAnalysis>(() => {
        return {
            slug: slug.value,
            wordCount: wordCount.value,
            length: length.value,
            hasKeyword: hasKeyword.value,
            hasSpecialChars: hasSpecialChars.value,
            hasUppercase: hasUppercase.value,
            hasUnderscores: hasUnderscores.value,
            hasStopWords: hasStopWords.value,
            stopWords: stopWordsInSlug.value,
            isOptimal: isOptimalLength.value,
            score: score.value,
            status: status.value,
            recommendations: recommendations.value,
            optimizedSlug: optimizedSlug.value,
        };
    });

    /**
     * Apply optimized slug
     */
    const applyOptimization = () => {
        if (needsOptimization.value) {
            slug.value = optimizedSlug.value;
        }
    };

    return {
        analysis,
        score,
        status,
        recommendations,
        optimizedSlug,
        needsOptimization,
        applyOptimization,
        hasKeyword,
        wordCount,
        length,
    };
}
