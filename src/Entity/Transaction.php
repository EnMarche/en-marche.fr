<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\Table(name="donation_transactions", indexes={
 *     @ORM\Index(name="donation_transactions_result_idx", columns={"paybox_result_code"})
 * })
 */
class Transaction
{
    public const PAYBOX_SUCCESS = '00000';
    public const PAYBOX_CONNECTION_FAILED = '00001';
    public const PAYBOX_INTERNAL_ERROR = '00003';
    public const PAYBOX_CARD_NUMBER_INVALID = '00004';
    public const PAYBOX_CARD_END_DATE_INVALID = '00008';
    public const PAYBOX_CARD_UNAUTHORIZED = '000021';
    public const PAYBOX_PAYMENT_PAGE_TIMEOUT = '000030';

    private const PAYBOX_RESULT_LABELS = [
        self::PAYBOX_SUCCESS => 'Succès',
        self::PAYBOX_CONNECTION_FAILED => 'Problème de connexion',
        self::PAYBOX_INTERNAL_ERROR => 'Erreur interne',
        self::PAYBOX_CARD_NUMBER_INVALID => 'Numéro de carte invalide',
        self::PAYBOX_CARD_END_DATE_INVALID => 'Date d\'expiration de carte invalide',
        self::PAYBOX_CARD_UNAUTHORIZED => 'Carte non autorisée',
        self::PAYBOX_PAYMENT_PAGE_TIMEOUT => 'Timeout',
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxResultCode;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxAuthorizationCode;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $payboxPayload;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $payboxDateTime;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true, nullable=true)
     */
    private $payboxTransactionId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $payboxSubscriptionId;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var Donation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Donation", inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $donation;

    public function __construct(Donation $donation, array $payboxPayload)
    {
        $this->donation = $donation;
        $this->createdAt = new \DateTimeImmutable();
        $this->payboxPayload = $payboxPayload;
        $this->payboxResultCode = $payboxPayload['result'];
        $this->payboxAuthorizationCode = $payboxPayload['authorization'] ?: null;
        $this->payboxSubscriptionId = $payboxPayload['subscription'] ?: null;
        $this->payboxTransactionId = $payboxPayload['transaction'] ?: null;

        if (isset($payboxPayload['date'], $payboxPayload['time'])) {
            $this->payboxDateTime = \DateTimeImmutable::createFromFormat(
                'dmYH:i:s',
                $payboxPayload['date'].str_replace('%3A', ':', $payboxPayload['time'])
            );
        }
    }

    public function __toString()
    {
        $str = '['.$this->getPayboxResultLabel().']';

        if ($this->payboxDateTime) {
            $str .= ' '.$this->payboxDateTime->format('Y/m/d H:i:s');
        }

        return $str;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayboxResultCode(): ?string
    {
        return $this->payboxResultCode;
    }

    public function getPayboxResultLabel(): ?string
    {
        return self::PAYBOX_RESULT_LABELS[$this->payboxResultCode] ?? $this->payboxResultCode;
    }

    public function getPayboxAuthorizationCode(): ?string
    {
        return $this->payboxAuthorizationCode;
    }

    public function getPayboxPayload(): ?array
    {
        return $this->payboxPayload;
    }

    public function getDonation(): Donation
    {
        return $this->donation;
    }

    public function getPayboxDateTime(): ?\DateTimeImmutable
    {
        return $this->payboxDateTime;
    }

    public function getPayboxTransactionId(): ?string
    {
        return $this->payboxTransactionId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPayboxSubscriptionId(): ?string
    {
        return $this->payboxSubscriptionId;
    }

    public function isSuccessful(): bool
    {
        return self::PAYBOX_SUCCESS === $this->payboxResultCode;
    }
}
