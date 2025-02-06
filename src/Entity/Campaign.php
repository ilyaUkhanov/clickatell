<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use DateInterval;
use DateInvalidTimeZoneException;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 65535)]
    private ?string $template = null;

    #[ORM\Column(type: 'string', enumType: State::class)]
    public State $state = State::Pending;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SendingList $sendingList = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?DateTimeInterface $dateEnd = null;

    #[ORM\Column]
    private ?int $cursor = 0;

    #[ORM\Column(length: 255)]
    private ?string $timezone = null;

    public function __construct()
    {
        $this->cursor = 0;
        $this->dateStart = new \DateTimeImmutable();
        $this->dateStart->setTime(9,0);

        $this->dateEnd = new \DateTimeImmutable();
        $this->dateEnd = $this->dateEnd->add(new DateInterval('P7D'));
        $this->dateEnd->setTime(20, 0, 0);
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        // Reset cursor
        if($this->state === State::Pending || $this->state === State::Launched) {
            $this->setCursor(0);
        }

        return $this;
    }

    public function getSendingList(): ?SendingList
    {
        return $this->sendingList;
    }

    public function setSendingList(?SendingList $sendingList): static
    {
        $this->sendingList = $sendingList;
        // Reset cursor
        if($this->state === State::Pending || $this->state === State::Launched) {
            $this->setCursor(0);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }

    /**
     */
    public function getDateStart(): ?DateTimeInterface
    {
        return $this->dateStart;
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function getDateStartTimezone(): ?DateTimeInterface
    {
        $realDate = clone $this->dateStart;
        $realDate->setTimezone(new DateTimeZone($this->timezone));
        $realDate->setTime($this->dateStart->format('H'), $this->dateStart->format('i'), 0);

        return $realDate;
    }


    public function setDateStart(DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function getDateEndTimezone(): ?DateTimeInterface
    {
        $realDate = clone $this->dateEnd;
        $realDate->setTimezone(new DateTimeZone($this->timezone));
        $realDate->setTime($this->dateEnd->format('H'), $this->dateEnd->format('i'), 0);

        return $realDate;
    }

    public function setDateEnd(DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getCursor(): ?int
    {
        return $this->cursor;
    }

    public function setCursor(int $cursor): static
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }
}
