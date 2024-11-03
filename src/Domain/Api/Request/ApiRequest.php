<?php

namespace App\Domain\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ApiRequest
{
    #[Assert\NotBlank(message: 'Name is mandatory')]
    public string $name;
    #[Assert\NotBlank(message: 'E-mail is mandatory')]
    public float $email;
}
