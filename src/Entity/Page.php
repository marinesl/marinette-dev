<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\ContentTrait;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\EditedAtTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\MetaDescriptionTrait;
use App\Entity\Trait\MetaKeywordTrait;
use App\Entity\Trait\SlugTrait;
use App\Entity\Trait\TitleTrait;
use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity('slug', 'Une page existe déjà avec ce slug.')]
class Page
{
    use CreatedAtTrait;
    use EditedAtTrait;
    use MetaDescriptionTrait;
    use MetaKeywordTrait;
    use TitleTrait;
    use ContentTrait;
    use SlugTrait;
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\OneToOne(mappedBy: 'home', cascade: ['persist', 'remove'])]
    private ?Setting $home = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->editedAt = new \DateTimeImmutable();
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHome(): ?Setting
    {
        return $this->home;
    }

    public function setHome(Setting $home): self
    {
        // set the owning side of the relation if necessary
        if ($home->getHome() !== $this) {
            $home->setHome($this);
        }

        $this->home = $home;

        return $this;
    }
}
