<?php
# Parsedown
# https://parsedown.org
#
# A Markdown parser for PHP that is fast, simple, and accurate.


class Parsedown
{
    const version = '1.7.4';

    protected $breaksEnabled = true;
    protected $markupEscaped = false;
    protected $safeMode = false;
    protected $inlineTypes = [];
    protected $textTypes = [];
    protected $blockTypes = [];
    protected $unmarkedBlockTypes = [];
    protected $DefinitionData = [];

    protected static $instance;

    # Static Methods

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function parse($text)
    {
        return static::instance()->text($text);
    }

    # Methods

    public function __construct()
    {
        $this->inlineTypes = [
            '#' => 'Inline\\Comment',
            '[' => 'Inline\\Link',
            '!' => 'Inline\\Image',
            '&' => 'Inline\\SpecialCharacter',
            '*' => 'Inline\\Emphasis',
            '_' => 'Inline\\Emphasis',
            '`' => 'Inline\\Code',
            '\\' => 'Inline\\EscapeSequence',
            '|' => 'Inline\\Pipe',
        ];

        $this->textTypes = [
            '*' => 'Text\\UnorderedList',
            '+' => 'Text\\UnorderedList',
            '-' => 'Text\\UnorderedList',
            '.' => 'Text\\OrderedList',
            '`' => 'Text\\Code',
            '>' => 'Text\\BlockQuote',
            '#' => 'Text\\Headline',
            '|' => 'Text\\Table',
            '=' => 'Text\\HorizontalRule',
            '_' => 'Text\\HorizontalRule',
            '~' => 'Text\\Code',
            '[' => 'Text\\Reference',
        ];

        $this->blockTypes = [
            '\n' => 'Block\\Paragraph',
        ] + $this->textTypes;

        $this->unmarkedBlockTypes = [
            '\n' => 'Block\\Paragraph',
        ];
    }

    public function text($text)
    {
        $Elements = $this->elements($this->lines($text));

        return $this->toHtml($Elements);
    }

    public function setBreaksEnabled($breaksEnabled)
    {
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    }

