<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue()]
    private $id = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
