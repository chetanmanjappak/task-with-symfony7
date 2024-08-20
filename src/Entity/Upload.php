<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadRepository::class)]
class Upload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column(length: 300)]
    private ?string $file_path = null;

    #[ORM\Column(length: 50)]
    private ?string $file_type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $upload_at = null;

    #[ORM\ManyToOne(inversedBy: 'upload')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UploadBatch $uploadBatch = null;

    /**
     * @var Collection<int, Scan>
     */
    #[ORM\OneToMany(targetEntity: Scan::class, mappedBy: 'upload', orphanRemoval: true)]
    private Collection $scan;

    public function __construct()
    {
        $this->scan = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->file_type;
    }

    public function setFileType(string $file_type): static
    {
        $this->file_type = $file_type;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUploadAt(): ?\DateTimeImmutable
    {
        return $this->upload_at;
    }

    public function setUploadAt(\DateTimeImmutable $upload_at): static
    {
        $this->upload_at = $upload_at;

        return $this;
    }

    public function getUploadBatch(): ?UploadBatch
    {
        return $this->uploadBatch;
    }

    public function setUploadBatch(?UploadBatch $uploadBatch): static
    {
        $this->uploadBatch = $uploadBatch;

        return $this;
    }

    /**
     * @return Collection<int, Scan>
     */
    public function getScan(): Collection
    {
        return $this->scan;
    }

    public function addScan(Scan $scan): static
    {
        if (!$this->scan->contains($scan)) {
            $this->scan->add($scan);
            $scan->setUpload($this);
        }

        return $this;
    }

    public function removeScan(Scan $scan): static
    {
        if ($this->scan->removeElement($scan)) {
            // set the owning side to null (unless already changed)
            if ($scan->getUpload() === $this) {
                $scan->setUpload(null);
            }
        }

        return $this;
    }
}
