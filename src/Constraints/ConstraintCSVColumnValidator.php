<?php

namespace App\Constraints;

use App\Service\ServiceCSV;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ConstraintCSVColumnValidator extends ConstraintValidator
{
    public ServiceCSV $serviceCSV;

    public function __construct(ServiceCSV $serviceCSV)
    {
        $this->serviceCSV = $serviceCSV;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ConstraintCSVColumn) {
            throw new UnexpectedTypeException($constraint, ConstraintCSVColumn::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof File) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, File::class);
        }

        // Check that the CSV file contains the column we are looking for
        $columns = $this->serviceCSV->getColumns($value->getContent());
        if(in_array($constraint->column, $columns)) {
           return;
        }

        // the argument must be a string or an object implementing __toString()
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $constraint->column)
            ->addViolation();
    }
}
