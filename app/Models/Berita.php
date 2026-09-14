<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Berita extends Model
{
    protected $table = 'beritas';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'image_path',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viewCounter(): HasOne
    {
        return $this->hasOne(BeritaView::class, 'slug', 'slug');
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path ?: 'images/header2.jpg');
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->created_at) {
            return '-';
        }

        return $this->created_at->locale('id')->translatedFormat('d F Y');
    }

    public function getExcerptAttribute(): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $this->content)));

        return Str::limit($text, 150);
    }

    public function getContentParagraphsAttribute(): array
    {
        $content = trim((string) $this->content);
        if ($content === '') {
            return [];
        }

        $paragraphs = preg_split("/(?:\r\n){2,}|\n{2,}/", $content) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs)));

        return $paragraphs !== [] ? $paragraphs : [$content];
    }

    public function getContentBlocksAttribute(): array
    {
        $content = trim((string) $this->content);
        if ($content === '') {
            return [];
        }

        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        $blocks = [];
        $paragraphLines = [];
        $listItems = [];
        $listOrdered = false;

        $flushParagraph = static function () use (&$paragraphLines, &$blocks): void {
            if ($paragraphLines === []) {
                return;
            }

            $blocks[] = [
                'type' => 'paragraph',
                'content' => implode("\n", $paragraphLines),
            ];

            $paragraphLines = [];
        };

        $flushList = static function () use (&$listItems, &$listOrdered, &$blocks): void {
            if ($listItems === []) {
                return;
            }

            $blocks[] = [
                'type' => 'list',
                'items' => $listItems,
                'ordered' => $listOrdered,
            ];

            $listItems = [];
            $listOrdered = false;
        };

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                $flushParagraph();
                $flushList();
                continue;
            }

            if (preg_match('/^(?<marker>[-*•]|\d+\.)\s+(?<text>.+)$/u', $line, $matches) === 1) {
                $flushParagraph();

                $currentOrdered = preg_match('/^\d+\.$/', $matches['marker']) === 1;
                if ($listItems !== [] && $listOrdered !== $currentOrdered) {
                    $flushList();
                }

                $listOrdered = $currentOrdered;
                $listItems[] = $matches['text'];
                continue;
            }

            $flushList();
            $paragraphLines[] = $line;
        }

        $flushParagraph();
        $flushList();

        return $blocks;
    }

    public function getUploaderNameAttribute(): string
    {
        return $this->uploader?->name ?: 'Admin Balai K3';
    }

    public function getViewsTotalAttribute(): int
    {
        return (int) ($this->viewCounter?->views_count ?? 0);
    }

    public function getViewsLabelAttribute(): string
    {
        return number_format($this->views_total) . ' Views';
    }
}
