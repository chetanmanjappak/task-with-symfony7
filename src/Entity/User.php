<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $slackChannel = null;

    /**
     * @var Collection<int, UploadBatch>
     */
    #[ORM\OneToMany(targetEntity: UploadBatch::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $upload_batch;

    /**
     * @var Collection<int, NotificationLogs>
     */
    #[ORM\OneToMany(targetEntity: NotificationLogs::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notification;

    public function __construct()
    {
        $this->upload_batch = new ArrayCollection();
        $this->notification = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getSlackChannel(): ?string
    {
        return $this->slackChannel;
    }

    public function setSlackChannel(?string $slackChannel): static
    {
        $this->slackChannel = $slackChannel;
        return $this;
    }

    /**
     * @return Collection<int, UploadBatch>
     */
    public function getUploadBatch(): Collection
    {
        return $this->upload_batch;
    }

    public function addUploadBatch(UploadBatch $uploadBatch): static
    {
        if (!$this->upload_batch->contains($uploadBatch)) {
            $this->upload_batch->add($uploadBatch);
            $uploadBatch->setUser($this);
        }

        return $this;
    }

    public function removeUploadBatch(UploadBatch $uploadBatch): static
    {
        if ($this->upload_batch->removeElement($uploadBatch)) {
            // set the owning side to null (unless already changed)
            if ($uploadBatch->getUser() === $this) {
                $uploadBatch->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NotificationLogs>
     */
    public function getNotification(): Collection
    {
        return $this->notification;
    }

    public function addNotification(NotificationLogs $notification): static
    {
        if (!$this->notification->contains($notification)) {
            $this->notification->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(NotificationLogs $notification): static
    {
        if ($this->notification->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }
}
