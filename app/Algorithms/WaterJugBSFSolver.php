<?php

namespace App\Algorithms;

use App\Exceptions\NoSolutionException;
use App\Interfaces\SolverInterface;

/**
 * This class implements the Breadth-First Search (BSF) algorithm to solve the Water Jug problem.
 * The Water Jug problem involves two jugs of water with capacities X and Y liters. The goal is to measure Z liters of water using these jugs.
 * The algorithm explores all possible states of the jugs and their actions (Fill, Empty, Transfer) to find a solution.
 */
class WaterJugBSFSolver implements SolverInterface
{
    private array $queue = [];
    private array $solutions = [];

    // Define constants for actions
    private const ACTION_FILL_BUCKET_X = 'Fill bucket X';
    private const ACTION_FILL_BUCKET_Y = 'Fill bucket Y';
    private const ACTION_EMPTY_BUCKET_X = 'Empty bucket X';
    private const ACTION_EMPTY_BUCKET_Y = 'Empty bucket Y';
    private const ACTION_TRANSFER_X_TO_Y = 'Transfer from bucket X to Y';
    private const ACTION_TRANSFER_Y_TO_X = 'Transfer from bucket Y to X';
    private const STATUS_SOLVED = 'Solved';
    
    /**
     * Solve the Water Jug problem using Breadth-First Search (BSF)
     * @return array
     * @throws NoSolutionException
     */
    public function resolve(int $bucketX, int $bucketY, int $amountWantedZ): array
    {
        // Edge case: If the target amount Z is larger than both buckets
        if ($amountWantedZ > max($bucketX, $bucketY)) {
            throw new NoSolutionException();
        }

        // Check if it is not possible to solve
        if ($amountWantedZ % $this->gcd($bucketX, $bucketY) !== 0) {
            throw new NoSolutionException();
        }

        $visited = [];
        $this->queue[] = [0, 0, []]; // [bucketX, bucketY, steps]

        while (!empty($this->queue)) {
            [$currentX, $currentY, $steps] = array_shift($this->queue);

            // If we have reached the target amount in either bucket
            if ($currentX == $amountWantedZ || $currentY == $amountWantedZ) {
                // Modify the last action to include the status "Solved"
                if (!empty($steps)) {
                    $steps[count($steps) - 1]['status'] = self::STATUS_SOLVED;
                
                    // Add step count for each line
                    $steps = array_map(function ($step, $index) {
                        $step['step'] = $index + 1;
                        return $step;
                    }, $steps, array_keys($steps));
                
                    $this->solutions[] = $steps;
                }
                continue;           
            }

            // Mark the state as visited
            if (isset($visited[$currentX][$currentY])) {
                continue;
            }
            $visited[$currentX][$currentY] = true;

            // Possible actions: Fill, Empty, Transfer
            $this->enqueueAction($currentX, $bucketY, $steps, self::ACTION_FILL_BUCKET_Y);
            $this->enqueueAction($bucketX, $currentY, $steps, self::ACTION_FILL_BUCKET_X);
            $this->enqueueAction(0, $currentY, $steps, self::ACTION_EMPTY_BUCKET_X);
            $this->enqueueAction($currentX, 0, $steps, self::ACTION_EMPTY_BUCKET_Y);

            // Transfer actions (X -> Y and Y -> X)
            list($newX, $newY) = $this->transfer($currentX, $currentY, $bucketY);
            $this->enqueueAction($newX, $newY, $steps, self::ACTION_TRANSFER_X_TO_Y);

            list($newY, $newX) = $this->transfer($currentY, $currentX, $bucketX);
            $this->enqueueAction($newX, $newY, $steps, self::ACTION_TRANSFER_Y_TO_X);
        }

        //throw new NoSolutionException();
        if (empty($this->solutions)) {
            throw new NoSolutionException();
        }

        // Find the best and worst solutions
        usort($this->solutions, function ($a, $b) {
            return count($a) - count($b);
        });

        $bestSolution = $this->solutions[0];

        $indexWorstCase = count($this->solutions) - 1;
        $worstSolution = $indexWorstCase > 0 ? $this->solutions[$indexWorstCase] : [];

        return [
            'best_solution' => $bestSolution,
            'worst_solution' => $worstSolution,
        ];
    }

    /**
     * Enqueue a new action with the updated state
     * @param int $newX
     * @param int $newY
     * @param array $steps
     * @param string $action
     * @return void
     */
    private function enqueueAction(int $newX, int $newY, array $steps, string $action): void
    {
        $newSteps = $steps;
        $newSteps[] = ['bucketX' => $newX, 'bucketY' => $newY, 'action' => $action];
        $this->queue[] = [$newX, $newY, $newSteps];
    }

    /**
     * Transfer water from one bucket to another
     * @param int $from
     * @param int $to
     * @param int $toCapacity
     * @return array
     */
    private function transfer(int $from, int $to, int $toCapacity): array
    {
        $transferAmount = min($from, $toCapacity - $to);
        return [$from - $transferAmount, $to + $transferAmount];
    }

    /**
     * Calculate the greatest common divisor (GCD) of two numbers
     * @param int $a
     * @param int $b
     * @return int
     */
    private function gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }

        return $a;
    }
}

