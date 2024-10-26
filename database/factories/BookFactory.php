<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => substr($this->faker->sentence(3), 0, -1),       
            'author' => $this->faker->name(),                  
            'publication_year' => $this->faker->year(),         
            'genre' => $this->faker->word(),                    
            'isbn' => $this->faker->unique()->isbn13(),         
            'pages' => $this->faker->numberBetween(100, 1000),  
            'available' => $this->faker->boolean(),             
        ];
    }
}
