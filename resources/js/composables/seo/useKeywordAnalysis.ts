import { computed, type Ref } from 'vue';

export interface KeywordAnalysis {
    targetKeyword: string;
    density: number; // percentage
    occurrences: number;
    inTitle: boolean;
    inFirstParagraph: boolean;
    inHeadings: number;
    inMetaDescription: boolean;
    inSlug: boolean;
    prominenceScore: number; // 0-100
}

export interface KeywordPlacement {
    location: string;
    found: boolean;
    count?: number;
}

export function useKeywordAnalysis(
    targetKeyword: Ref<string>,
    title: Ref<string>,
    content: Ref<string>,
    slug: Ref<string>,
    metaDescription: Ref<string>,
) {
    /**
     * Extract first paragraph from markdown content
     */
    const getFirstParagraph = (markdown: string): string => {
        // Remove front matter if present
        let text = markdown.replace(/^---[\s\S]*?---/, '');

        // Remove markdown syntax
        text = text
            .replace(/^#{1,6}\s+/gm, '') // Remove headers
            .replace(/\*\*(.+?)\*\*/g, '$1') // Remove bold
            .replace(/\*(.+?)\*/g, '$1') // Remove italic
            .replace(/\[(.+?)\]\(.+?\)/g, '$1') // Remove links
            .replace(/`(.+?)`/g, '$1') // Remove inline code
            .replace(/```[\s\S]*?```/g, '') // Remove code blocks
            .trim();

        // Get first non-empty paragraph
        const paragraphs = text.split(/\n\n+/).filter((p) => p.trim().length > 0);
        return paragraphs[0] || '';
    };

    /**
     * Extract all headings from markdown content
     */
    const getHeadings = (markdown: string): string[] => {
        const headingRegex = /^#{1,6}\s+(.+)$/gm;
        const headings: string[] = [];
        let match;

        while ((match = headingRegex.exec(markdown)) !== null) {
            headings.push(match[1].trim());
        }

        return headings;
    };

    /**
     * Count keyword occurrences in text (case-insensitive)
     */
    const countKeywordOccurrences = (text: string, keyword: string): number => {
        if (!keyword.trim() || !text.trim()) return 0;

        const normalizedText = text.toLowerCase();
        const normalizedKeyword = keyword.toLowerCase().trim();

        // Create regex for whole word matching
        const regex = new RegExp(`\\b${normalizedKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'gi');
        const matches = normalizedText.match(regex);

        return matches ? matches.length : 0;
    };

    /**
     * Calculate total word count in content
     */
    const getTotalWordCount = (markdown: string): number => {
        // Remove markdown syntax and count words
        const text = markdown
            .replace(/^---[\s\S]*?---/, '') // Remove front matter
            .replace(/^#{1,6}\s+/gm, '') // Remove headers
            .replace(/\*\*(.+?)\*\*/g, '$1') // Remove bold
            .replace(/\*(.+?)\*/g, '$1') // Remove italic
            .replace(/\[(.+?)\]\(.+?\)/g, '$1') // Remove links
            .replace(/`(.+?)`/g, '$1') // Remove inline code
            .replace(/```[\s\S]*?```/g, '') // Remove code blocks
            .trim();

        const words = text.split(/\s+/).filter((word) => word.length > 0);
        return words.length;
    };

    /**
     * Check if keyword is in title
     */
    const isInTitle = computed(() => {
        return countKeywordOccurrences(title.value, targetKeyword.value) > 0;
    });

    /**
     * Check if keyword is in first paragraph
     */
    const isInFirstParagraph = computed(() => {
        const firstPara = getFirstParagraph(content.value);
        return countKeywordOccurrences(firstPara, targetKeyword.value) > 0;
    });

    /**
     * Count keyword occurrences in headings
     */
    const headingsWithKeyword = computed(() => {
        const headings = getHeadings(content.value);
        return headings.filter((heading) => countKeywordOccurrences(heading, targetKeyword.value) > 0).length;
    });

    /**
     * Check if keyword is in meta description
     */
    const isInMetaDescription = computed(() => {
        return countKeywordOccurrences(metaDescription.value, targetKeyword.value) > 0;
    });

    /**
     * Check if keyword is in slug
     */
    const isInSlug = computed(() => {
        return countKeywordOccurrences(slug.value.replace(/-/g, ' '), targetKeyword.value) > 0;
    });

    /**
     * Count total keyword occurrences in content
     */
    const totalOccurrences = computed(() => {
        return countKeywordOccurrences(content.value, targetKeyword.value);
    });

    /**
     * Calculate keyword density percentage
     */
    const keywordDensity = computed(() => {
        const totalWords = getTotalWordCount(content.value);
        if (totalWords === 0 || !targetKeyword.value.trim()) return 0;

        const keywordWords = targetKeyword.value.trim().split(/\s+/).length;
        const occurrences = totalOccurrences.value;

        // Density = (keyword occurrences × keyword word count / total words) × 100
        const density = (occurrences * keywordWords) / totalWords * 100;

        return Math.round(density * 100) / 100; // Round to 2 decimal places
    });

    /**
     * Calculate keyword prominence score (0-100)
     */
    const prominenceScore = computed(() => {
        if (!targetKeyword.value.trim()) return 0;

        let score = 0;

        // In title (25 points)
        if (isInTitle.value) score += 25;

        // In first paragraph (20 points)
        if (isInFirstParagraph.value) score += 20;

        // In headings (20 points max - 5 per heading up to 4)
        score += Math.min(headingsWithKeyword.value * 5, 20);

        // In meta description (15 points)
        if (isInMetaDescription.value) score += 15;

        // In slug (10 points)
        if (isInSlug.value) score += 10;

        // Optimal density bonus (10 points)
        // Optimal density is 1-2%
        if (keywordDensity.value >= 1 && keywordDensity.value <= 2) {
            score += 10;
        } else if (keywordDensity.value > 0 && keywordDensity.value < 3) {
            score += 5;
        }

        return Math.min(score, 100);
    });

    /**
     * Get keyword placement details
     */
    const keywordPlacements = computed<KeywordPlacement[]>(() => {
        return [
            {
                location: 'Title',
                found: isInTitle.value,
                count: countKeywordOccurrences(title.value, targetKeyword.value),
            },
            {
                location: 'First Paragraph',
                found: isInFirstParagraph.value,
            },
            {
                location: 'Headings',
                found: headingsWithKeyword.value > 0,
                count: headingsWithKeyword.value,
            },
            {
                location: 'Meta Description',
                found: isInMetaDescription.value,
            },
            {
                location: 'URL Slug',
                found: isInSlug.value,
            },
        ];
    });

    /**
     * Get keyword density status
     */
    const densityStatus = computed<'optimal' | 'low' | 'high' | 'none'>(() => {
        if (keywordDensity.value === 0) return 'none';
        if (keywordDensity.value >= 1 && keywordDensity.value <= 2) return 'optimal';
        if (keywordDensity.value < 1) return 'low';
        return 'high';
    });

    /**
     * Get keyword analysis recommendations
     */
    const recommendations = computed<string[]>(() => {
        const tips: string[] = [];

        if (!targetKeyword.value.trim()) {
            tips.push('Set a target keyword to track its usage throughout your content.');
            return tips;
        }

        if (!isInTitle.value) {
            tips.push('Include your target keyword in the title for better SEO.');
        }

        if (!isInFirstParagraph.value) {
            tips.push('Use your target keyword in the first paragraph to establish topic relevance.');
        }

        if (headingsWithKeyword.value === 0) {
            tips.push('Add your target keyword to at least one heading (H2 or H3).');
        }

        if (!isInMetaDescription.value) {
            tips.push('Include your target keyword in the meta description.');
        }

        if (!isInSlug.value) {
            tips.push('Add your target keyword to the URL slug for better SEO.');
        }

        if (densityStatus.value === 'low') {
            tips.push('Keyword density is low. Consider using the keyword more naturally in your content.');
        } else if (densityStatus.value === 'high') {
            tips.push('Keyword density is high. Avoid keyword stuffing - use synonyms and related terms.');
        }

        if (tips.length === 0) {
            tips.push('Excellent keyword optimization! Your content is well-optimized for the target keyword.');
        }

        return tips;
    });

    /**
     * Get complete keyword analysis
     */
    const analysis = computed<KeywordAnalysis>(() => {
        return {
            targetKeyword: targetKeyword.value,
            density: keywordDensity.value,
            occurrences: totalOccurrences.value,
            inTitle: isInTitle.value,
            inFirstParagraph: isInFirstParagraph.value,
            inHeadings: headingsWithKeyword.value,
            inMetaDescription: isInMetaDescription.value,
            inSlug: isInSlug.value,
            prominenceScore: prominenceScore.value,
        };
    });

    return {
        analysis,
        keywordPlacements,
        densityStatus,
        recommendations,
        keywordDensity,
        prominenceScore,
        totalOccurrences,
    };
}
