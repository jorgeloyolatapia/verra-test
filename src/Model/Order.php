<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Order
{
    private const array STREET_WORDS = ['st' => 'street', 'rd' => 'road'];
    private const array STATES = ['illinois' => 'il', 'california' => 'ca', 'new york' => 'ny'];

    public function __construct(
        public readonly int $orderId,
        public readonly int $dealId,
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
        #[Assert\NotBlank]
        public readonly string $streetAddress,
        #[Assert\NotBlank]
        public readonly string $city,
        #[Assert\NotBlank]
        public readonly string $state,
        #[Assert\NotBlank]
        public readonly string $zipCode,
        #[Assert\NotBlank]
        public readonly string $creditCard,
    ) {
    }

    /**
     * Ways to recognise the same buyer in the same deal: orders sharing an identity but not
     * the credit card are fraudulent. To add one (e.g. a national id), add a line here.
     *
     * @return list<string>
     */
    public function getIdentities(): array
    {
        return [
            "email|$this->dealId|{$this->getNormalizedEmail()}",
            "address|$this->dealId|{$this->getNormalizedAddress()}",
        ];
    }

    public function getNormalizedEmail(): string
    {
        [$user, $domain] = explode('@', strtolower(trim($this->email)), 2);
        $user = explode('+', $user)[0];

        return str_replace('.', '', $user).'@'.$domain;
    }

    public function getNormalizedAddress(): string
    {
        $street = array_map(
            static fn (string $word): string => self::STREET_WORDS[$word] ?? $word,
            explode(' ', $this->normalizeText($this->streetAddress)),
        );
        $state = $this->normalizeText($this->state);

        return implode('|', [
            implode(' ', $street),
            $this->normalizeText($this->city),
            self::STATES[$state] ?? $state,
            $this->normalizeText($this->zipCode),
        ]);
    }

    public function getNormalizedCreditCard(): string
    {
        return str_replace([' ', '-'], '', $this->creditCard);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(['.', ','], '', strtolower($text));

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
