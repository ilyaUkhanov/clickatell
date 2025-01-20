<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ServiceCSV
{
    public function getColumns(string $data): array
    {
        $cleanData = $this->cleanupCSV($data);
        $firstLine = preg_split('#\r?\n#', $cleanData, 2)[0];
        return array_filter(explode($this->detectDelimiter($cleanData), $firstLine));
    }
    public function detectDelimiter(string $data): string
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];
        $firstLine = preg_split('#\r?\n#', $data, 2)[0];

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    public function cleanupCSV(string $data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        // Remove empty rows at the beginning and the end of CSV files
        $data = preg_replace("/^(?:[\t,;]*(?:\r?\n|\r))+/", "", $data);

        return $data;
    }

    public function decodeCSV(File $file)
    {
        $data = $file->getContent();
        $cleanData = $this->cleanupCSV($data);
        $encoder = new CsvEncoder([
            CsvEncoder::DELIMITER_KEY => $this->detectDelimiter($file),
        ]);
        $serializer = new Serializer([new ObjectNormalizer()], [$encoder]);
        $decodedData = $serializer->decode($cleanData, 'csv');

        return $decodedData;
    }
}
