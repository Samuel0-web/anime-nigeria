<?php
namespace App\Support;

/**
 * Blog titles are stored as plain source text. The only supported syntax is
 * _italic_. Markers must sit at word boundaries, so snake_case_words are untouched.
 */
final class BlogTitle {
    private const ITALIC = '/(?<![\p{L}\p{N}_])_([^\s_](?:[^_]*[^\s_])?)_(?![\p{L}\p{N}_])/u';

    /** Escaped HTML with <em> for the markers. Safe to echo. */
    public static function html(string $source): string {
        $escaped = htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return preg_replace(self::ITALIC, '<em>$1</em>', $escaped) ?? $escaped;
    }

    /** Markers removed. For alt text, breadcrumbs, <title>, slugs. */
    public static function plain(string $source): string {
        return preg_replace(self::ITALIC, '$1', $source) ?? $source;
    }

    public static function slugify(string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');

        if (function_exists('transliterator_transliterate')) {
            $latin = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);

            if (is_string($latin)) {
                $text = $latin;
            }
        }

        return trim(preg_replace('/[^a-z0-9]+/', '-', $text) ?? '', '-');
    }

    public static function slugFromTitle(string $source): string {
        return self::slugify(self::plain($source));
    }
}