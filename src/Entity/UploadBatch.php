<?php

namespace App\Entity;

use App\Repository\UploadBatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadBatchRepository::class)]
class UploadBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $batch_name = null;

    #[ORM\Column]
    private ?int $total_received_files = null;

    #[ORM\Column(options: ["default" => 0], nullable: true)]
    private ?int $total_uploaded_files = null;

    #[ORM\Column(options: ["default" => 0], nullable: true)]
    private ?int $total_failed_upload = null;

    #[ORM\Column(options: ["default" => 0], nullable: true)]
    private ?int $total_scanned = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'upload_batch')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Upload>
     */
    #[ORM\OneToMany(targetEntity: Upload::class, mappedBy: 'uploadBatch', orphanRemoval: true)]
    private Collection $upload;

    public function __construct()
    {
        $this->upload = new ArrayCollection();
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

    public function getBatchName(): ?string
    {
        return $this->batch_name;
    }

    public function setBatchName(string $batch_name): static
    {
        $this->batch_name = $batch_name;

        return $this;
    }

    public function getTotalReceivedFiles(): ?int
    {
        return $this->total_received_files;
    }

    public function setTotalReceivedFiles(int $total_received_files): static
    {
        $this->total_received_files = $total_received_files;

        return $this;
    }

    public function getTotalUploadedFiles(): ?int
    {
        return $this->total_uploaded_files;
    }

    public function setTotalUploadedFiles(int $total_uploaded_files): static
    {
        $this->total_uploaded_files = $total_uploaded_files;

        return $this;
    }

    public function getTotalFailedUpload(): ?int
    {
        return $this->total_failed_upload;
    }

    public function setTotalFailedUpload(int $total_failed_upload): static
    {
        $this->total_failed_upload = $total_failed_upload;

        return $this;
    }

    public function getTotalScanned(): ?int
    {
        return $this->total_scanned;
    }

    public function setTotalScanned(int $total_scanned): static
    {
        $this->total_scanned = $total_scanned;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(): static
    {
        $this->created_at = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(): static
    {
        $this->updated_at =  new \DateTimeImmutable();

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Upload>
     */
    public function getUpload(): Collection
    {
        return $this->upload;
    }

    public function addUpload(Upload $upload): static
    {
        if (!$this->upload->contains($upload)) {
            $this->upload->add($upload);
            $upload->setUploadBatch($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): static
    {
        if ($this->upload->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getUploadBatch() === $this) {
                $upload->setUploadBatch(null);
            }
        }

        return $this;
    }
}
