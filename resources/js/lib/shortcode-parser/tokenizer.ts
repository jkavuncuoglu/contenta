/**
 * Token types for shortcode syntax
 */
export enum TokenType {
  SHORTCODE_OPEN = 'SHORTCODE_OPEN',
  SHORTCODE_CLOSE = 'SHORTCODE_CLOSE',
  SHORTCODE_SELF_CLOSE = 'SHORTCODE_SELF_CLOSE',
  ATTRIBUTE_NAME = 'ATTRIBUTE_NAME',
  ATTRIBUTE_VALUE = 'ATTRIBUTE_VALUE',
  EQUALS = 'EQUALS',
  CONTENT_OPEN = 'CONTENT_OPEN',
  CONTENT_CLOSE = 'CONTENT_CLOSE',
  TEXT = 'TEXT',
  MARKDOWN = 'MARKDOWN',
  COMMENT = 'COMMENT',
  EOF = 'EOF',
}

export interface Token {
  type: TokenType
  value: string
  line: number
  column: number
}

/**
 * JavaScript implementation of the shortcode tokenizer
 * Used for syntax highlighting in the editor
 */
export class Tokenizer {
  private input: string
  private position = 0
  private line = 1
  private column = 1
  private tokens: Token[] = []

  constructor(input: string) {
    this.input = input
  }

  public tokenize(): Token[] {
    while (!this.isAtEnd()) {
      this.scanToken()
    }

    this.tokens.push({
      type: TokenType.EOF,
      value: '',
      line: this.line,
      column: this.column,
    })

    return this.tokens
  }

  private scanToken(): void {
    const char = this.peek()

    if (char === '[') {
      this.scanShortcode()
    } else {
      this.scanText()
    }
  }

  private scanShortcode(): void {
    if (this.peek() !== '[') return

    const startLine = this.line
    const startColumn = this.column
    this.advance() // consume '['

    // Check for comment: [#!--
    if (this.peek() === '#' && this.peekNext() === '!') {
      this.scanComment()
      return
    }

    // Check for closing tag: [/#
    const isClosing = this.peek() === '/'
    if (isClosing) {
      this.advance() // consume '/'
    }

    // Expect '#' for opening tag
    if (this.peek() !== '#' && !isClosing) {
      this.position-- // not a shortcode, treat as text
      this.scanText()
      return
    }

    if (!isClosing) {
      this.advance() // consume '#'
    }

    // Scan tag name
    const tagName = this.scanIdentifier()

    if (isClosing) {
      // Expect ']' for closing tag
      if (this.peek() === ']') {
        this.advance()
        this.tokens.push({
          type: TokenType.SHORTCODE_CLOSE,
          value: tagName,
          line: startLine,
          column: startColumn,
        })
      }
      return
    }

    // Opening tag
    this.tokens.push({
      type: TokenType.SHORTCODE_OPEN,
      value: tagName,
      line: startLine,
      column: startColumn,
    })

    // Scan attributes
    this.skipWhitespace()
    while (!this.isAtEnd() && this.peek() !== ']' && this.peek() !== '/' && this.peek() !== '{') {
      this.scanAttribute()
      this.skipWhitespace()
    }

    // Check for self-closing: /]
    if (this.peek() === '/' && this.peekNext() === ']') {
      this.advance() // consume '/'
      this.advance() // consume ']'
      this.tokens.push({
        type: TokenType.SHORTCODE_SELF_CLOSE,
        value: '',
        line: this.line,
        column: this.column,
      })
      return
    }

    // Consume closing ']'
    if (this.peek() === ']') {
      this.advance()
    }

    // Check for content block: {
    if (this.peek() === '{') {
      this.scanContent()
    }
  }

  private scanComment(): void {
    const startLine = this.line
    const startColumn = this.column

    this.advance() // consume '#'
    this.advance() // consume '!'

    let comment = ''
    // Scan until '--]'
    while (!this.isAtEnd()) {
      if (this.peek() === '-' && this.peekNext() === '-' && this.peekAt(2) === ']') {
        this.advance() // consume '-'
        this.advance() // consume '-'
        this.advance() // consume ']'
        break
      }
      comment += this.advance()
    }

    this.tokens.push({
      type: TokenType.COMMENT,
      value: comment,
      line: startLine,
      column: startColumn,
    })
  }

