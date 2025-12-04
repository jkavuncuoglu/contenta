<template>
    <div
        ref="editorElement"
        class="markdown-content min-h-[600px] rounded-lg border border-neutral-200 bg-white text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-50"
    ></div>
</template>

<script setup lang="ts">
import EditorJS, { type OutputData } from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Code from '@editorjs/code';
import InlineCode from '@editorjs/inline-code';
import Paragraph from '@editorjs/paragraph';
import Delimiter from '@editorjs/delimiter';
import Raw from '@editorjs/raw';
import Marker from '@editorjs/marker';
import Link from '@editorjs/link';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Props {
    modelValue: string;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder:
        'Start writing your page content with markdown and shortcodes...',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editorElement = ref<HTMLElement | null>(null);
let editor: EditorJS | null = null;

onMounted(async () => {
    if (!editorElement.value) return;

    // Parse initial markdown content to Editor.js format
    const initialData = markdownToEditorJS(props.modelValue);

    editor = new EditorJS({
        holder: editorElement.value,
        placeholder: props.placeholder,
        data: initialData,
        tools: {
            header: {
                class: Header,
                config: {
                    placeholder: 'Enter a header',
                    levels: [1, 2, 3, 4, 5, 6],
                    defaultLevel: 2,
                },
            },
            list: {
                class: List,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered',
                },
            },
            code: {
                class: Code,
            },
            inlineCode: {
                class: InlineCode,
            },
            paragraph: {
                class: Paragraph,
                inlineToolbar: true,
            },
            delimiter: Delimiter,
            raw: {
                class: Raw,
                config: {
                    placeholder: 'Enter raw HTML or shortcodes...',
                },
            },
            marker: {
                class: Marker,
            },
            linkTool: {
                class: Link,
                config: {
                    endpoint: '/api/fetch-url', // Optional: for link previews
                },
            },
        },
        onChange: async () => {
            if (!editor) return;

            try {
                const outputData = await editor.save();
                const markdown = editorJSToMarkdown(outputData);
                emit('update:modelValue', markdown);
            } catch (error) {
                console.error('Error saving editor content:', error);
            }
        },
        onReady: () => {
            // Editor is ready
        },
    });
});

onBeforeUnmount(() => {
    if (editor) {
        editor.destroy();
        editor = null;
    }
});

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    async (newValue) => {
        if (!editor) return;

        try {
            const currentData = await editor.save();
            const currentMarkdown = editorJSToMarkdown(currentData);

            // Only update if the content has actually changed
            if (currentMarkdown !== newValue) {
                const newData = markdownToEditorJS(newValue);
                await editor.render(newData);
            }
        } catch (error) {
            console.error('Error updating editor:', error);
        }
    },
);

/**
 * Convert markdown to Editor.js format
 */
function markdownToEditorJS(markdown: string): OutputData {
    if (!markdown || markdown.trim() === '') {
        return {
            time: Date.now(),
            blocks: [],
            version: '2.28.0',
        };
    }

    const blocks: any[] = [];
    const lines = markdown.split('\n');
    let currentBlock = '';
    let inCodeBlock = false;
    let codeLanguage = '';
    let codeContent: string[] = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        // Handle code blocks
        if (line.trim().startsWith('```')) {
            if (inCodeBlock) {
                // End code block
                blocks.push({
                    type: 'code',
                    data: {
                        code: codeContent.join('\n'),
                    },
                });
                codeContent = [];
                inCodeBlock = false;
                codeLanguage = '';
            } else {
                // Start code block
                inCodeBlock = true;
                codeLanguage = line.trim().substring(3).trim();
            }
            continue;
        }

        if (inCodeBlock) {
            codeContent.push(line);
            continue;
        }

        // Handle shortcodes - treat as raw HTML
        if (line.trim().startsWith('[#') || line.trim().startsWith('[/#')) {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }

            // Collect entire shortcode
            let shortcode = line;
            let j = i + 1;
            while (j < lines.length && !lines[j].trim().startsWith('[/#')) {
                shortcode += '\n' + lines[j];
                j++;
            }
            if (j < lines.length) {
                shortcode += '\n' + lines[j];
                i = j;
            }

            blocks.push({
                type: 'raw',
                data: {
                    html: shortcode,
                },
            });
            continue;
        }

        // Handle headers
        const headerMatch = line.match(/^(#{1,6})\s+(.+)$/);
        if (headerMatch) {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }
            blocks.push({
                type: 'header',
                data: {
                    text: headerMatch[2],
                    level: headerMatch[1].length,
                },
            });
            continue;
        }

        // Handle unordered lists
        if (line.trim().startsWith('- ') || line.trim().startsWith('* ')) {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }

            const items: string[] = [];
            let j = i;
            while (
                j < lines.length &&
                (lines[j].trim().startsWith('- ') ||
                    lines[j].trim().startsWith('* '))
            ) {
                items.push(lines[j].trim().substring(2));
                j++;
            }
            i = j - 1;

            blocks.push({
                type: 'list',
                data: {
                    style: 'unordered',
                    items,
                },
            });
            continue;
        }

        // Handle ordered lists
        if (line.trim().match(/^\d+\.\s/)) {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }

            const items: string[] = [];
            let j = i;
            while (j < lines.length && lines[j].trim().match(/^\d+\.\s/)) {
                items.push(lines[j].trim().replace(/^\d+\.\s/, ''));
                j++;
            }
            i = j - 1;

            blocks.push({
                type: 'list',
                data: {
                    style: 'ordered',
                    items,
                },
            });
            continue;
        }

        // Handle delimiters
        if (line.trim() === '---' || line.trim() === '***') {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }
            blocks.push({
                type: 'delimiter',
                data: {},
            });
            continue;
        }

        // Handle empty lines - end current paragraph
        if (line.trim() === '') {
            if (currentBlock.trim()) {
                blocks.push({
                    type: 'paragraph',
                    data: {
                        text: currentBlock.trim(),
                    },
                });
                currentBlock = '';
            }
            continue;
        }

        // Accumulate text for paragraph
        if (currentBlock) {
            currentBlock += '\n' + line;
        } else {
            currentBlock = line;
        }
    }

    // Add any remaining text
    if (currentBlock.trim()) {
        blocks.push({
            type: 'paragraph',
            data: {
                text: currentBlock.trim(),
            },
        });
    }

    return {
        time: Date.now(),
        blocks,
        version: '2.28.0',
    };
}

