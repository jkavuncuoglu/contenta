import { computed, type Ref } from 'vue';

export interface ContentQuality {
    wordCount: number;
    targetWordCount: { min: number; max: number };
    readingTime: number; // minutes
    paragraphCount: number;
    sentenceCount: number;
    avgSentenceLength: number;
    avgParagraphLength: number;
    hasImages: boolean;
    imageCount: number;
    hasVideos: boolean;
    qualityScore: number; // 0-100
}

export interface ContentMetrics {
    wordCount: number;
    characterCount: number;
    paragraphCount: number;
    sentenceCount: number;
    avgSentenceLength: number;
    avgWordsPerParagraph: number;
    readingTime: number;
    imageCount: number;
    videoCount: number;
    linkCount: number;
    headingCount: {
        h1: number;
        h2: number;
        h3: number;
        h4: number;
        h5: number;
        h6: number;
        total: number;
    };
}

export function useContentMetrics(content: Ref<string>) {
    /**
     * Clean markdown content for word counting
     */
    const cleanContent = computed(() => {
        return content.value
            .replace(/^---[\s\S]*?---/, '') // Remove front matter
            .replace(/```[\s\S]*?```/g, '') // Remove code blocks
            .replace(/`([^`]+)`/g, '$1') // Remove inline code
            .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1') // Replace links with text
            .replace(/!\[([^\]]*)\]\([^)]+\)/g, '') // Remove images
            .replace(/^#{1,6}\s+/gm, '') // Remove heading markers
            .replace(/\*\*([^*]+)\*\*/g, '$1') // Remove bold
            .replace(/\*([^*]+)\*/g, '$1') // Remove italic
            .replace(/~~([^~]+)~~/g, '$1') // Remove strikethrough
            .replace(/>\s+/gm, '') // Remove blockquotes
            .replace(/[-*+]\s+/gm, '') // Remove list markers
            .replace(/\d+\.\s+/gm, '') // Remove ordered list markers
            .trim();
    });

    /**
     * Count words in content
     */
    const wordCount = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;

        const words = text.split(/\s+/).filter((word) => word.length > 0);
        return words.length;
    });

    /**
     * Count characters (without spaces)
     */
    const characterCount = computed(() => {
        return cleanContent.value.replace(/\s/g, '').length;
    });

    /**
     * Count characters (with spaces)
     */
    const characterCountWithSpaces = computed(() => {
        return cleanContent.value.length;
    });

    /**
     * Count paragraphs
     */
    const paragraphCount = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;

        const paragraphs = text.split(/\n\n+/).filter((p) => p.trim().length > 0);
        return paragraphs.length;
    });

    /**
     * Count sentences
     */
    const sentenceCount = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;

        // Split on sentence boundaries (., !, ?)
        const sentences = text.split(/[.!?]+/).filter((s) => s.trim().length > 0);
        return sentences.length;
    });

    /**
     * Calculate average sentence length in words
     */
    const avgSentenceLength = computed(() => {
        if (sentenceCount.value === 0) return 0;
        return Math.round((wordCount.value / sentenceCount.value) * 10) / 10;
    });

    /**
     * Calculate average words per paragraph
     */
    const avgWordsPerParagraph = computed(() => {
        if (paragraphCount.value === 0) return 0;
        return Math.round((wordCount.value / paragraphCount.value) * 10) / 10;
    });

    /**
     * Calculate reading time in minutes
     * Average reading speed: 200-250 words per minute (using 225)
     */
    const readingTime = computed(() => {
        const wordsPerMinute = 225;
        const minutes = wordCount.value / wordsPerMinute;
        return Math.max(1, Math.round(minutes)); // Minimum 1 minute
    });

    /**
     * Count images in markdown
     */
    const imageCount = computed(() => {
        const imageRegex = /!\[([^\]]*)\]\([^)]+\)/g;
        const matches = content.value.match(imageRegex);
        return matches ? matches.length : 0;
    });

    /**
     * Count videos (YouTube, Vimeo embeds or video tags)
     */
    const videoCount = computed(() => {
        const videoRegex = /(youtube\.com|youtu\.be|vimeo\.com|<video)/gi;
        const matches = content.value.match(videoRegex);
        return matches ? matches.length : 0;
    });

    /**
     * Count links (both internal and external)
     */
    const linkCount = computed(() => {
        const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
        const matches = content.value.match(linkRegex);
        return matches ? matches.length : 0;
    });

    /**
     * Count headings by level
     */
    const headingCount = computed(() => {
        const counts = {
            h1: 0,
            h2: 0,
            h3: 0,
            h4: 0,
            h5: 0,
            h6: 0,
            total: 0,
        };

        const h1Regex = /^#\s+/gm;
        const h2Regex = /^##\s+/gm;
        const h3Regex = /^###\s+/gm;
        const h4Regex = /^####\s+/gm;
        const h5Regex = /^#####\s+/gm;
        const h6Regex = /^######\s+/gm;

        counts.h1 = (content.value.match(h1Regex) || []).length;
        counts.h2 = (content.value.match(h2Regex) || []).length;
        counts.h3 = (content.value.match(h3Regex) || []).length;
        counts.h4 = (content.value.match(h4Regex) || []).length;
        counts.h5 = (content.value.match(h5Regex) || []).length;
        counts.h6 = (content.value.match(h6Regex) || []).length;
        counts.total = counts.h1 + counts.h2 + counts.h3 + counts.h4 + counts.h5 + counts.h6;

        return counts;
    });

    /**
     * Calculate content quality score (0-100)
     */
    const qualityScore = computed(() => {
        let score = 0;

        // Word count score (40 points)
        // Optimal: 1500-2500 words for blog posts
        const wc = wordCount.value;
        if (wc >= 1500 && wc <= 2500) {
            score += 40;
        } else if (wc >= 1000 && wc < 1500) {
            score += 30;
        } else if (wc >= 2500 && wc <= 3000) {
            score += 30;
        } else if (wc >= 500 && wc < 1000) {
            score += 20;
        } else if (wc > 3000) {
            score += 25;
        } else if (wc >= 300) {
            score += 10;
        }

        // Structure score (30 points)
        // Has headings
        if (headingCount.value.total > 0) score += 10;
        // Good paragraph count
        if (paragraphCount.value >= 5) score += 10;
        // Reasonable sentence length
        if (avgSentenceLength.value >= 15 && avgSentenceLength.value <= 25) score += 10;

        // Multimedia score (20 points)
        if (imageCount.value > 0) score += 10;
        if (videoCount.value > 0) score += 10;

        // Links score (10 points)
        if (linkCount.value >= 2) score += 10;

        return Math.min(score, 100);
    });

    /**
     * Get target word count based on content type
     */
    const targetWordCount = computed(() => {
        // For blog posts/articles: 1500-2500 words is ideal
        return { min: 1500, max: 2500 };
    });

    /**
     * Get word count status
     */
    const wordCountStatus = computed<'optimal' | 'short' | 'long' | 'too-short'>(() => {
        const wc = wordCount.value;
        const { min, max } = targetWordCount.value;

        if (wc < 300) return 'too-short';
        if (wc >= min && wc <= max) return 'optimal';
        if (wc < min) return 'short';
        return 'long';
    });

    /**
     * Get complete content metrics
     */
    const metrics = computed<ContentMetrics>(() => {
        return {
            wordCount: wordCount.value,
            characterCount: characterCount.value,
            paragraphCount: paragraphCount.value,
            sentenceCount: sentenceCount.value,
            avgSentenceLength: avgSentenceLength.value,
            avgWordsPerParagraph: avgWordsPerParagraph.value,
            readingTime: readingTime.value,
            imageCount: imageCount.value,
            videoCount: videoCount.value,
            linkCount: linkCount.value,
            headingCount: headingCount.value,
        };
    });

    /**
     * Get content quality analysis
     */
    const quality = computed<ContentQuality>(() => {
        return {
            wordCount: wordCount.value,
            targetWordCount: targetWordCount.value,
            readingTime: readingTime.value,
            paragraphCount: paragraphCount.value,
            sentenceCount: sentenceCount.value,
            avgSentenceLength: avgSentenceLength.value,
            avgParagraphLength: avgWordsPerParagraph.value,
            hasImages: imageCount.value > 0,
            imageCount: imageCount.value,
            hasVideos: videoCount.value > 0,
            qualityScore: qualityScore.value,
        };
    });

    /**
     * Get content recommendations
     */
    const recommendations = computed<string[]>(() => {
        const tips: string[] = [];

        // Word count recommendations
        if (wordCountStatus.value === 'too-short') {
            tips.push('Content is very short. Aim for at least 300 words for better SEO.');
        } else if (wordCountStatus.value === 'short') {
            tips.push(`Content is ${wordCount.value} words. Aim for 1500-2500 words for optimal SEO.`);
        } else if (wordCountStatus.value === 'long') {
            tips.push('Content is quite long. Consider breaking it into multiple articles or adding a table of contents.');
        }

        // Structure recommendations
        if (headingCount.value.total === 0) {
            tips.push('Add headings (H2, H3) to improve content structure and readability.');
        }

        if (headingCount.value.h1 > 1) {
            tips.push('Multiple H1 headings detected. Use only one H1 per page (usually the title).');
        }

        if (paragraphCount.value < 3) {
            tips.push('Break your content into more paragraphs for better readability.');
        }

        if (avgSentenceLength.value > 30) {
            tips.push('Average sentence length is high. Consider using shorter sentences for better readability.');
        }

        // Multimedia recommendations
        if (imageCount.value === 0) {
            tips.push('Add images to make your content more engaging and visually appealing.');
        }

        // Links recommendations
        if (linkCount.value < 2) {
            tips.push('Add internal and external links to provide more value and improve SEO.');
        }

        return tips;
    });

    return {
        metrics,
        quality,
        recommendations,
        wordCount,
        characterCount,
        characterCountWithSpaces,
        paragraphCount,
        sentenceCount,
        avgSentenceLength,
        readingTime,
        imageCount,
        videoCount,
        linkCount,
        headingCount,
        qualityScore,
        wordCountStatus,
    };
}