  private scanAttribute(): void {
    const name = this.scanIdentifier()
    if (!name) return

    const startLine = this.line
    const startColumn = this.column

    this.tokens.push({
      type: TokenType.ATTRIBUTE_NAME,
      value: name,
      line: startLine,
      column: startColumn,
    })

    this.skipWhitespace()

    // Check for '='
    if (this.peek() === '=') {
      this.tokens.push({
        type: TokenType.EQUALS,
        value: '=',
        line: this.line,
        column: this.column,
      })
      this.advance()
      this.skipWhitespace()

      // Scan attribute value
      const value = this.scanAttributeValue()
      if (value !== null) {
        this.tokens.push({
          type: TokenType.ATTRIBUTE_VALUE,
          value,
          line: this.line,
          column: this.column,
        })
      }
    }
  }

  private scanAttributeValue(): string | null {
    const quote = this.peek()

    if (quote !== '"' && quote !== "'") {
      return null
    }

    this.advance() // consume opening quote

    let value = ''
    while (!this.isAtEnd() && this.peek() !== quote) {
      if (this.peek() === '\\' && this.peekNext() === quote) {
        this.advance() // consume backslash
        value += this.advance() // add escaped quote
      } else {
        value += this.advance()
      }
    }

    if (this.peek() === quote) {
      this.advance() // consume closing quote
    }

    return value
  }

  private scanContent(): void {
    this.tokens.push({
      type: TokenType.CONTENT_OPEN,
      value: '{',
      line: this.line,
      column: this.column,
    })
    this.advance() // consume '{'

    let content = ''
    let braceDepth = 1

    while (!this.isAtEnd() && braceDepth > 0) {
      if (this.peek() === '{') {
        braceDepth++
        content += this.advance()
      } else if (this.peek() === '}') {
        braceDepth--
        if (braceDepth === 0) break
        content += this.advance()
      } else {
        content += this.advance()
      }
    }

    // Determine if content is markdown or contains shortcodes
    const isMarkdown = this.containsMarkdown(content)

    this.tokens.push({
      type: isMarkdown ? TokenType.MARKDOWN : TokenType.TEXT,
      value: content.trim(),
      line: this.line,
      column: this.column,
    })

    if (this.peek() === '}') {
      this.tokens.push({
        type: TokenType.CONTENT_CLOSE,
        value: '}',
        line: this.line,
        column: this.column,
      })
      this.advance() // consume '}'
    }
  }

  private scanText(): void {
    const startLine = this.line
    const startColumn = this.column
    let text = ''

    while (!this.isAtEnd() && this.peek() !== '[') {
      text += this.advance()
    }

    if (text.length > 0) {
      this.tokens.push({
        type: TokenType.TEXT,
        value: text,
        line: startLine,
        column: startColumn,
      })
    }
  }

  private scanIdentifier(): string {
    let identifier = ''

    while (!this.isAtEnd() && this.isAlphaNumeric(this.peek())) {
      identifier += this.advance()
    }

    return identifier
  }

  private containsMarkdown(content: string): boolean {
    // Simple heuristics to detect markdown
    return (
      content.includes('**') ||
      content.includes('*') ||
      content.includes('#') ||
      content.includes('[') ||
      content.includes('`')
    )
  }

  private isAlphaNumeric(char: string): boolean {
    return /[a-zA-Z0-9_-]/.test(char)
  }

  private skipWhitespace(): void {
    while (!this.isAtEnd() && /\s/.test(this.peek())) {
      this.advance()
    }
  }

  private peek(): string {
    if (this.isAtEnd()) return '\0'
    return this.input[this.position]
  }

  private peekNext(): string {
    if (this.position + 1 >= this.input.length) return '\0'
    return this.input[this.position + 1]
  }

  private peekAt(offset: number): string {
    if (this.position + offset >= this.input.length) return '\0'
    return this.input[this.position + offset]
  }

  private advance(): string {
    const char = this.input[this.position++]

    if (char === '\n') {
      this.line++
      this.column = 1
    } else {
      this.column++
    }

    return char
  }

  private isAtEnd(): boolean {
    return this.position >= this.input.length
  }
}
