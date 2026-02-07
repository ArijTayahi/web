<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProfileImageExtension extends AbstractExtension
{
    private string $projectDir;

    public function __construct(#[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('profile_image', [$this, 'getProfileImagePath']),
        ];
    }

    public function getProfileImagePath(?User $user): ?string
    {
        if (!$user || !$user->getUsername()) {
            return null;
        }

        $safeUsername = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $user->getUsername());
        $userDir = $this->projectDir . '/public/uploads/users/' . $safeUsername;
        $candidates = ['pfp.png', 'pfp.jpg', 'pfp.jpeg'];

        foreach ($candidates as $candidate) {
            $fullPath = $userDir . '/' . $candidate;
            if (is_file($fullPath)) {
                return 'uploads/users/' . $safeUsername . '/' . $candidate;
            }
        }

        return null;
    }
}
