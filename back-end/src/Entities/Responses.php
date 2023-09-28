<?php

namespace App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'responses')]
class Responses implements \JsonSerializable
{
    #[Id]
    #[Column(
        name: 'frdoc_number',
        type: 'string',
        nullable: false
    )]
    private string $frdocNumber;

    #[Id]
    #[Column(
        name: 'response_id',
        type: 'integer',
        nullable: false
    )]
    private int $responseId;

    #[Column(
        name: 'any_change',
        type: 'string',
        nullable: false
    )]
    private string $anyChange;

    #[Column(
        name: 'y_prob',
        type: 'float',
        nullable: false
    )]
    private float $yProb;

    public function __construct(
        string $frdocNumber,
        int $responseId,
        string $anyChange,
        float $yProb
    ) {
        $this->frdocNumber = $frdocNumber;
        $this->responseId = $responseId;
        $this->anyChange = $anyChange;
        $this->yProb = $yProb;
    }

    /**
     * @return string
     */
    public function getFrdocNumber(): string
    {
        return $this->frdocNumber;
    }

    /**
     * @return int
     */
    public function getResponseId(): int
    {
        return $this->responseId;
    }

    /**
     * @return string
     */
    public function getAnyChange(): string
    {
        return $this->anyChange;
    }

    /**
     * @return float
     */
    public function getYProb(): float
    {
        return $this->yProb;
    }

    public function jsonSerialize(): array
    {
        return [
            'frdocNumber' => $this->getFrdocNumber(),
            'responseId' => $this->getResponseId(),
            'anyChange' => $this->getAnyChange(),
            'yProb' => $this->getYProb()
        ];
    }
}