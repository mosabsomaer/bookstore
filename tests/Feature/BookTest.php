<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\UserSeeder;
class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
    

        $response = $this->postJson('/api/auth/login', [
            'email' => $adminEmail,
            'password' => $adminPassword,
        ]);

        $token = $response->json('token');
        $this->adminToken = $token;

    }
    
    protected function adminHeaders()
    {
        return ['Authorization' => 'Bearer ' . $this->adminToken];
    }

    public function testIndexReturnsPaginatedBooks()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Book::factory()->count(15)->create(); 
    
        $response = $this->getJson('/api/books?page=1');
    
        $response->assertStatus(200)
        ->assertJsonStructure(['data' => [], 'links' => []]) 
        ->assertJsonCount(10, 'data');

    }
    
    public function testShowReturnsSingleBook()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create();
    
        $response = $this->getJson('/api/books/' . $book->id);
    
        $response->assertStatus(200)
                 ->assertJson(['data' => $book->toArray()]);
    }
    
    public function testShowReturnsNotFoundForInvalidId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/books/99987799');
    
        $response->assertStatus(404)
                 ->assertJson(['error' => 'Book not found.']);
    }
    
    public function testStoreCreatesNewBook()
    {
        $data = [
            'title' => 'New Book',
            'author' => 'Mosab Omaer',
            'publication_year' => 1970,
            'genre' => 'Fiction',
            'isbn' => '1234567890123',
            'pages' => 300,
            'available' => true,
        ];
    
        $response = $this->postJson('/api/books', $data, $this->adminHeaders());
    
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Book added successfully!'])
                 ->assertJsonStructure(['data' => ['id', 'title', 'author']]);
    
        $this->assertDatabaseHas('books', ['title' => 'New Book']);
    }
    
    public function testUpdateModifiesExistingBook()
    {
        $book = Book::factory()->create();
    
        $data = [
            'title' => 'Updated Book',
        ];
    
        $response = $this->putJson('/api/books/' . $book->id, $data, $this->adminHeaders());
    
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Book updated successfully!'])
                 ->assertJson(['data' => ['id' => $book->id, 'title' => 'Updated Book']]);
    
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Book']);
    }
    
    public function testDestroyDeletesBook()
    {
        $book = Book::factory()->create();
    
        $response = $this->deleteJson('/api/books/' . $book->id, [], $this->adminHeaders());
    
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Book deleted successfully!']);
    
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
    
    
    public function testSearchReturnsMatchingBooks()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Book::factory()->create(['title' => 'Unique Great Book']);
        Book::factory()->create(['title' => 'Another Book']);
        Book::factory()->create(['title' => 'Non-Matching Book']);
        
        $response = $this->getJson('/api/books/search?title=Unique');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data');
    }
    
    
    
    public function testSearchReturnsNotFoundForNoMatches()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/books/search?title=Nonexistent');
    
        $response->assertStatus(404)
                 ->assertJson(['message' => 'No books found matching the criteria.']);
    }
    
}

