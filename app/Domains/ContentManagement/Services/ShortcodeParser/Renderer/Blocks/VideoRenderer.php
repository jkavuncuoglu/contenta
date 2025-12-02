<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class VideoRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'video';
    }

    public function getRequiredAttributes(): array
    {
        return ['src'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'provider' => 'auto',
            'title' => '',
            'aspectRatio' => '16:9',
            'controls' => 'true',
            'autoplay' => 'false',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $src = $this->attr($node, 'src');
        $provider = $this->attr($node, 'provider');
        $title = $this->attr($node, 'title');
        $aspectRatio = $this->attr($node, 'aspectRatio');

        // Auto-detect provider
        if ($provider === 'auto') {
            if (str_contains($src, 'youtube.com') || str_contains($src, 'youtu.be')) {
                $provider = 'youtube';
            } elseif (str_contains($src, 'vimeo.com')) {
                $provider = 'vimeo';
            } else {
                $provider = 'direct';
            }
        }

        $paddingClass = match ($aspectRatio) {
            '4:3' => 'pb-[75%]',
            '1:1' => 'pb-[100%]',
            default => 'pb-[56.25%]',
        };

        $html = sprintf('<div class="video-wrapper relative %s">', $paddingClass);

        if ($provider === 'youtube') {
            $videoId = $this->extractYouTubeId($src);
            $html .= sprintf(
                '<iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/%s" title="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                $videoId,
                $this->e($title)
            );
        } elseif ($provider === 'vimeo') {
            $videoId = $this->extractVimeoId($src);
            $html .= sprintf(
                '<iframe class="absolute top-0 left-0 w-full h-full" src="https://player.vimeo.com/video/%s" title="%s" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>',
                $videoId,
                $this->e($title)
            );
        } else {
            $controls = $this->attr($node, 'controls') === 'true' ? 'controls' : '';
            $autoplay = $this->attr($node, 'autoplay') === 'true' ? 'autoplay' : '';
            $html .= sprintf(
                '<video class="absolute top-0 left-0 w-full h-full" src="%s" %s %s></video>',
                $this->e($src),
                $controls,
                $autoplay
            );
        }

        $html .= '</div>';

        return $html;
    }

    private function extractYouTubeId(string $url): string
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?]+)/', $url, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function extractVimeoId(string $url): string
    {
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
