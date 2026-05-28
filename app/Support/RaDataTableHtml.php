<?php

namespace App\Support;

/**
 * Reusable action markup for admin Yajra DataTables (Tailwind / jc-action-btn).
 */
final class RaDataTableHtml
{
    public const SVG_EDIT = '<svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';

    public const SVG_TRASH = '<svg class="h-3.5 w-3.5" fill="none" stroke="#EF4444" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';

    public const SVG_EYE = '<svg class="h-3.5 w-3.5" fill="none" stroke="#475569" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';

    public const SVG_DOWNLOAD = '<svg class="h-3.5 w-3.5" fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>';

    public const SVG_EXTERNAL = '<svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>';

    public const SVG_COPY = '<svg class="h-3.5 w-3.5" fill="none" stroke="#6366F1" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';

    public const SVG_REFRESH = '<svg class="h-3.5 w-3.5" fill="none" stroke="#D97706" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

    public const SVG_CLONE = '<svg class="h-3.5 w-3.5" fill="none" stroke="#B45309" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';

    public const SVG_PLAY = '<svg class="h-3.5 w-3.5" fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

    public static function wrap(string $inner): string
    {
        return '<div class="flex flex-wrap items-center justify-end gap-2">'.$inner.'</div>';
    }

    public static function edit(string $href, ?string $tooltip = null): string
    {
        $t = e($tooltip ?? __('app.edit'));

        return '<a href="'.e($href).'" class="jc-action-btn jc-btn-edit inline-flex" data-tip="'.$t.'" aria-label="'.$t.'">'.self::SVG_EDIT.'</a>';
    }

    public static function view(string $href, ?string $tooltip = null): string
    {
        $t = e($tooltip ?? __('app.view'));

        return '<a href="'.e($href).'" class="jc-action-btn jc-btn-slate inline-flex" data-tip="'.$t.'" aria-label="'.$t.'">'.self::SVG_EYE.'</a>';
    }

    public static function deleteSaParams(int|string $rowId, ?string $tooltip = null): string
    {
        $t = e($tooltip ?? __('app.delete'));

        return '<a href="javascript:;" class="jc-action-btn jc-btn-del sa-params inline-flex" data-tip="'.$t.'" aria-label="'.$t.'" data-row-id="'.e((string) $rowId).'">'.self::SVG_TRASH.'</a>';
    }

    /**
     * Link with icon (e.g. download, open URL).
     */
    public static function linkIcon(string $href, string $svg, string $jcBtnClass, ?string $tooltip = null): string
    {
        $t = $tooltip !== null ? e($tooltip) : '';
        $tipAttrs = $tooltip !== null ? ' data-tip="'.$t.'" aria-label="'.$t.'"' : '';

        return '<a href="'.e($href).'" class="jc-action-btn '.$jcBtnClass.' inline-flex"'.$tipAttrs.'>'.$svg.'</a>';
    }

    public static function externalLink(string $href, string $svg, string $jcBtnClass, ?string $tooltip = null): string
    {
        $t = $tooltip !== null ? e($tooltip) : '';
        $tipAttrs = $tooltip !== null ? ' data-tip="'.$t.'" aria-label="'.$t.'"' : '';

        return '<a href="'.e($href).'" target="_blank" rel="noopener noreferrer" class="jc-action-btn '.$jcBtnClass.' inline-flex"'.$tipAttrs.'>'.$svg.'</a>';
    }

    /**
     * javascript:; control with extra classes (e.g. edit-language, show-document).
     *
     * @param  array<string, string|int>  $htmlAttributes
     */
    public static function scriptLink(string $javascriptHref, string $svg, string $jcModifier, ?string $tooltip = null): string
    {
        $attrs = ['href="'.$javascriptHref.'"', 'class="jc-action-btn '.$jcModifier.' inline-flex"'];
        if ($tooltip !== null) {
            $attrs[] = 'data-tip="'.e($tooltip).'"';
            $attrs[] = 'aria-label="'.e($tooltip).'"';
        }

        return '<a '.implode(' ', $attrs).'>'.$svg.'</a>';
    }

    public static function js(string $svg, string $jcModifier, array $htmlAttributes = [], ?string $tooltip = null): string
    {
        $attrs = ['href="javascript:;"', 'class="jc-action-btn '.$jcModifier.' inline-flex"'];
        if ($tooltip !== null) {
            $attrs[] = 'data-tip="'.e($tooltip).'"';
            $attrs[] = 'aria-label="'.e($tooltip).'"';
        }
        foreach ($htmlAttributes as $key => $value) {
            $attrs[] = e((string) $key).'="'.e((string) $value).'"';
        }

        return '<a '.implode(' ', $attrs).'>'.$svg.'</a>';
    }
}
