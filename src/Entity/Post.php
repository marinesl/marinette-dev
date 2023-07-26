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
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[UniqueEntity('slug', 'Un post existe déjà avec ce slug.')]
class Post
{
    use CreatedAtTrait;
    use EditedAtTrait;
    use MetaDescriptionTrait;
    use MetaKeywordTrait;
    use TitleTrait;
    use ContentTrait;
    use SlugTrait;
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PostCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->editedAt = new \DateTimeImmutable();
    }

    public function getCategory(): ?PostCategory
    {
        return $this->category;
    }

    public function setCategory(?PostCategory $category): self
    {
        $this->category = $category;

        return $this;
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
}
