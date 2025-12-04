import { computed, type Ref } from 'vue';
import { useKeywordAnalysis, type KeywordAnalysis } from './useKeywordAnalysis';
import { useContentMetrics, type ContentQuality } from './useContentMetrics';
import { useReadabilityScore, type ReadabilityScores } from './useReadabilityScore';
import { useSlugAnalysis, type SlugAnalysis } from './useSlugAnalysis';

export interface SEOScore {
    overall: number; // 0-100
    keyword: number; // 0-100
    content: number; // 0-100
    readability: number; // 0-100
    technical: number; // 0-100
}

export interface SEOAnalysis {
    score: SEOScore;
    keyword: KeywordAnalysis;
    content: ContentQuality;
    readability: ReadabilityScores;
    slug: SlugAnalysis;
    status: 'excellent' | 'good' | 'needs-improvement' | 'poor';
    recommendations: SEORecommendation[];
}

export interface SEORecommendation {
    category: 'keyword' | 'content' | 'readability' | 'technical' | 'meta';
    priority: 'high' | 'medium' | 'low';
    message: string;
    fixed: boolean;
}

export interface TitleAnalysis {
    title: string;
    length: number;
    pixelWidth: number; // Estimated
    hasKeyword: boolean;
    isOptimalLength: boolean;
    score: number;
    status: 'optimal' | 'too-short' | 'too-long' | 'acceptable';
}

export interface MetaDescriptionAnalysis {
    description: string;
    length: number;
    pixelWidth: number; // Estimated
    hasKeyword: boolean;
    hasCTA: boolean;
    isOptimalLength: boolean;
    score: number;
    status: 'optimal' | 'too-short' | 'too-long' | 'acceptable';
}

