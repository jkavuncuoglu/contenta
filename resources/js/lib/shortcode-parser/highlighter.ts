import { Tokenizer, TokenType, type Token } from './tokenizer'

export interface HighlightedSegment {
  text: string
  type: string
}

/**
 * Syntax highlighter for shortcode syntax
 * Returns segments with CSS class names for styling
 */
export class ShortcodeHighlighter {
  public highlight(input: string): HighlightedSegment[] {
    const tokenizer = new Tokenizer(input)
    const tokens = tokenizer.tokenize()
    const segments: HighlightedSegment[] = []

    for (const token of tokens) {
      if (token.type === TokenType.EOF) continue

      const cssClass = this.getTokenClass(token.type)
      segments.push({
        text: this.getTokenText(token),
        type: cssClass,
      })
    }

    return segments
  }

  private getTokenClass(type: TokenType): string {
    switch (type) {
      case TokenType.SHORTCODE_OPEN:
        return 'shortcode-tag-open'
      case TokenType.SHORTCODE_CLOSE:
        return 'shortcode-tag-close'
      case TokenType.SHORTCODE_SELF_CLOSE:
        return 'shortcode-self-close'
      case TokenType.ATTRIBUTE_NAME:
        return 'shortcode-attr-name'
      case TokenType.ATTRIBUTE_VALUE:
        return 'shortcode-attr-value'
      case TokenType.EQUALS:
        return 'shortcode-equals'
      case TokenType.CONTENT_OPEN:
      case TokenType.CONTENT_CLOSE:
        return 'shortcode-brace'
      case TokenType.TEXT:
        return 'shortcode-text'
      case TokenType.MARKDOWN:
        return 'shortcode-markdown'
      case TokenType.COMMENT:
        return 'shortcode-comment'
      default:
        return ''
    }
  }

  private getTokenText(token: Token): string {
    switch (token.type) {
      case TokenType.SHORTCODE_OPEN:
        return `[#${token.value}`
      case TokenType.SHORTCODE_CLOSE:
        return `[/#${token.value}]`
      case TokenType.SHORTCODE_SELF_CLOSE:
        return ' /]'
      case TokenType.ATTRIBUTE_NAME:
        return ` ${token.value}`
      case TokenType.ATTRIBUTE_VALUE:
        return `"${token.value}"`
      case TokenType.EQUALS:
        return '='
      case TokenType.CONTENT_OPEN:
        return ']{'
      case TokenType.CONTENT_CLOSE:
        return '}'
      case TokenType.COMMENT:
        return `[#!--${token.value}--]`
      default:
        return token.value
    }
  }
}

/**
 * Get HTML string with syntax highlighting
 */
export function highlightShortcodeHTML(input: string): string {
  const highlighter = new ShortcodeHighlighter()
  const segments = highlighter.highlight(input)

  return segments
    .map((segment) => {
      if (segment.type) {
        return `<span class="${segment.type}">${escapeHtml(segment.text)}</span>`
      }
      return escapeHtml(segment.text)
    })
    .join('')
}

function escapeHtml(text: string): string {
  const div = document.createElement('div')
  div.textContent = text
  return div.innerHTML
}
