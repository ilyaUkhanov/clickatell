<?php

namespace App\Service;

use Vich\UploaderBundle\Naming\NamerInterface;

class FileNamer implements NamerInterface
{
    public function name($object, $mapping): string
    {
        $file = $mapping->getFile($object);
        $extension = pathinfo($file->getClientOriginalName(),PATHINFO_EXTENSION);

        if (strlen($extension)<2) {
            $extension = $file->guessExtension();
        }
        return sprintf('%s.%s', uniqid(), $extension);
    }
}
