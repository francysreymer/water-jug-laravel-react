<?php

namespace Tests\Unit;

use App\Algorithms\WaterJugBSFSolver;
use App\Exceptions\NoSolutionException;
use Tests\TestCase;

class WaterJugSolverTest extends TestCase
{
    private WaterJugBSFSolver $solver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->solver = new WaterJugBSFSolver();
    }

    public function testSolveWithValidSolution()
    {
        $result = $this->solver->resolve(3, 5, 4);

        $expected = [
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
                    'step' => 2
                ],
                [
                    'bucketX' => 0,
                    'bucketY' => 2,
                    'action' => 'Empty bucket X',
                    'step' => 3
                ],
                [
                    'bucketX' => 2,
                    'bucketY' => 0,
                    'action' => 'Transfer from bucket Y to X',
                    'step' => 4
                ],
                [
                    'bucketX' => 2,
                    'bucketY' => 5,
                    'action' => 'Fill bucket Y',
                    'step' => 5
                ],
                [
                    'bucketX' => 3,
                    'bucketY' => 4,
                    'action' => 'Transfer from bucket Y to X',
                    'status' => 'Solved',
                    'step' => 6
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

        $this->assertEquals($expected, $result);
    }

    public function testSolveWithExactSolution()
    {
        $result = $this->solver->resolve(3, 5, 3);

        $expected = [
            'best_solution' => [
                ['bucketX' => 3, 'bucketY' => 0, 'action' => 'Fill bucket X', 'step' => 1, 'status' => 'Solved']
            ],
            'worst_solution' => [
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
            ]
        ];
    
        $this->assertEquals($expected, $result);
    }

    public function testSolveWithNoSolution()
    {
        $this->expectException(NoSolutionException::class);
        $this->solver->resolve(2, 6, 5);
    }

    public function testSolveWithImpossibleAmount()
    {
        $this->expectException(NoSolutionException::class);
        $this->solver->resolve(3, 5, 8);
    }

    public function testSolveWithZeroAmount()
    {
        $this->expectException(NoSolutionException::class);
        $this->solver->resolve(3, 5, 0);
    }

    public function testGcd()
    {
        $reflection = new \ReflectionClass($this->solver);
        $method = $reflection->getMethod('gcd');
        $method->setAccessible(true);

        $this->assertEquals(1, $method->invokeArgs($this->solver, [3, 5]));
        $this->assertEquals(2, $method->invokeArgs($this->solver, [2, 4]));
        $this->assertEquals(3, $method->invokeArgs($this->solver, [3, 9]));
    }

    public function testTransfer()
    {
        $reflection = new \ReflectionClass($this->solver);
        $method = $reflection->getMethod('transfer');
        $method->setAccessible(true);

        $this->assertEquals([0, 3], $method->invokeArgs($this->solver, [3, 0, 3]));
        $this->assertEquals([1, 5], $method->invokeArgs($this->solver, [6, 0, 5]));
        $this->assertEquals([2, 3], $method->invokeArgs($this->solver, [5, 0, 3]));
    }
}