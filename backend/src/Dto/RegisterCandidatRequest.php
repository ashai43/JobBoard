<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterCandidatRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 100)]
        #[Assert\NoSuspiciousCharacters]
        #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\- ]+$/', normalizer: 'trim')]
        public string $nom,
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 100)]
        #[Assert\NoSuspiciousCharacters]
        #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\- ]+$/', normalizer: 'trim')]
        public string $prenom,
        #[Assert\NotBlank]
        #[Assert\Email(normalizer: 'trim')]
        #[Assert\Length(max: 255)]
        #[Assert\NoSuspiciousCharacters]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $password,
    ) {
    }
}
