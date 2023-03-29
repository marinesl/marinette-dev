<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\NameTrait;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    use NameTrait;
    use IdTrait;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Media::class)]
    private Collection $medias;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Page::class)]
    private Collection $pages;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: PostCategory::class)]
    private Collection $postCategories;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Post::class)]
    private Collection $posts;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->postCategories = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setStatus($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getStatus() === $this) {
                $media->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setStatus($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getStatus() === $this) {
                $page->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostCategory>
     */
    public function getPostCategories(): Collection
    {
        return $this->postCategories;
    }

    public function addPostCategory(PostCategory $postCategory): self
    {
        if (!$this->postCategories->contains($postCategory)) {
            $this->postCategories->add($postCategory);
            $postCategory->setStatus($this);
        }

        return $this;
    }

    public function removePostCategory(PostCategory $postCategory): self
    {
        if ($this->postCategories->removeElement($postCategory)) {
            // set the owning side to null (unless already changed)
            if ($postCategory->getStatus() === $this) {
                $postCategory->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setStatus($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getStatus() === $this) {
                $post->setStatus(null);
            }
        }

        return $this;
    }
}
