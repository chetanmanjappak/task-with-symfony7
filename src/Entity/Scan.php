<?php

namespace App\Entity;

use App\Repository\ScanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScanRepository::class)]
class Scan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ci_upload_id = null;

    #[ORM\Column]
    private ?int $upload_programs_file_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_scans = null;

    #[ORM\Column(nullable: true)]
    private ?int $remaining_scans = null;

    #[ORM\Column(nullable: true)]
    private ?float $percentage = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimated_days_left = null;

    #[ORM\Column(nullable: true)]
    private ?int $repository_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $commit_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $vulnerabilities_found = null;

    #[ORM\Column(nullable: true)]
    private ?int $unaffected_vulnerabilities_found = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $automations_action = null;

    #[ORM\Column(length: 50,nullable: true)]
    private ?string $policy_engine_action = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'scan')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Upload $upload = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCiUploadId(): ?int
    {
        return $this->ci_upload_id;
    }

    public function setCiUploadId(int $ci_upload_id): static
    {
        $this->ci_upload_id = $ci_upload_id;

        return $this;
    }

    public function getUploadProgramsFileId(): ?int
    {
        return $this->upload_programs_file_id;
    }

    public function setUploadProgramsFileId(int $upload_programs_file_id): static
    {
        $this->upload_programs_file_id = $upload_programs_file_id;

        return $this;
    }

    public function getTotalScans(): ?int
    {
        return $this->total_scans;
    }

    public function setTotalScans(?int $total_scans): static
    {
        $this->total_scans = $total_scans;

        return $this;
    }

    public function getRemainingScans(): ?int
    {
        return $this->remaining_scans;
    }

    public function setRemainingScans(?int $remaining_scans): static
    {
        $this->remaining_scans = $remaining_scans;

        return $this;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(?float $percentage): static
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getEstimatedDaysLeft(): ?int
    {
        return $this->estimated_days_left;
    }

    public function setEstimatedDaysLeft(?int $estimated_days_left): static
    {
        $this->estimated_days_left = $estimated_days_left;

        return $this;
    }

    public function getRepositoryId(): ?int
    {
        return $this->repository_id;
    }

    public function setRepositoryId(?int $repository_id): static
    {
        $this->repository_id = $repository_id;

        return $this;
    }

    public function getCommitId(): ?int
    {
        return $this->commit_id;
    }

    public function setCommitId(?int $commit_id): static
    {
        $this->commit_id = $commit_id;

        return $this;
    }

    public function getVulnerabilitiesFound(): ?int
    {
        return $this->vulnerabilities_found;
    }

    public function setVulnerabilitiesFound(?int $vulnerabilities_found): static
    {
        $this->vulnerabilities_found = $vulnerabilities_found;

        return $this;
    }

    public function getUnaffectedVulnerabilitiesFound(): ?int
    {
        return $this->unaffected_vulnerabilities_found;
    }

    public function setUnaffectedVulnerabilitiesFound(?int $unaffected_vulnerabilities_found): static
    {
        $this->unaffected_vulnerabilities_found = $unaffected_vulnerabilities_found;

        return $this;
    }

    public function getAutomationsAction(): ?string
    {
        return $this->automations_action;
    }

    public function setAutomationsAction(?string $automations_action): static
    {
        $this->automations_action = $automations_action;

        return $this;
    }

    public function getPolicyEngineAction(): ?string
    {
        return $this->policy_engine_action;
    }

    public function setPolicyEngineAction(string $policy_engine_action): static
    {
        $this->policy_engine_action = $policy_engine_action;

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
        $this->updated_at = new \DateTimeImmutable();

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

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): static
    {
        $this->upload = $upload;

        return $this;
    }
}
