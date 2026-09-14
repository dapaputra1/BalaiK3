<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SocialMediaPost extends Model
{
    protected $table = 'social_media_posts';

    protected $fillable = [
        'user_id',
        'platform',
        'post_url',
    ];

    public static function platformDefinitions(): array
    {
        return [
            'instagram' => [
                'label' => 'Instagram',
                'icon' => 'bi bi-instagram',
                'accent' => '#d62976',
                'aspect_ratio' => '4 / 5',
                'format_label' => '1080 x 1350 (4:5)',
            ],
            'facebook' => [
                'label' => 'Facebook',
                'icon' => 'bi bi-facebook',
                'accent' => '#1877f2',
                'aspect_ratio' => '4 / 5',
                'format_label' => '1200 x 1500 (4:5)',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'icon' => 'bi bi-youtube',
                'accent' => '#ff0000',
                'aspect_ratio' => '16 / 9',
                'format_label' => '1280 x 720 (16:9)',
            ],
            'tiktok' => [
                'label' => 'TikTok',
                'icon' => 'bi bi-tiktok',
                'accent' => '#111111',
                'aspect_ratio' => '9 / 16',
                'format_label' => '1080 x 1920 (9:16)',
            ],
        ];
    }

    public static function platformKeys(): array
    {
        return array_keys(static::platformDefinitions());
    }

    public static function supportsUrl(string $platform, string $url): bool
    {
        return static::makeEmbedUrl($platform, $url) !== null;
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPlatformLabelAttribute(): string
    {
        return static::platformDefinitions()[$this->platform]['label'] ?? Str::headline((string) $this->platform);
    }

    public function getPlatformIconAttribute(): string
    {
        return static::platformDefinitions()[$this->platform]['icon'] ?? 'bi bi-share';
    }

    public function getPlatformAccentAttribute(): string
    {
        return static::platformDefinitions()[$this->platform]['accent'] ?? '#18456f';
    }

    public function getPlatformAspectRatioAttribute(): string
    {
        return static::platformDefinitions()[$this->platform]['aspect_ratio'] ?? '1 / 1';
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->updated_at) {
            return '-';
        }

        return $this->updated_at->locale('id')->translatedFormat('d F Y');
    }

    public function getUploaderNameAttribute(): string
    {
        return $this->uploader?->name ?: 'Admin Balai K3';
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return static::makeEmbedUrl((string) $this->platform, (string) $this->post_url);
    }

    public function getHasEmbedAttribute(): bool
    {
        return $this->embed_url !== null;
    }

    public static function makeEmbedUrl(string $platform, string $url): ?string
    {
        $normalizedUrl = static::normalizeUrl($url);
        if ($normalizedUrl === '') {
            return null;
        }

        return match ($platform) {
            'instagram' => static::buildInstagramEmbedUrl($normalizedUrl),
            'facebook' => static::buildFacebookEmbedUrl($normalizedUrl),
            'youtube' => static::buildYoutubeEmbedUrl($normalizedUrl),
            'tiktok' => static::buildTikTokEmbedUrl($normalizedUrl),
            default => null,
        };
    }

    private static function normalizeUrl(string $url): string
    {
        $normalized = trim($url);
        if ($normalized === '') {
            return '';
        }

        if (!preg_match('~^https?://~i', $normalized)) {
            $normalized = 'https://' . ltrim($normalized, '/');
        }

        return $normalized;
    }

    private static function buildInstagramEmbedUrl(string $url): ?string
    {
        if (!preg_match('~instagram\.com/(p|reel|tv)/([A-Za-z0-9_-]+)~i', $url, $matches)) {
            return null;
        }

        $type = strtolower($matches[1]);
        $shortcode = $matches[2];

        return "https://www.instagram.com/{$type}/{$shortcode}/embed";
    }

    private static function buildYoutubeEmbedUrl(string $url): ?string
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $queryString = (string) parse_url($url, PHP_URL_QUERY);

        parse_str($queryString, $query);

        $videoId = null;

        if (Str::contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (!empty($query['v'])) {
            $videoId = (string) $query['v'];
        } else {
            foreach (['embed/', 'shorts/', 'live/'] as $segment) {
                if (Str::contains($path, $segment)) {
                    $videoId = Str::after($path, $segment);
                    break;
                }
            }
        }

        if (!$videoId) {
            return null;
        }

        $videoId = preg_split('~/~', $videoId)[0] ?? $videoId;
        if (!preg_match('~^[A-Za-z0-9_-]{11}$~', $videoId)) {
            return null;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    private static function buildTikTokEmbedUrl(string $url): ?string
    {
        if (!preg_match('~tiktok\.com/(?:.+/)?video/(\d+)~i', $url, $matches)) {
            return null;
        }

        return 'https://www.tiktok.com/player/v1/' . $matches[1]
            . '?controls=1'
            . '&progress_bar=1'
            . '&play_button=1'
            . '&volume_control=1'
            . '&fullscreen_button=1'
            . '&timestamp=1'
            . '&description=0'
            . '&music_info=0'
            . '&rel=0';
    }

    private static function buildFacebookEmbedUrl(string $url): ?string
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        if (!Str::contains($host, ['facebook.com', 'fb.watch'])) {
            return null;
        }

        $encodedUrl = rawurlencode($url);
        $isVideo = preg_match('~(?:/videos/|/reel/|/watch/|fb\.watch)~i', $url) === 1;

        if ($isVideo) {
            return "https://www.facebook.com/plugins/video.php?href={$encodedUrl}&show_text=false&width=500";
        }

        return "https://www.facebook.com/plugins/post.php?href={$encodedUrl}&show_text=true&width=500";
    }
}
