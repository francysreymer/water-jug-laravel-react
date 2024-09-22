<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Mockery;
use App\Http\Controllers\WaterJugController;
use App\Exceptions\NoSolutionException;
use Mockery\MockInterface;
use App\Services\WaterJugService;
use Illuminate\Support\Facades\Cache;

class WaterJugControllerTest extends TestCase
{
    // Define a constant for cache duration (1 hour)
    private const CACHE_DURATION_SECONDS = 3600;
    private const API_ENDPOINT = '/api/water-jugs';
    private array $mockedSolution = [
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

    #[DataProvider('invalidPayloadProvider')]
    public function testInvalidPayload(array $payload)
    {
        $response = $this->postJson(self::API_ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'errors' => [
                '*' => []
            ]
        ]);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'missing amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 5]],
            'missing bucket_x' => [['bucket_y' => 5, 'amount_wanted_z' => 4]],
            'missing bucket_y' => [['bucket_x' => 3, 'amount_wanted_z' => 4]],
            'bucket_x equals amount_wanted_z' => [['bucket_x' => 4, 'bucket_y' => 5, 'amount_wanted_z' => 4]],
            'bucket_y equals amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 4, 'amount_wanted_z' => 4]],
            'non-integer bucket_x' => [['bucket_x' => 'three', 'bucket_y' => 5, 'amount_wanted_z' => 4]],
            'non-integer bucket_y' => [['bucket_x' => 3, 'bucket_y' => 'five', 'amount_wanted_z' => 4]],
            'non-integer amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 5, 'amount_wanted_z' => 'four']],
            'negative bucket_x' => [['bucket_x' => -3, 'bucket_y' => 5, 'amount_wanted_z' => 4]],
            'negative bucket_y' => [['bucket_x' => 3, 'bucket_y' => -5, 'amount_wanted_z' => 4]],
            'negative amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 5, 'amount_wanted_z' => -4]],
            'zero bucket_x' => [['bucket_x' => 0, 'bucket_y' => 5, 'amount_wanted_z' => 4]],
            'zero bucket_y' => [['bucket_x' => 3, 'bucket_y' => 0, 'amount_wanted_z' => 4]],
            'zero amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 5, 'amount_wanted_z' => 0]],
            'float bucket_x' => [['bucket_x' => 3.5, 'bucket_y' => 5, 'amount_wanted_z' => 4]],
            'float bucket_y' => [['bucket_x' => 3, 'bucket_y' => 5.5, 'amount_wanted_z' => 4]],
            'float amount_wanted_z' => [['bucket_x' => 3, 'bucket_y' => 5, 'amount_wanted_z' => 4.5]],
        ];
    }

    public function testNoPossibleSolutionException()
    {
        $this->instance(
            WaterJugController::class,
            Mockery::mock(WaterJugController::class, function (MockInterface $mock) {
                $mock->shouldReceive('resolve')->once()->andThrow(new NoSolutionException());
            })
        );

        $response = $this->postJson(self::API_ENDPOINT, [
            'bucket_x' => 2,
            'bucket_y' => 6,
            'amount_wanted_z' => 5
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'errors' => [
                'payload' => [
                    'No Solution'
                ]
            ]
        ]);    
    }

    public function testResolveWithValidPayload()
    {
        // Mock the WaterJugService
        $mockedSolution = $this->mockedSolution;
        $this->mock(WaterJugService::class, function (MockInterface $mock) use ($mockedSolution) {
            $mock->shouldReceive('resolve')
                ->once()
                ->with(3, 5, 4)
                ->andReturn($mockedSolution);
        });

        // Define the payload
        $payload = [
            'bucket_x' => 3,
            'bucket_y' => 5,
            'amount_wanted_z' => 4
        ];

        // Send a POST request to the endpoint
        $response = $this->postJson(self::API_ENDPOINT, $payload);

        // Assert the response status and structure
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'solutions' => [
                    'best_solution' => [
                        '*' => [
                            'bucketX',
                            'bucketY',
                            'action',
                            'step',
                        ]
                    ],
                    'worst_solution' => [
                        '*' => [
                            'bucketX',
                            'bucketY',
                            'action',
                            'step',
                        ]
                    ]
                ]
            ]);

        // Assert the last step contains the status key
        $response->assertJsonFragment([
            'bucketX' => 3,
            'bucketY' => 4,
            'action' => 'Transfer from bucket Y to X',
            'step' => 6,
            'status' => 'Solved'
        ]);

        // Assert the rest of the steps do not contain the status key
        foreach ($response->json('solutions.best_solution') as $step) {
            if ($step['step'] !== count($this->mockedSolution['best_solution'])) {
                $this->assertArrayNotHasKey('status', $step);
            }
        }

        // Assert the entire solution array
        $response->assertExactJson([
            'solutions' => $this->mockedSolution
        ]);
    }

    public function testResolveWithValidPayloadWithCache()
    {
        // Define the payload
        $payload = [
            'bucket_x' => 3,
            'bucket_y' => 5,
            'amount_wanted_z' => 4
        ];

        // Generate the cache key
        $cacheKey = sprintf("water_jug_solution_%d_%d_%d", $payload['bucket_x'], $payload['bucket_y'], $payload['amount_wanted_z']);

        // Mock the Cache facade
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, self::CACHE_DURATION_SECONDS, Mockery::on(function ($callback) {
                return is_callable($callback);
            }))
            ->andReturn($this->mockedSolution);

        // Send a POST request to the endpoint
        $response = $this->postJson(self::API_ENDPOINT, $payload);

        // Assert the response status and structure
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'solutions' => $this->mockedSolution
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
