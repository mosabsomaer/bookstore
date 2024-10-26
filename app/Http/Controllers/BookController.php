<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookController extends Controller
{
    protected function handleError(Throwable $e, $statusCode = 500)
    {
        if ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $error = 'Book not found.';
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $error = $e->errors();
        } elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $error = 'Not authorized.';
        } else {
            $error = 'An error occurred.';
        }

        return response()->json([
            'error' => $error
        ], $statusCode);
    }

    public function index(Request $request)
    {
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $books = Book::paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json($books);
    }

    public function show(string $id)
    {
        try {
            $book = Book::findOrFail($id);
            return response()->json([
                'data' => $book
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'publication_year' => 'required|integer',
                'genre' => 'sometimes|string|max:100',
                'isbn' => 'required|string|unique:books,isbn',
                'pages' => 'required|integer',
                'available' => 'required|boolean',
            ]);

            $book = Book::create($validatedData);

            return response()->json([
                'data' => $book,
                'message' => 'Book added successfully!',
            ], 201);
        } catch (Throwable $e) {
            return $this->handleError($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'sometimes|string|max:255',
                'publication_year' => 'sometimes|integer',
                'genre' => 'sometimes|string|max:100',
                'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id,
                'pages' => 'sometimes|integer',
                'available' => 'sometimes|boolean',
            ]);

            $book->update($validatedData);

            return response()->json([
                'data' => $book,
                'validatedData' => $validatedData,
                'message' => 'Book updated successfully!',
            ], 200);
        } catch (Throwable $e) {
            return $this->handleError($e);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();

            return response()->json([
                'message' => 'Book deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book not found.',
            ], 404);
        } catch (Throwable $e) {
            return $this->handleError($e);
        }
    }

    public function search(Request $request)
    {
        $query = Book::query();
    
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
    
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->input('author') . '%');
        }
    
        if ($request->filled('isbn')) {
            $query->where('isbn', 'like', '%' . $request->input('isbn') . '%');
        }
    
        $books = $query->paginate(10);
    
        if ($books->isEmpty()) {
            return response()->json([
                'message' => 'No books found matching the criteria.',
            ], 404);
        }
    
        return response()->json([
            'data' => $books,
        ], 200);
    }
    
    
    
}
