<?php

namespace App\Services;

use App\Interfaces\SolverInterface;

class WaterJugService
{
    public function __construct(
        private SolverInterface $solver
    ) {}

    public function resolve(int $bucketX, int $bucketY, int $amountWantedZ): array
    {
        return $this->solver->resolve($bucketX, $bucketY, $amountWantedZ);
    }
}