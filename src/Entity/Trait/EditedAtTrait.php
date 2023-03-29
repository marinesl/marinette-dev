<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

trait EditedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['defaults' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $editedAt = null;

    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    public function setEditedAt(\DateTimeImmutable $editedAt): self
    {
        $this->editedAt = $editedAt;

        return $this;
    }
}