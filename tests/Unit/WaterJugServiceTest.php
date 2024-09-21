<?php

namespace Tests\Unit;

use App\Services\WaterJugService;
use App\Interfaces\SolverInterface;
use PHPUnit\Framework\TestCase;

class WaterJugServiceTest extends TestCase
{
    public function testResolve()
    {
        // Arrange
        $bucketX = 3;
        $bucketY = 5;
        $amountWantedZ = 4;

        $expectedResult = [
            'best_solution' => [
                [
                    'bucketX' => 0,
                    'bucketY' => 5,
                    'action' => 'Fill bucket Y',
                    'step' => 1
                ],
                [
                    'bucketX' => 3,
                    'bucketY' => 2,
                    'action' => 'Transfer from bucket Y to X',
                    'status' => 'Solved',
                    'step' => 2
                ]
            ],
            'worst_solution' => [
                [
                    'bucketX' => 3,
                    'bucketY' => 0,
                    'action' => 'Fill bucket X',
                    'step' => 1
                ],
                [
                    'bucketX' => 0,
                    'bucketY' => 3,
                    'action' => 'Transfer from bucket X to Y',
                    'step' => 2
                ],
                [
                    'bucketX' => 3,
                    'bucketY' => 3,
                    'action' => 'Fill bucket X',
                    'step' => 3
                ],
                [
                    'bucketX' => 1,
                    'bucketY' => 5,
                    'action' => 'Transfer from bucket X to Y',
                    'step' => 4
                ],
                [
                    'bucketX' => 1,
                    'bucketY' => 0,
                    'action' => 'Empty bucket Y',
                    'step' => 5
                ],
                [
                    'bucketX' => 0,
                    'bucketY' => 1,
                    'action' => 'Transfer from bucket X to Y',
                    'step' => 6
                ],
                [
                    'bucketX' => 3,
                    'bucketY' => 1,
                    'action' => 'Fill bucket X',
                    'step' => 7
                ],
                [
                    'bucketX' => 0,
                    'bucketY' => 4,
                    'action' => 'Transfer from bucket X to Y',
                    'status' => 'Solved',
                    'step' => 8
                ]
            ]
        ];

        $solverMock = $this->createMock(SolverInterface::class);
        $solverMock->expects($this->once())
                   ->method('resolve')
                   ->with($bucketX, $bucketY, $amountWantedZ)
                   ->willReturn($expectedResult);

        $service = new WaterJugService($solverMock);

        // Act
        $result = $service->resolve($bucketX, $bucketY, $amountWantedZ);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}