<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetVersionExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_mtime', $this->assetMtime(...)),
        ];
    }

    public function assetMtime(string $path): string
    {
        $absolute = $this->projectDir . '/public/' . ltrim($path, '/');

        return is_file($absolute) ? (string) filemtime($absolute) : (string) time();
    }
}