    public function setMarkupEscaped($markupEscaped = true)
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }

    public function setSafeMode($safeMode = true)
    {
        $this->safeMode = $safeMode;

        return $this;
    }

    # Protected Methods

    protected function elements(array $lines)
    {
        $Elements = [];
        $currentBlock = null;

        foreach ($lines as $line) {
            if (isset($currentBlock)) {
                $currentBlock = $this->addToCurrentBlock($line, $currentBlock);

                if ($currentBlock !== null) {
                    continue;
                }
            }

            $Elements []= $this->line($line);
        }

        return $Elements;
    }

    protected function addToCurrentBlock($line, $currentBlock)
    {
        $type = $currentBlock['type'];

        if (method_exists($this, $method = "addTo{$type}")) {
            return $this->$method($line, $currentBlock);
        }

        return null;
    }

    protected function line($line)
    {
        $element = [
            'type' => '',
            'element' => [
                'name' => '',
                'attributes' => [],
                'handler' => null,
                'text' => '',
            ],
        ];

        $firstTwo = substr($line, 0, 2);
        $first = $line[0];

        # ~

        if (isset($this->blockTypes[$first])) {
            $type = $this->blockTypes[$first];

            if (method_exists($this, $method = "line{$type}")) {
                return $this->$method($line);
            }
        }

        if (isset($this->blockTypes[$firstTwo])) {
            $type = $this->blockTypes[$firstTwo];

            if (method_exists($this, $method = "line{$type}")) {
                return $this->$method($line);
            }
        }

        # ~

        if (isset($this->unmarkedBlockTypes[$first])) {
            $type = $this->unmarkedBlockTypes[$first];

            if (method_exists($this, $method = "line{$type}")) {
                return $this->$method($line);
            }
        }

        if (isset($this->unmarkedBlockTypes[$firstTwo])) {
            $type = $this->unmarkedBlockTypes[$firstTwo];

            if (method_exists($this, $method = "line{$type}")) {
                return $this->$method($line);
            }
        }

        return $element;
    }

    protected function lines($text)
    {
        $lines = explode("\n", $text);

        return $lines;
    }

    protected function toHtml(array $Elements)
    {
        $html = '';

        foreach ($Elements as $Element) {
            $html .= $this->toHtmlElement($Element);
        }

        return $html;
    }

    protected function toHtmlElement(array $Element)
    {
        if (isset($Element['text'])) {
            return $Element['text'];
        }

        $element = $Element['element'];

        $attributes = $this->buildAttributes($element['attributes']);

        $text = '';

        if (isset($element['rawHtml'])) {
            $text = $element['rawHtml'];
        } elseif (isset($element['text'])) {
            $text = $element['text'];
        } elseif (isset($element['elements'])) {
            $text = $this->toHtml($element['elements']);
        }

        if (isset($element['handler'])) {
            $text = call_user_func($element['handler'], $text);
        }

        if ($this->markupEscaped) {
            $text = $this->escape($text);
            $attributes = $this->escape($attributes);
        }

        if ($element['name'] !== '') {
            return "<{$element['name']}{$attributes}>{$text}</{$element['name']}>";
        }

        return $text;
    }

    protected function buildAttributes(array $attributes)
    {
        $string = '';

        foreach ($attributes as $key => $value) {
            $string .= " {$key}=\"{$value}\"";
        }

        return $string;
    }

    protected function escape($text)
    {
        return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    }

    # Inline Elements

    protected function inlineComment($Excerpt)
    {
        if (preg_match('/^#[^\n]+/', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'rawHtml' => '',
                ],
            ];
        }
    }

    protected function inlineEscapeSequence($Excerpt)
    {
        if ($Excerpt['text'][0] === '\\' and strlen($Excerpt['text']) > 1) {
            return [
                'extent' => 2,
                'element' => [
                    'rawHtml' => $Excerpt['text'][1],
                ],
            ];
        }
    }

    protected function inlineSpecialCharacter($Excerpt)
    {
        if (preg_match('/^&(?:[a-zA-Z0-9]+|#\d+|#x[0-9a-fA-F]+);/', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'rawHtml' => $matches[0],
                ],
            ];
        }
    }

    protected function inlineLink($Excerpt)
    {
        if (preg_match('/^\[((?:[^\]]|\\\])+)\]\s*\(\s*(<[^>]+>|[^\s\)]+)\s*\)/', $Excerpt['text'], $matches)) {
            $text = $matches[1];
            $url = $matches[2];

            if ($this->safeMode) {
                $url = $this->safeModeSanitizeUrl($url);
            }

            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'a',
                    'attributes' => [
                        'href' => $url,
                    ],
                    'elements' => $this->parseInline($text),
                ],
            ];
        }
    }

    protected function inlineImage($Excerpt)
    {
        if (preg_match('/^!\[((?:[^\]]|\\\])+)\]\s*\(\s*(<[^>]+>|[^\s\)]+)\s*\)/', $Excerpt['text'], $matches)) {
            $text = $matches[1];
            $url = $matches[2];

            if ($this->safeMode) {
                $url = $this->safeModeSanitizeUrl($url);
            }

            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'img',
                    'attributes' => [
                        'src' => $url,
                        'alt' => $text,
                    ],
                ],
            ];
        }
    }

    protected function inlineCode($Excerpt)
    {
        if (preg_match('/^(`+)([^`\n]+(?<!`))\1/', $Excerpt['text'], $matches)) {
            $element = [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'code',
                    'text' => $matches[2],
                ],
            ];

            return $element;
        }
    }

    protected function inlineEmphasis($Excerpt)
    {
        if (preg_match('/^([*_]{1,3})([^\s\*_](?:[^\*_]+|\*(?!\s)|_(?!\s)){0,})\1/', $Excerpt['text'], $matches)) {
            $element = [
                'extent' => strlen($matches[0]),
            ];

            if (strlen($matches[1]) % 2 === 0) {
                $element['element'] = [
                    'name' => 'strong',
                    'elements' => $this->parseInline($matches[2]),
                ];
            } else {
                $element['element'] = [
                    'name' => 'em',
                    'elements' => $this->parseInline($matches[2]),
                ];
            }

            return $element;
        }
    }

    protected function inlinePipe($Excerpt)
    {
        if (strpos($Excerpt['text'], '|') !== false) {
            return [
                'extent' => 1,
                'element' => [
                    'rawHtml' => '|',
                ],
            ];
        }
    }

    # Text Elements

    protected function textUnorderedList($line)
    {
        if (preg_match('/^[\s]*:?[\*+-]\s+(.*)/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\UnorderedList',
                'element' => [
                    'name' => 'ul',
                    'elements' => [
                        [
                            'type' => 'Text\\UnorderedList\\Item',
                            'element' => [
                                'name' => 'li',
                                'elements' => $this->parseInline($matches[1]),
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    protected function addToTextUnorderedList($line, array $currentBlock)
    {
        if (preg_match('/^[\s]*:?[\*+-]\s+(.*)/', $line['text'], $matches)) {
            $currentBlock['element']['elements'] []= [
                'type' => 'Text\\UnorderedList\\Item',
                'element' => [
                    'name' => 'li',
                    'elements' => $this->parseInline($matches[1]),
                ],
            ];

            return $currentBlock;
        }

        if (preg_match('/^[\s]+(.*)/', $line['text'], $matches)) {
            if (isset($currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1])) {
                $currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1]['element']['elements'] []= [
                    'type' => 'Text\\UnorderedList\\Item\\Text',
                    'element' => [
                        'name' => 'p',
                        'elements' => $this->parseInline($matches[1]),
                    ],
                ];

                return $currentBlock;
            }
        }

        return null;
    }

    protected function textOrderedList($line)
    {
        if (preg_match('/^[\s]*\d+\.\s+(.*)/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\OrderedList',
                'element' => [
                    'name' => 'ol',
                    'elements' => [
                        [
                            'type' => 'Text\\OrderedList\\Item',
                            'element' => [
                                'name' => 'li',
                                'elements' => $this->parseInline($matches[1]),
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    protected function addToTextOrderedList($line, array $currentBlock)
    {
        if (preg_match('/^[\s]*\d+\.\s+(.*)/', $line['text'], $matches)) {
            $currentBlock['element']['elements'] []= [
                'type' => 'Text\\OrderedList\\Item',
                'element' => [
                    'name' => 'li',
                    'elements' => $this->parseInline($matches[1]),
                ],
            ];

            return $currentBlock;
        }

        if (preg_match('/^[\s]+(.*)/', $line['text'], $matches)) {
            if (isset($currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1])) {
                $currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1]['element']['elements'] []= [
                    'type' => 'Text\\OrderedList\\Item\\Text',
                    'element' => [
                        'name' => 'p',
                        'elements' => $this->parseInline($matches[1]),
                    ],
                ];

                return $currentBlock;
            }
        }

        return null;
    }

    protected function textBlockQuote($line)
    {
        if (preg_match('/^[\s]*>[\s]+(.*)/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\BlockQuote',
                'element' => [
                    'name' => 'blockquote',
                    'elements' => [
                        [
                            'type' => 'Text\\BlockQuote\\Text',
                            'element' => [
                                'name' => 'p',
                                'elements' => $this->parseInline($matches[1]),
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    protected function addToTextBlockQuote($line, array $currentBlock)
    {
        if (preg_match('/^[\s]*>[\s]+(.*)/', $line['text'], $matches)) {
            $currentBlock['element']['elements'] []= [
                'type' => 'Text\\BlockQuote\\Text',
                'element' => [
                    'name' => 'p',
                    'elements' => $this->parseInline($matches[1]),
                ],
            ];

            return $currentBlock;
        }

        if (preg_match('/^[\s]+(.*)/', $line['text'], $matches)) {
            if (isset($currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1])) {
                $currentBlock['element']['elements'][count($currentBlock['element']['elements']) - 1]['element']['elements'] []= [
                    'type' => 'Text\\BlockQuote\\Text',
                    'element' => [
                        'name' => 'p',
                        'elements' => $this->parseInline($matches[1]),
                    ],
                ];

                return $currentBlock;
            }
        }

        return null;
    }

    protected function textCode($line)
    {
        if (preg_match('/^(`+)([^`\n]+)(`+)$/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\Code',
                'element' => [
                    'name' => 'pre',
                    'elements' => [
                        [
                            'type' => 'Text\\Code\\Code',
                            'element' => [
                                'name' => 'code',
                                'text' => $matches[2],
                            ],
                        ],
                    ],
                ],
            ];
        }

        if (preg_match('/^~~~([^\n]*)\n?(.*)\n?~~~$/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\Code',
                'element' => [
                    'name' => 'pre',
                    'elements' => [
                        [
                            'type' => 'Text\\Code\\Code',
                            'element' => [
                                'name' => 'code',
                                'attributes' => [
                                    'class' => $matches[1],
                                ],
                                'text' => $matches[2],
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    protected function addToTextCode($line, array $currentBlock)
    {
        if (preg_match('/^(`+)(.*)/', $line['text'], $matches)) {
            $currentBlock['element']['elements'][0]['element']['text'] .= "\n" . $matches[2];

            return $currentBlock;
        }

        if (preg_match('/^~~~([^\n]*)\n?(.*)\n?~~~$/', $line['text'], $matches)) {
            $currentBlock['element']['elements'][0]['element']['text'] .= "\n" . $matches[2];

            return $currentBlock;
        }

        if (preg_match('/^([^`]+)$/', $line['text'], $matches)) {
            $currentBlock['element']['elements'][0]['element']['text'] .= "\n" . $matches[1];

            return $currentBlock;
        }

        return null;
    }

    protected function textHeadline($line)
    {
        if (preg_match('/^(#{1,6})\s*(.*?)\s*#*$/', $line['text'], $matches)) {
            $level = strlen($matches[1]);

            return [
                'type' => 'Text\\Headline',
                'element' => [
                    'name' => 'h' . $level,
                    'elements' => $this->parseInline($matches[2]),
                ],
            ];
        }
    }

    protected function textTable($line)
    {
        if (preg_match('/^[\s]*\|(.+)\n/', $line['text'], $matches)) {
            return [
                'type' => 'Text\\Table',
                'element' => [
                    'name' => 'table',
                    'elements' => [
                        [
                            'type' => 'Text\\Table\\Row',
                            'element' => [
                                'name' => 'tr',
                                'elements' => $this->parseTableRow($matches[1]),
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    protected function addToTextTable($line, array $currentBlock)
    {
        if (preg_match('/^[\s]*\|(.+)$/', $line['text'], $matches)) {
            $currentBlock['element']['elements'] []= [
                'type' => 'Text\\Table\\Row',
                'element' => [
                    'name' => 'tr',
                    'elements' => $this->parseTableRow($matches[1]),
                ],
            ];

            return $currentBlock;
        }

        if (preg_match('/^[\s]*\|(.+)\n/', $line['text'], $matches)) {
            $currentBlock['element']['elements'] []= [
                'type' => 'Text\\Table\\Row',
                'element' => [
                    'name' => 'tr',
                    'elements' => $this->parseTableRow($matches[1]),
                ],
            ];

            return $currentBlock;
        }

        return null;
    }

    protected function parseTableRow($row)
    {
        $elements = [];
        $cells = preg_split('/\s*\|\s*/', $row);

        foreach ($cells as $cell) {
            $elements []= [
                'type' => 'Text\\Table\\Row\\Cell',
                'element' => [
                    'name' => 'td',
                    'elements' => $this->parseInline($cell),
                ],
            ];
        }

        return $elements;
    }

    protected function textHorizontalRule($line)
    {
        if (preg_match('/^[\s]*([-_*]{3,})[\s]*$/', $line['text'])) {
            return [
                'type' => 'Text\\HorizontalRule',
                'element' => [
                    'name' => 'hr',
                ],
            ];
        }
    }

    protected function textReference($line)
    {
        if (preg_match('/^\[([^\]]+)\]:\s*([^\s]+).*$/', $line['text'], $matches)) {
            $this->DefinitionData['Reference'][$matches[1]] = $matches[2];

            return [
                'type' => 'Text\\Reference',
                'element' => [
                    'rawHtml' => '',
                ],
            ];
        }
    }

    # Blocks

    protected function lineBlockParagraph($line)
    {
        if ($line['text'] !== '') {
            return [
                'type' => 'Block\\Paragraph',
                'element' => [
                    'name' => 'p',
                    'elements' => $this->parseInline($line['text']),
                ],
            ];
        }
    }

    # Inline Parsing

    protected function parseInline($text)
    {
        $Elements = [];

        while ($text !== '') {
            $first = $text[0];

            if (isset($this->inlineTypes[$first])) {
                $type = $this->inlineTypes[$first];

                if (method_exists($this, $method = "inline{$type}")) {
                    $Element = $this->$method([
                        'text' => $text,
                    ]);

                    if ($Element !== null) {
                        $Elements []= $Element['element'];
                        $text = substr($text, $Element['extent']);
                        continue;
                    }
                }
            }

            if (isset($this->inlineTypes[$firstTwo = substr($text, 0, 2)])) {
                $type = $this->inlineTypes[$firstTwo];

                if (method_exists($this, $method = "inline{$type}")) {
                    $Element = $this->$method([
                        'text' => $text,
                    ]);

                    if ($Element !== null) {
                        $Elements []= $Element['element'];
                        $text = substr($text, $Element['extent']);
                        continue;
                    }
                }
            }

            if ($text[0] === '\n') {
                if ($this->breaksEnabled) {
                    $Elements []= [
                        'name' => 'br',
                    ];
                } else {
                    $Elements []= [
                        'rawHtml' => '\n',
                    ];
                }

                $text = substr($text, 1);
            } else {
                $length = strcspn($text, "\n" . implode('', array_keys($this->inlineTypes)));
                $length = max($length, 1);

                $Elements []= [
                    'rawHtml' => substr($text, 0, $length),
                ];

                $text = substr($text, $length);
            }
        }

        return $Elements;
    }

    # Safe Mode

    protected function safeModeSanitizeUrl($url)
    {
        if (preg_match('/^[a-zA-Z]+:\/\//', $url)) {
            $urlParts = parse_url($url);

            if (isset($urlParts['scheme'], $urlParts['host'])) {
                $scheme = strtolower($urlParts['scheme']);
                $host = strtolower($urlParts['host']);

                if (($scheme === 'http' || $scheme === 'https') && $this->safeModeIsTrustedUrl($host)) {
                    return $url;
                }
            }
        }

        return '';
    }

    protected function safeModeIsTrustedUrl($host)
    {
        $trustedHosts = [
            'youtube.com',
            'youtu.be',
            'vimeo.com',
        ];

        foreach ($trustedHosts as $trustedHost) {
            if (substr($host, -strlen($trustedHost) - 1) === '.' . $trustedHost) {
                return true;
            }

            if ($host === $trustedHost) {
                return true;
            }
        }

        return false;
    }
}
