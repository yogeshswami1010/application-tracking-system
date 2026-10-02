<?php
namespace App\Services;

final class EmailSignatureHtml
{
    public static function clean(?string $html): string
    {
        if (trim((string) $html) === '') return '';
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $doc->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $allowed = ['table','tbody','thead','tfoot','tr','td','th','div','span','p','br','strong','b','em','i','u','a','img','hr','ul','ol','li'];
        $attributes = ['style','width','height','align','valign','cellpadding','cellspacing','border','colspan','rowspan','alt','title'];
        $properties = ['color','background-color','font-family','font-size','font-weight','font-style','line-height','text-align','text-decoration','vertical-align','width','height','max-width','max-height','padding','padding-top','padding-right','padding-bottom','padding-left','margin','margin-top','margin-right','margin-bottom','margin-left','border','border-top','border-right','border-bottom','border-left','border-collapse','border-spacing','display'];
        $walk = function ($parent) use (&$walk, $allowed, $attributes, $properties) {
            foreach (iterator_to_array($parent->childNodes) as $node) {
                if ($node instanceof \DOMComment) { $parent->removeChild($node); continue; }
                if (!($node instanceof \DOMElement)) continue;
                $tag = strtolower($node->tagName);
                if (!in_array($tag, $allowed, true)) { $parent->removeChild($node); continue; }
                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $name = strtolower($attribute->name);
                    $value = trim($attribute->value);
                    $urlAttribute = ($tag === 'img' && $name === 'src') || ($tag === 'a' && $name === 'href');
                    if ($urlAttribute) {
                        $schemes = $tag === 'img' ? ['https','http'] : ['https','http','mailto','tel'];
                        if (!in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), $schemes, true) || preg_match('/[\x00-\x20]/', $value)) $node->removeAttribute($name);
                    } elseif (!in_array($name, $attributes, true)) {
                        $node->removeAttribute($name);
                    } elseif ($name === 'style') {
                        $styles = [];
                        foreach (explode(';', $value) as $declaration) {
                            $parts = explode(':', $declaration, 2);
                            if (count($parts) !== 2) continue;
                            $property = strtolower(trim($parts[0]));
                            $setting = trim($parts[1]);
                            if (in_array($property, $properties, true) && preg_match('/^[a-zA-Z0-9\s#.,%()\x27"-]+$/D', $setting) && !preg_match('/url|expression|javascript|@/i', $setting)) $styles[] = $property.':'.$setting;
                        }
                        $node->setAttribute('style', implode(';', $styles));
                    } elseif (in_array($name, ['width','height','cellpadding','cellspacing','border','colspan','rowspan'], true) && !preg_match('/^\d{1,4}%?$/D', $value)) {
                        $node->removeAttribute($name);
                    }
                }
                if ($tag === 'img' && !$node->hasAttribute('src')) { $parent->removeChild($node); continue; }
                $walk($node);
            }
        };
        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) return '';
        $walk($body);
        $result = '';
        foreach ($body->childNodes as $child) $result .= $doc->saveHTML($child);
        return trim($result);
    }
}
