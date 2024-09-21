<?php

namespace App\Interfaces;

interface SolverInterface
{
    public function resolve(int $bucketX, int $bucketY, int $amountWantedZ): array;
}