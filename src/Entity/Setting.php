<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\IdTrait;
use App\Repository\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    use IdTrait;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $googleAnalyticsCode = null;

    #[ORM\Column(length: 100)]
    private ?string $url = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\OneToOne(inversedBy: 'home', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $home = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getGoogleAnalyticsCode(): ?string
    {
        return $this->googleAnalyticsCode;
    }

    public function setGoogleAnalyticsCode(?string $googleAnalyticsCode): self
    {
        $this->googleAnalyticsCode = $googleAnalyticsCode;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getHome(): ?Page
    {
        return $this->home;
    }

    public function setHome(Page $home): self
    {
        $this->home = $home;

        return $this;
    }
}
