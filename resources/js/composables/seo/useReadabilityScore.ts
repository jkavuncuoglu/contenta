import { computed, type Ref } from 'vue';

export interface ReadabilityScores {
    fleschReadingEase: number; // 0-100 (higher = easier)
    fleschKincaidGrade: number; // US grade level
    smogIndex: number; // US grade level
    colemanLiauIndex: number; // US grade level
    automatedReadabilityIndex: number; // US grade level
    averageGradeLevel: number;
    readingLevel: string; // Text description
    passiveVoicePercentage: number;
    adverbPercentage: number;
    complexWordPercentage: number;
}

export interface ReadabilityAnalysis {
    scores: ReadabilityScores;
    overallScore: number; // 0-100
    status: 'very-easy' | 'easy' | 'fairly-easy' | 'standard' | 'fairly-difficult' | 'difficult' | 'very-difficult';
    recommendations: string[];
}

export function useReadabilityScore(content: Ref<string>) {
    /**
     * Clean content for readability analysis
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
     * Count syllables in a word (approximation)
     */
    const countSyllables = (word: string): number => {
        word = word.toLowerCase().trim();
        if (word.length <= 3) return 1;

        // Remove non-letters
        word = word.replace(/[^a-z]/g, '');

        // Count vowel groups
        const vowels = word.match(/[aeiouy]+/g);
        let count = vowels ? vowels.length : 0;

        // Adjust for silent 'e'
        if (word.endsWith('e')) count--;

        // Minimum 1 syllable
        return Math.max(count, 1);
    };

    /**
     * Count complex words (3+ syllables)
     */
    const countComplexWords = (text: string): number => {
        const words = text.split(/\s+/).filter((w) => w.length > 0);
        return words.filter((word) => countSyllables(word) >= 3).length;
    };

    /**
     * Count total syllables in text
     */
    const countTotalSyllables = (text: string): number => {
        const words = text.split(/\s+/).filter((w) => w.length > 0);
        return words.reduce((total, word) => total + countSyllables(word), 0);
    };

    /**
     * Count sentences
     */
    const sentenceCount = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;
        const sentences = text.split(/[.!?]+/).filter((s) => s.trim().length > 0);
        return Math.max(sentences.length, 1);
    });

    /**
     * Count words
     */
    const wordCount = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;
        const words = text.split(/\s+/).filter((w) => w.length > 0);
        return words.length;
    });

    /**
     * Count characters (without spaces)
     */
    const characterCount = computed(() => {
        return cleanContent.value.replace(/\s/g, '').length;
    });

    /**
     * Count syllables in entire text
     */
    const totalSyllables = computed(() => {
        return countTotalSyllables(cleanContent.value);
    });

    /**
     * Count complex words
     */
    const complexWords = computed(() => {
        return countComplexWords(cleanContent.value);
    });

    /**
     * Flesch Reading Ease Score (0-100, higher = easier)
     * Formula: 206.835 - 1.015 × (words/sentences) - 84.6 × (syllables/words)
     */
    const fleschReadingEase = computed(() => {
        if (wordCount.value === 0 || sentenceCount.value === 0) return 0;

        const wordsPerSentence = wordCount.value / sentenceCount.value;
        const syllablesPerWord = totalSyllables.value / wordCount.value;

        const score = 206.835 - 1.015 * wordsPerSentence - 84.6 * syllablesPerWord;

        return Math.max(0, Math.min(100, Math.round(score * 10) / 10));
    });

    /**
     * Flesch-Kincaid Grade Level (US grade level)
     * Formula: 0.39 × (words/sentences) + 11.8 × (syllables/words) - 15.59
     */
    const fleschKincaidGrade = computed(() => {
        if (wordCount.value === 0 || sentenceCount.value === 0) return 0;

        const wordsPerSentence = wordCount.value / sentenceCount.value;
        const syllablesPerWord = totalSyllables.value / wordCount.value;

        const grade = 0.39 * wordsPerSentence + 11.8 * syllablesPerWord - 15.59;

        return Math.max(0, Math.round(grade * 10) / 10);
    });

    /**
     * SMOG Index (Simple Measure of Gobbledygook)
     * Formula: 1.043 × √(polysyllables × 30/sentences) + 3.1291
     */
    const smogIndex = computed(() => {
        if (sentenceCount.value === 0) return 0;

        const polysyllables = complexWords.value;
        const grade = 1.043 * Math.sqrt((polysyllables * 30) / sentenceCount.value) + 3.1291;

        return Math.max(0, Math.round(grade * 10) / 10);
    });

    /**
     * Coleman-Liau Index (character-based)
     * Formula: 0.0588 × L - 0.296 × S - 15.8
     * L = average number of letters per 100 words
     * S = average number of sentences per 100 words
     */
    const colemanLiauIndex = computed(() => {
        if (wordCount.value === 0) return 0;

        const L = (characterCount.value / wordCount.value) * 100;
        const S = (sentenceCount.value / wordCount.value) * 100;

        const grade = 0.0588 * L - 0.296 * S - 15.8;

        return Math.max(0, Math.round(grade * 10) / 10);
    });

    /**
     * Automated Readability Index (ARI)
     * Formula: 4.71 × (characters/words) + 0.5 × (words/sentences) - 21.43
     */
    const automatedReadabilityIndex = computed(() => {
        if (wordCount.value === 0 || sentenceCount.value === 0) return 0;

        const charactersPerWord = characterCount.value / wordCount.value;
        const wordsPerSentence = wordCount.value / sentenceCount.value;

        const ari = 4.71 * charactersPerWord + 0.5 * wordsPerSentence - 21.43;

        return Math.max(0, Math.round(ari * 10) / 10);
    });

    /**
     * Average grade level across all formulas
     */
    const averageGradeLevel = computed(() => {
        const grades = [
            fleschKincaidGrade.value,
            smogIndex.value,
            colemanLiauIndex.value,
            automatedReadabilityIndex.value,
        ];

        const avg = grades.reduce((sum, grade) => sum + grade, 0) / grades.length;
        return Math.round(avg * 10) / 10;
    });

    /**
     * Detect passive voice (approximation)
     */
    const passiveVoicePercentage = computed(() => {
        const text = cleanContent.value;
        if (!text) return 0;

        // Simple passive voice detection using "to be" verbs + past participle patterns
        const passivePatterns = /\b(am|is|are|was|were|be|being|been)\s+\w+ed\b/gi;
        const matches = text.match(passivePatterns);
        const passiveCount = matches ? matches.length : 0;

        return Math.round((passiveCount / sentenceCount.value) * 100 * 10) / 10;
    });

    /**
     * Detect adverb usage
     */
    const adverbPercentage = computed(() => {
        if (wordCount.value === 0) return 0;

        // Simple adverb detection (words ending in -ly)
        const adverbPattern = /\b\w+ly\b/gi;
        const matches = cleanContent.value.match(adverbPattern);
        const adverbCount = matches ? matches.length : 0;

        return Math.round((adverbCount / wordCount.value) * 100 * 10) / 10;
    });

    /**
     * Complex word percentage
     */
    const complexWordPercentage = computed(() => {
        if (wordCount.value === 0) return 0;
        return Math.round((complexWords.value / wordCount.value) * 100 * 10) / 10;
    });

    /**
     * Get reading level description from Flesch Reading Ease score
     */
    const readingLevel = computed(() => {
        const score = fleschReadingEase.value;

        if (score >= 90) return 'Very Easy (5th grade)';
        if (score >= 80) return 'Easy (6th grade)';
        if (score >= 70) return 'Fairly Easy (7th grade)';
        if (score >= 60) return 'Standard (8th-9th grade)';
        if (score >= 50) return 'Fairly Difficult (10th-12th grade)';
        if (score >= 30) return 'Difficult (College)';
        return 'Very Difficult (College graduate)';
    });

    /**
     * Get readability status
     */
    const readabilityStatus = computed<ReadabilityAnalysis['status']>(() => {
        const score = fleschReadingEase.value;

        if (score >= 90) return 'very-easy';
        if (score >= 80) return 'easy';
        if (score >= 70) return 'fairly-easy';
        if (score >= 60) return 'standard';
        if (score >= 50) return 'fairly-difficult';
        if (score >= 30) return 'difficult';
        return 'very-difficult';
    });

    /**
     * Calculate overall readability score (0-100)
     */
    const overallScore = computed(() => {
        // Primary score from Flesch Reading Ease
        let score = fleschReadingEase.value;

        // Adjust based on other factors
        // Deduct points for high passive voice
        if (passiveVoicePercentage.value > 20) {
            score -= 5;
        }

        // Deduct points for excessive adverbs
        if (adverbPercentage.value > 10) {
            score -= 5;
        }

        // Deduct points for too many complex words
        if (complexWordPercentage.value > 20) {
            score -= 5;
        }

        return Math.max(0, Math.min(100, Math.round(score)));
    });

    /**
     * Get readability scores object
     */
    const scores = computed<ReadabilityScores>(() => {
        return {
            fleschReadingEase: fleschReadingEase.value,
            fleschKincaidGrade: fleschKincaidGrade.value,
            smogIndex: smogIndex.value,
            colemanLiauIndex: colemanLiauIndex.value,
            automatedReadabilityIndex: automatedReadabilityIndex.value,
            averageGradeLevel: averageGradeLevel.value,
            readingLevel: readingLevel.value,
            passiveVoicePercentage: passiveVoicePercentage.value,
            adverbPercentage: adverbPercentage.value,
            complexWordPercentage: complexWordPercentage.value,
        };
    });

    /**
     * Get readability recommendations
     */
    const recommendations = computed<string[]>(() => {
        const tips: string[] = [];

        // Based on Flesch Reading Ease
        if (fleschReadingEase.value < 50) {
            tips.push('Content is difficult to read. Use shorter sentences and simpler words.');
        } else if (fleschReadingEase.value < 60) {
            tips.push('Consider simplifying some sentences for broader readability.');
        }

        // Based on average sentence length
        const avgSentenceLength = wordCount.value / sentenceCount.value;
        if (avgSentenceLength > 25) {
            tips.push('Average sentence length is high. Break long sentences into shorter ones.');
        }

        // Based on passive voice
        if (passiveVoicePercentage.value > 20) {
            tips.push('High passive voice usage detected. Use active voice for clearer writing.');
        }

        // Based on adverbs
        if (adverbPercentage.value > 10) {
            tips.push('Excessive adverbs detected. Use stronger verbs instead of adverb + verb combinations.');
        }

        // Based on complex words
        if (complexWordPercentage.value > 20) {
            tips.push('High percentage of complex words. Use simpler alternatives where possible.');
        }

        // Based on grade level
        if (averageGradeLevel.value > 12) {
            tips.push('Content requires college-level reading ability. Consider your target audience.');
        }

        if (tips.length === 0) {
            tips.push('Excellent readability! Your content is clear and easy to understand.');
        }

        return tips;
    });

    /**
     * Get complete readability analysis
     */
    const analysis = computed<ReadabilityAnalysis>(() => {
        return {
            scores: scores.value,
            overallScore: overallScore.value,
            status: readabilityStatus.value,
            recommendations: recommendations.value,
        };
    });

    return {
        analysis,
        scores,
        overallScore,
        readabilityStatus,
        recommendations,
        fleschReadingEase,
        fleschKincaidGrade,
        averageGradeLevel,
        readingLevel,
    };
}
