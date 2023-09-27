<?php

namespace App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'org_responses')]
class OrgResponses
{
    #[Id]
    #[Column(
        name: 'org_name',
        type: 'string',
        nullable: false
    )]
    private string $orgName;

    #[Column(
        name: 'frdoc_number',
        type: 'string',
        nullable: false
    )]
    private string $frdocNumber;

    #[Column(
        name: 'response_id',
        type: 'integer',
        nullable: false
    )]
    private int $responseId;

    #[Column(
        name: 'score',
        type: 'float',
        nullable: false
    )]
    private float $score;

    #[Column(
        name: 'norm_score',
        type: 'float',
        nullable: false
    )]
    private float $normScore;

    public function __construct(
        $orgName,
        $frdocNumber,
        $responseId,
        $score,
        $normScore
    ){
        $this->orgName = $orgName;
        $this->frdocNumber = $frdocNumber;
        $this->responseId = $responseId;
        $this->score = $score;
        $this->normScore = $normScore;
    }

    /**
     * @return string
     */
    public function getOrgName(): string
    {
        return $this->orgName;
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
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return float
     */
    public function getNormScore(): float
    {
        return $this->normScore;
    }

}