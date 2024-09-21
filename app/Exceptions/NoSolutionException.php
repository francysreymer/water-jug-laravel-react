<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class NoSolutionException extends ValidationException
{
    public function __construct($message = "No Solution")
    {
        // Create a Validator instance
        $validator = Validator::make([], []);
        
        // Add the custom error message to the Validator
        $validator->errors()->add('payload', $message);

        // Pass the Validator instance to the parent ValidationException constructor
        parent::__construct($validator);
    }
}