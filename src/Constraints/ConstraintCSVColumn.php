<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ConstraintCSVColumn extends Constraint
{
    public string $message = "The CSV file must contain a column named '{{ string }}'";
    public string $column;

    // all configurable options must be passed to the constructor
    public function __construct(?string $column,  ?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->message = $message ?? $this->message;
        $this->column = $column;
    }
}
