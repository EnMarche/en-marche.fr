<?php

namespace App\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient as GoogleImageAnnotatorClient;
use Google\Cloud\Vision\V1\TextAnnotation;
use Google\Cloud\Vision\V1\WebDetection;
use Google\Protobuf\Internal\RepeatedField;

class ImageAnnotatorClient
{
    private $client;

    private $textAnnotations = [];
    private $webDetections = [];

    public function __construct()
    {
        $this->client = new GoogleImageAnnotatorClient();
    }

    public function getBestGuessLabels(string $content): ?RepeatedField
    {
        $webDetection = $this->getWebDetection($content);

        return $webDetection ? $webDetection->getBestGuessLabels() : null;
    }

    public function getWebEntities(string $content): ?RepeatedField
    {
        $webDetection = $this->getWebDetection($content);

        return $webDetection ? $webDetection->getWebEntities() : null;
    }

    public function getWebDetection(string $content): ?WebDetection
    {
        $key = md5($content);

        if (!\array_key_exists($key, $this->webDetections)) {
            $response = $this->client->webDetection($content);

            $this->webDetections[$key] = $response->getWebDetection();
        }

        return $this->webDetections[$key];
    }

    public function getFullTextAnnotation(string $content): ?TextAnnotation
    {
        $key = md5($content);

        if (!\array_key_exists($key, $this->textAnnotations)) {
            $response = $this->client->documentTextDetection($content);

            $this->textAnnotations[$key] = $response->getFullTextAnnotation();
        }

        return $this->textAnnotations[$key];
    }
}