export function useSEOAnalysis(
    targetKeyword: Ref<string>,
    title: Ref<string>,
    content: Ref<string>,
    slug: Ref<string>,
    metaDescription: Ref<string>,
) {
    // Initialize sub-composables
    const keywordAnalysis = useKeywordAnalysis(targetKeyword, title, content, slug, metaDescription);
    const contentMetrics = useContentMetrics(content);
    const readabilityScore = useReadabilityScore(content);
    const slugAnalysis = useSlugAnalysis(slug, targetKeyword);

    /**
     * Analyze title tag for SEO
     */
    const titleAnalysis = computed<TitleAnalysis>(() => {
        const titleText = title.value || '';
        const len = titleText.length;

        // Estimate pixel width (average character width ~8-10px in Google)
        const avgCharWidth = 9;
        const pixelWidth = len * avgCharWidth;

        // Check if keyword is in title
        const hasKeyword =
            targetKeyword.value.trim().length > 0 &&
            titleText.toLowerCase().includes(targetKeyword.value.toLowerCase());

        // Optimal: 50-60 characters or ~600px
        const isOptimalLength = len >= 50 && len <= 60;

        // Calculate score
        let score = 0;
        if (hasKeyword) score += 40;
        if (isOptimalLength) score += 30;
        else if ((len >= 40 && len < 50) || (len > 60 && len <= 70)) score += 20;
        else if (len >= 30 && len <= 80) score += 10;

        if (len > 0) score += 10; // Has title
        if (len >= 40) score += 10; // Minimum acceptable length
        if (pixelWidth <= 600) score += 10; // Within Google's display limit

        // Determine status
        let status: TitleAnalysis['status'];
        if (len >= 50 && len <= 60) status = 'optimal';
        else if (len < 30) status = 'too-short';
        else if (len > 70) status = 'too-long';
        else status = 'acceptable';

        return {
            title: titleText,
            length: len,
            pixelWidth,
            hasKeyword,
            isOptimalLength,
            score: Math.min(score, 100),
            status,
        };
    });

    /**
     * Analyze meta description for SEO
     */
    const metaDescriptionAnalysis = computed<MetaDescriptionAnalysis>(() => {
        const desc = metaDescription.value || '';
        const len = desc.length;

        // Estimate pixel width
        const avgCharWidth = 8;
        const pixelWidth = len * avgCharWidth;

        // Check if keyword is in description
        const hasKeyword =
            targetKeyword.value.trim().length > 0 &&
            desc.toLowerCase().includes(targetKeyword.value.toLowerCase());

        // Check for Call-to-Action words
        const ctaWords = [
            'discover',
            'learn',
            'find',
            'get',
            'try',
            'start',
            'buy',
            'shop',
            'read',
            'see',
            'click',
            'download',
            'subscribe',
            'join',
            'explore',
        ];
        const hasCTA = ctaWords.some((word) => desc.toLowerCase().includes(word));

        // Optimal: 150-160 characters or ~920px
        const isOptimalLength = len >= 150 && len <= 160;

        // Calculate score
        let score = 0;
        if (hasKeyword) score += 40;
        if (hasCTA) score += 20;
        if (isOptimalLength) score += 30;
        else if ((len >= 120 && len < 150) || (len > 160 && len <= 180)) score += 20;
        else if (len >= 100 && len <= 200) score += 10;

        if (len > 0) score += 10; // Has description

        // Determine status
        let status: MetaDescriptionAnalysis['status'];
        if (len >= 150 && len <= 160) status = 'optimal';
        else if (len < 120) status = 'too-short';
        else if (len > 180) status = 'too-long';
        else status = 'acceptable';

        return {
            description: desc,
            length: len,
            pixelWidth,
            hasKeyword,
            hasCTA,
            isOptimalLength,
            score: Math.min(score, 100),
            status,
        };
    });

    /**
     * Calculate overall SEO score breakdown
     */
    const seoScore = computed<SEOScore>(() => {
        // Keyword optimization score (25%)
        const keywordScore = keywordAnalysis.prominenceScore.value;

        // Content quality score (25%)
        const contentScore = contentMetrics.qualityScore.value;

        // Readability score (25%)
        const readabilityScoreValue = readabilityScore.overallScore.value;

        // Technical SEO score (25%)
        // Based on: title, meta description, slug
        const titleScore = titleAnalysis.value.score;
        const metaScore = metaDescriptionAnalysis.value.score;
        const slugScore = slugAnalysis.score.value;
        const technicalScore = (titleScore + metaScore + slugScore) / 3;

        // Overall score (weighted average)
        const overall = (keywordScore + contentScore + readabilityScoreValue + technicalScore) / 4;

        return {
            overall: Math.round(overall),
            keyword: Math.round(keywordScore),
            content: Math.round(contentScore),
            readability: Math.round(readabilityScoreValue),
            technical: Math.round(technicalScore),
        };
    });

    /**
     * Get overall SEO status
     */
    const seoStatus = computed<SEOAnalysis['status']>(() => {
        const score = seoScore.value.overall;

        if (score >= 85) return 'excellent';
        if (score >= 70) return 'good';
        if (score >= 50) return 'needs-improvement';
        return 'poor';
    });

    /**
     * Generate prioritized SEO recommendations
     */
    const recommendations = computed<SEORecommendation[]>(() => {
        const recs: SEORecommendation[] = [];

        // Keyword recommendations (high priority if no keyword set)
        if (!targetKeyword.value.trim()) {
            recs.push({
                category: 'keyword',
                priority: 'high',
                message: 'Set a target keyword to optimize your content for search engines.',
                fixed: false,
            });
        } else {
            // Check keyword placement
            if (!keywordAnalysis.analysis.value.inTitle) {
                recs.push({
                    category: 'keyword',
                    priority: 'high',
                    message: 'Include target keyword in the title.',
                    fixed: false,
                });
            }

            if (!keywordAnalysis.analysis.value.inFirstParagraph) {
                recs.push({
                    category: 'keyword',
                    priority: 'medium',
                    message: 'Use target keyword in the first paragraph.',
                    fixed: false,
                });
            }

            if (keywordAnalysis.analysis.value.inHeadings === 0) {
                recs.push({
                    category: 'keyword',
                    priority: 'medium',
                    message: 'Add target keyword to at least one heading.',
                    fixed: false,
                });
            }

            if (!keywordAnalysis.analysis.value.inSlug) {
                recs.push({
                    category: 'keyword',
                    priority: 'high',
                    message: 'Include target keyword in the URL slug.',
                    fixed: false,
                });
            }
        }

        // Title recommendations
        if (titleAnalysis.value.status === 'too-short') {
            recs.push({
                category: 'meta',
                priority: 'high',
                message: 'Title is too short. Aim for 50-60 characters.',
                fixed: false,
            });
        } else if (titleAnalysis.value.status === 'too-long') {
            recs.push({
                category: 'meta',
                priority: 'medium',
                message: 'Title is too long and may be truncated in search results.',
                fixed: false,
            });
        }

        // Meta description recommendations
        if (!metaDescription.value.trim()) {
            recs.push({
                category: 'meta',
                priority: 'high',
                message: 'Add a meta description to improve click-through rates.',
                fixed: false,
            });
        } else if (metaDescriptionAnalysis.value.status === 'too-short') {
            recs.push({
                category: 'meta',
                priority: 'medium',
                message: 'Meta description is too short. Aim for 150-160 characters.',
                fixed: false,
            });
        } else if (metaDescriptionAnalysis.value.status === 'too-long') {
            recs.push({
                category: 'meta',
                priority: 'low',
                message: 'Meta description may be truncated in search results.',
                fixed: false,
            });
        }

        if (!metaDescriptionAnalysis.value.hasCTA) {
            recs.push({
                category: 'meta',
                priority: 'low',
                message: 'Add a call-to-action to your meta description.',
                fixed: false,
            });
        }

        // Content recommendations
        if (contentMetrics.wordCountStatus.value === 'too-short') {
            recs.push({
                category: 'content',
                priority: 'high',
                message: 'Content is very short. Aim for at least 300 words.',
                fixed: false,
            });
        } else if (contentMetrics.wordCountStatus.value === 'short') {
            recs.push({
                category: 'content',
                priority: 'medium',
                message: 'Content could be more comprehensive. Aim for 1500+ words.',
                fixed: false,
            });
        }

        if (contentMetrics.headingCount.value.total === 0) {
            recs.push({
                category: 'content',
                priority: 'medium',
                message: 'Add headings (H2, H3) to improve content structure.',
                fixed: false,
            });
        }

        if (contentMetrics.imageCount.value === 0) {
            recs.push({
                category: 'content',
                priority: 'low',
                message: 'Add images to make content more engaging.',
                fixed: false,
            });
        }

        if (contentMetrics.linkCount.value < 2) {
            recs.push({
                category: 'content',
                priority: 'low',
                message: 'Add internal and external links to provide more value.',
                fixed: false,
            });
        }

        // Readability recommendations
        if (readabilityScore.fleschReadingEase.value < 50) {
            recs.push({
                category: 'readability',
                priority: 'medium',
                message: 'Content is difficult to read. Use shorter sentences and simpler words.',
                fixed: false,
            });
        }

        // Slug recommendations
        if (slugAnalysis.status.value === 'poor') {
            recs.push({
                category: 'technical',
                priority: 'high',
                message: `URL slug needs optimization. ${slugAnalysis.recommendations.value[0]}`,
                fixed: false,
            });
        }

        // Sort by priority
        const priorityOrder = { high: 1, medium: 2, low: 3 };
        return recs.sort((a, b) => priorityOrder[a.priority] - priorityOrder[b.priority]);
    });

    /**
     * Get complete SEO analysis
     */
    const analysis = computed<SEOAnalysis>(() => {
        return {
            score: seoScore.value,
            keyword: keywordAnalysis.analysis.value,
            content: contentMetrics.quality.value,
            readability: readabilityScore.scores.value,
            slug: slugAnalysis.analysis.value,
            status: seoStatus.value,
            recommendations: recommendations.value,
        };
    });

    /**
     * Get high priority recommendations count
     */
    const highPriorityCount = computed(() => {
        return recommendations.value.filter((r) => r.priority === 'high').length;
    });

    /**
     * Get medium priority recommendations count
     */
    const mediumPriorityCount = computed(() => {
        return recommendations.value.filter((r) => r.priority === 'medium').length;
    });

    /**
     * Get low priority recommendations count
     */
    const lowPriorityCount = computed(() => {
        return recommendations.value.filter((r) => r.priority === 'low').length;
    });

    return {
        analysis,
        seoScore,
        seoStatus,
        recommendations,
        highPriorityCount,
        mediumPriorityCount,
        lowPriorityCount,
        titleAnalysis,
        metaDescriptionAnalysis,
        // Expose sub-composables
        keywordAnalysis,
        contentMetrics,
        readabilityScore,
        slugAnalysis,
    };
}
