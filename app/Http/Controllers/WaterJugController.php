<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaterJugService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Info(
 *     title="Water Jug API",
 *     version="1.0.0",
 *     description="API for solving the Water Jug problem"
 * )
 */
class WaterJugController extends Controller
{
    // Define a constant for cache duration (1 hour)
    private const CACHE_DURATION_SECONDS = 3600;

    public function __construct(private WaterJugService $waterJugService)
    {
    }

    /**
    * @OA\Post(
    *     path="/api/water-jugs",
    *     summary="Solve Water Jug Problem",
    *     tags={"Water Jug"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"bucket_x", "bucket_y", "amount_wanted_z"},
    *             @OA\Property(property="bucket_x", type="integer", example=2),
    *             @OA\Property(property="bucket_y", type="integer", example=10),
    *             @OA\Property(property="amount_wanted_z", type="integer", example=3434),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Solution Found",
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="solutions",
    *                 type="object",
    *                 @OA\Property(
    *                     property="best_solution",
    *                     type="array",
    *                     @OA\Items(
    *                         type="object",
    *                         @OA\Property(property="bucketX", type="integer", example=0),
    *                         @OA\Property(property="bucketY", type="integer", example=5),
    *                         @OA\Property(
    *                             property="action",
    *                             type="string",
    *                             example="Fill bucket Y",
    *                             description="Possible values: 'Fill bucket X', 'Fill bucket Y', 'Empty bucket X', 'Empty bucket Y', 'Transfer from bucket X to Y', 'Transfer from bucket Y to X'"
    *                         ),
    *                         @OA\Property(property="step", type="integer", example=1),
    *                         @OA\Property(property="status", type="string", example="Solved", nullable=true)
    *                     )
    *                 ),
    *                 @OA\Property(
    *                     property="worst_solution",
    *                     type="array",
    *                     @OA\Items(
    *                         type="object",
    *                         @OA\Property(property="bucketX", type="integer", example=3),
    *                         @OA\Property(property="bucketY", type="integer", example=0),
    *                         @OA\Property(
    *                             property="action",
    *                             type="string",
    *                             example="Fill bucket X",
    *                             description="Possible values: 'Fill bucket X', 'Fill bucket Y', 'Empty bucket X', 'Empty bucket Y', 'Transfer from bucket X to Y', 'Transfer from bucket Y to X'"
    *                         ),
    *                         @OA\Property(property="step", type="integer", example=1),
    *                         @OA\Property(property="status", type="string", example="Solved", nullable=true)
    *                     )
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="Validation Error or No Solution",
    *         @OA\JsonContent(
    *             oneOf={
    *                 @OA\Schema(
    *                     @OA\Property(property="errors", type="object")
    *                 )
    *             }
    *         )
    *     )
    * )
    */
    public function resolve(Request $request)
    {
        try {            
            $validatedData = $request->validate([
                'bucket_x' => [
                    'required',
                    'integer',
                    'min:1',
                    'bail',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value == $request->input('amount_wanted_z')) {
                            $fail('The ' . str_replace('_', ' ', $attribute) . ' must be different from amount wanted z.');
                        }
                    },
                ],
                'bucket_y' => [
                    'required',
                    'integer',
                    'min:1',
                    'bail',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value == $request->input('amount_wanted_z')) {
                            $fail('The ' . str_replace('_', ' ', $attribute) . ' must be different from amount wanted z.');
                        }
                    },
                ],
                'amount_wanted_z' => 'required|integer|min:1',
            ]);

            $bucketX = $validatedData['bucket_x'];
            $bucketY = $validatedData['bucket_y'];
            $amountWantedZ = $validatedData['amount_wanted_z'];

            // Generate a unique cache key
            $cacheKey = sprintf("water_jug_solution_%d_%d_%d", $bucketX, $bucketY, $amountWantedZ);

            // Check if the solution is already cached
            $solutions = Cache::remember($cacheKey, self::CACHE_DURATION_SECONDS, function () use ($bucketX, $bucketY, $amountWantedZ) {
                return $this->waterJugService->resolve($bucketX, $bucketY, $amountWantedZ);
            });
        
            return response()->json(['solutions' => $solutions]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], $e->status);
        }
    }
}