/**
 * Convert Editor.js format to markdown
 */
function editorJSToMarkdown(data: OutputData): string {
    if (!data.blocks || data.blocks.length === 0) {
        return '';
    }

    const lines: string[] = [];

    for (const block of data.blocks) {
        switch (block.type) {
            case 'header':
                lines.push(
                    '#'.repeat(block.data.level) + ' ' + block.data.text,
                );
                lines.push('');
                break;

            case 'paragraph':
                lines.push(block.data.text);
                lines.push('');
                break;

            case 'list':
                if (block.data.style === 'ordered') {
                    block.data.items.forEach((item: string, index: number) => {
                        lines.push(`${index + 1}. ${item}`);
                    });
                } else {
                    block.data.items.forEach((item: string) => {
                        lines.push(`- ${item}`);
                    });
                }
                lines.push('');
                break;

            case 'code':
                lines.push('```');
                lines.push(block.data.code);
                lines.push('```');
                lines.push('');
                break;

            case 'raw':
                lines.push(block.data.html);
                lines.push('');
                break;

            case 'delimiter':
                lines.push('---');
                lines.push('');
                break;

            default:
                // Handle unknown block types
                if (block.data.text) {
                    lines.push(block.data.text);
                    lines.push('');
                }
        }
    }

    return lines.join('\n').trim();
}

// Expose methods for parent component
defineExpose({
    async insertShortcode(template: string) {
        if (!editor) return;

        const blocks = await editor.blocks.getBlocksCount();
        await editor.blocks.insert(
            'raw',
            {
                html: template,
            },
            {},
            blocks,
            true,
        );
    },
});
</script>

<style>
/* Editor.js styles */
.ce-block__content,
.ce-toolbar__content {
  max-width: 100%;
}

.codex-editor {
  min-height: 600px;
  padding: 1.5rem;
}

.codex-editor__redactor {
    padding-bottom: 16rem;
}

.ce-paragraph {
    color: rgb(31 41 55); /* text-neutral-800 */
}

.dark .ce-paragraph {
    color: rgb(250 250 250); /* text-neutral-50 */
}

.ce-block {
  color: rgb(23 23 23);
}

.dark .ce-block {
  color: rgb(245 245 245);
}

.ce-toolbar__plus,
.ce-toolbar__settings-btn {
  color: rgb(82 82 82);
}

.dark .ce-toolbar__plus,
.dark .ce-toolbar__settings-btn {
  color: rgb(163 163 163);
}

.ce-toolbar__plus:hover,
.ce-toolbar__settings-btn:hover {
  background-color: rgb(244 244 245);
}

.dark .ce-toolbar__plus:hover,
.dark .ce-toolbar__settings-btn:hover {
  background-color: rgb(64 64 64);
}

.ce-inline-toolbar,
.ce-conversion-toolbar,
.ce-settings {
  border-color: rgb(229 229 229);
  background-color: rgb(255 255 255);
}

.dark .ce-inline-toolbar,
.dark .ce-conversion-toolbar,
.dark .ce-settings {
  border-color: rgb(64 64 64);
  background-color: rgb(38 38 38);
}

.ce-inline-tool,
.ce-conversion-tool,
.ce-settings__button {
  color: rgb(64 64 64);
}

.dark .ce-inline-tool,
.dark .ce-conversion-tool,
.dark .ce-settings__button {
  color: rgb(212 212 212);
}

.ce-inline-tool:hover,
.ce-conversion-tool:hover,
.ce-settings__button:hover {
  background-color: rgb(244 244 245);
}

.dark .ce-inline-tool:hover,
.dark .ce-conversion-tool:hover,
.dark .ce-settings__button:hover {
  background-color: rgb(64 64 64);
}

.ce-popover {
  border-color: rgb(229 229 229);
  background-color: rgb(255 255 255);
}

.dark .ce-popover {
  border-color: rgb(64 64 64);
  background-color: rgb(38 38 38);
}

.ce-popover__item {
  color: rgb(64 64 64);
}

.dark .ce-popover__item {
  color: rgb(212 212 212);
}

.ce-popover__item:hover {
  background-color: rgb(244 244 245);
}

.dark .ce-popover__item:hover {
  background-color: rgb(64 64 64);
}

/* Dark mode for code blocks */
.dark .cdx-block.ce-code__textarea {
  background-color: rgb(23 23 23);
  color: rgb(245 245 245);
}
</style>
