<?php
namespace App\Support;

/**
 * Allowlist sanitiser for Tiptap output.
 *
 * It never strips tags out of dirty HTML. It walks the parsed DOM and builds
 * fresh HTML, emitting only: p, h2, h3, strong, em, br, hr, a, ul, ol, li,
 * blockquote, figure > img + figcaption. Unknown elements are unwrapped (their
 * text survives), dangerous ones are dropped with their contents, and every
 * attribute is rebuilt from validated values. In the same pass it collects the
 * readable text (for the word count) and the heading list (for anchors / TOC).
 */
final class BlogHtml {
    public const WORDS_PER_MINUTE = 230;
    public const MAX_BYTES = 600000;

    private const MAX_DEPTH = 12;
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template',
        'noscript', 'head', 'title', 'link', 'meta', 'form', 'input', 'button', 'select',
        'textarea', 'audio', 'video', 'canvas'];
    private const INLINE_UNWRAP = ['span', 'u', 's', 'strike', 'del', 'ins', 'code', 'mark', 'sub',
        'sup', 'small', 'abbr', 'cite', 'font', 'label', 'kbd', 'time'];

    private string $text = '';
    /** @var list<array{id:string,text:string,level:int}> */
    private array $headings = [];
    /** @var array<string,true> */
    private array $usedIds = [];

    private function __construct(private \Closure $resolveImage) {}

    /**
     * @param callable(string): ?string $resolveImage maps a raw src to a usable URL, or null to drop the image
     * @return array{html:string, text:string, words:int, headings:list<array{id:string,text:string,level:int}>}
     */
    public static function process(string $dirty, callable $resolveImage): array {
        $dirty = trim(mb_scrub($dirty));
        $empty = ['html' => '', 'text' => '', 'words' => 0, 'headings' => []];

        if ($dirty === '') {
            return $empty;
        }

        $previous = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8"><body>' . $dirty . '</body>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return $empty;
        }

        $self = new self(\Closure::fromCallable($resolveImage));
        $html = $self->blocks($body, 0);

        return [
            'html' => $html,
            'text' => trim($self->text),
            'words' => self::countWords($self->text),
            'headings' => $self->headings,
        ];
    }

    public static function countWords(string $text): int {
        $parts = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? 0 : count($parts);
    }

    /** max(1, ceil(words / 230)) */
    public static function readingMinutes(int $words): int {
        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }

    /** Heading list for "From this article", read back from stored (sanitised) HTML. */
    public static function toc(string $html): array {
        preg_match_all('#<h([23]) id="([^"]+)">(.*?)</h\1>#us', $html, $m, PREG_SET_ORDER);

        return array_map(static fn (array $h): array => [
            'id' => $h[2],
            'text' => trim(html_entity_decode(strip_tags($h[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'level' => (int) $h[1],
        ], $m);
    }

    /** Adds the member article's existing BEM classes. Applied when the cache entry is built. */
    public static function decorate(string $html): string {
        $html = strtr($html, [
            '<p>' => '<p class="akd-post-content__paragraph">',
            '<ul>' => '<ul class="akd-post-content__list">',
            '<ol>' => '<ol class="akd-post-content__list">',
            '<blockquote>' => '<blockquote class="akd-post-content__quote">',
            '<figure>' => '<figure class="akd-post-content__figure">',
            '<figcaption>' => '<figcaption class="akd-post-content__caption">',
            '<hr>' => '<hr class="akd-post-content__separator">',
        ]);
        $html = preg_replace('/<h([23]) id=/', '<h$1 class="akd-post-content__heading" id=', $html) ?? $html;

        return preg_replace_callback(
            '/<img src="([^"]*)" alt="([^"]*)" loading="lazy">/',
            static fn (array $m): string => '<a href="' . $m[1] . '" data-fancybox><img class="akd-post-content__image" src="'
                . $m[1] . '" alt="' . $m[2] . '" loading="lazy"></a>',
            $html
        ) ?? $html;
    }

    // -------------------------------------------------------------------------
    /** Container of blocks: inline runs become <p>, block results are appended. */
    private function blocks(\DOMNode $node, int $depth): string {
        if ($depth > self::MAX_DEPTH) {
            return '';
        }

        $out = '';
        $run = '';

        foreach ($node->childNodes as $child) {
            [$html, $isBlock] = $this->node($child, $depth + 1);

            if ($isBlock) {
                $out .= $this->paragraph($run) . $html;
                $run = '';
            } else {
                $run .= $html;
            }
        }

        return $out . $this->paragraph($run);
    }

    private function inline(\DOMNode $node, int $depth): string {
        if ($depth > self::MAX_DEPTH) {
            return '';
        }

        $out = '';

        foreach ($node->childNodes as $child) {
            [$html, $isBlock] = $this->node($child, $depth + 1);

            if (!$isBlock) {
                $out .= $html;
            }
        }

        return $out;
    }

    private function paragraph(string $run): string {
        if (!self::hasText($run)) {
            return '';
        }

        $this->text .= ' ';

        return '<p>' . trim($run) . '</p>';
    }

    /** @return array{0:string,1:bool} [html, isBlock] */
    private function node(\DOMNode $n, int $depth): array {
        if ($n instanceof \DOMText) {
            $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', (string) $n->nodeValue) ?? '';
            $this->text .= $t;

            return [self::e($t), false];
        }

        if (!$n instanceof \DOMElement) {
            return ['', false];
        }

        $tag = strtolower($n->nodeName);

        if (in_array($tag, self::DROP, true)) {
            return ['', false];
        }

        switch ($tag) {
            case 'strong':
            case 'b':
                $i = $this->inline($n, $depth);
                return [self::hasText($i) ? "<strong>$i</strong>" : $i, false];

            case 'em':
            case 'i':
                $i = $this->inline($n, $depth);
                return [self::hasText($i) ? "<em>$i</em>" : $i, false];

            case 'br':
                $this->text .= ' ';
                return ['<br>', false];

            case 'a':
                return [$this->anchor($n, $depth), false];

            case 'p':
                return [$this->blocks($n, $depth), true];

            case 'h1':
            case 'h2':
                return [$this->heading($n, $depth, 'h2'), true];

            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return [$this->heading($n, $depth, 'h3'), true];

            case 'blockquote':
                $i = $this->blocks($n, $depth);
                return [$i === '' ? '' : "<blockquote>$i</blockquote>", true];

            case 'ul':
            case 'ol':
                return [$this->listHtml($n, $depth, $tag), true];

            case 'li':
                $i = $this->blocks($n, $depth);
                return [$i === '' ? '' : "<li>$i</li>", true];

            case 'hr':
                return ['<hr>', true];

            case 'img':
                return [$this->figure($n, (string) $n->getAttribute('data-caption')), true];

            case 'figure':
                $img = $n->getElementsByTagName('img')->item(0);
                $cap = $n->getElementsByTagName('figcaption')->item(0);

                return [$this->figure($img instanceof \DOMElement ? $img : null,
                    $cap !== null ? (string) $cap->textContent : ''), true];

            case 'figcaption':
                return ['', true];

            default:
                if (in_array($tag, self::INLINE_UNWRAP, true)) {
                    return [$this->inline($n, $depth), false];
                }

                return [$this->blocks($n, $depth), true]; // div, section, table...: keep the content
        }
    }

    private function heading(\DOMElement $n, int $depth, string $tag): string {
        $inner = $this->inline($n, $depth);
        $plain = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($plain === '') {
            return '';
        }

        $id = $this->headingId($plain);
        $this->headings[] = ['id' => $id, 'text' => $plain, 'level' => (int) substr($tag, 1)];
        $this->text .= ' ';

        return "<$tag id=\"$id\">$inner</$tag>";
    }

    /** Unique, URL-safe anchor. Incoming id attributes are never trusted or reused. */
    private function headingId(string $text): string {
        $base = substr(BlogTitle::slugify($text), 0, 80) ?: 'section';
        $id = $base;
        $n = 2;

        while (isset($this->usedIds[$id])) {
            $id = $base . '-' . $n++;
        }

        $this->usedIds[$id] = true;

        return $id;
    }

    private function listHtml(\DOMElement $n, int $depth, string $tag): string {
        $items = '';

        foreach ($n->childNodes as $c) {
            if ($c instanceof \DOMElement && strtolower($c->nodeName) === 'li') {
                $i = $this->blocks($c, $depth + 1);

                if ($i !== '') {
                    $items .= "<li>$i</li>";
                    $this->text .= ' ';
                }
            }
        }

        return $items === '' ? '' : "<$tag>$items</$tag>";
    }

    private function anchor(\DOMElement $n, int $depth): string {
        $inner = $this->inline($n, $depth);
        $href = self::safeHref(trim($n->getAttribute('href')));

        if ($href === null || !self::hasText($inner)) {
            return $inner;
        }

        $attrs = ' href="' . self::e($href) . '"';

        if (preg_match('#^https?://#i', $href)) {
            $attrs .= ' target="_blank" rel="noopener noreferrer"';
        }

        return "<a$attrs>$inner</a>";
    }

    private function figure(?\DOMElement $img, string $caption): string {
        if ($img === null) {
            return '';
        }

        $src = ($this->resolveImage)(trim($img->getAttribute('src')));

        if (!is_string($src) || $src === '') {
            return ''; // never emit an image that cannot be served
        }

        $alt = mb_substr(trim(preg_replace('/\s+/u', ' ', $img->getAttribute('alt')) ?? ''), 0, 200);
        $caption = mb_substr(trim(preg_replace('/\s+/u', ' ', $caption) ?? ''), 0, 300);
        $this->text .= ' ' . $caption . ' ';

        $html = '<figure><img src="' . self::e($src) . '" alt="' . self::e($alt) . '" loading="lazy">';

        if ($caption !== '') {
            $html .= '<figcaption>' . self::e($caption) . '</figcaption>';
        }

        return $html . '</figure>';
    }

    private static function safeHref(string $h): ?string {
        if ($h === '' || strlen($h) > 500 || preg_match('/[\x00-\x20\x7f]/', $h)) {
            return null;
        }

        if ($h[0] === '#') {
            return preg_match('/^#[A-Za-z0-9_-]+$/', $h) ? $h : null;
        }

        if ($h[0] === '/') {
            return (str_starts_with($h, '//') || str_contains($h, '\\')) ? null : $h;
        }

        if (preg_match('#^https?://#i', $h)) {
            return filter_var($h, FILTER_VALIDATE_URL) !== false ? $h : null;
        }

        if (stripos($h, 'mailto:') === 0) {
            return filter_var(substr($h, 7), FILTER_VALIDATE_EMAIL) !== false ? $h : null;
        }

        return null;
    }

    private static function hasText(string $html): bool {
        $decoded = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/\S/u', $decoded) === 1;
    }

    private static function e(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}