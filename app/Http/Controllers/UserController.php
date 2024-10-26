<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Str;


class UserController extends Controller
{
    protected function handleError(Throwable $e, $statusCode = 500)
    {
        if ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $error = 'User not found.';
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

    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $input = $request->validate([
                'email' => ['required', 'unique:users,email', 'email'],
                'password' => ['required', 'min:8'],
                'name' => ['required', 'string']
            ]);

            $user = User::create($input);

            $token = $user->createToken("API TOKEN");

            if (!$token) {
                throw new \Exception('Failed to create API token.');
            }

            return response()->json([
                'data' => 'created',
                'token' => $token->plainTextToken
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => $e,

            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required',
                    'password' => 'required'
                ]
            );
            if ($validateUser->fails()) {
                $errors = '';
                foreach ($validateUser->errors()->all() as $error) {
                    $errors .= $error . ' ';
                }
                return response()->json([
                    'status' => false,
                    'message' =>  $errors
                ], 401);
            }

            if (!Auth::guard('user')->attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logoutUser(Request $request)
{
    try {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !Str::startsWith($authorizationHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token not provided or invalid.',
            ], 400);
        }

        $token = Str::after($authorizationHeader, 'Bearer ');
        $tokenParts = explode('|', $token);

        if (count($tokenParts) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format.',
            ], 400);
        }

        $plainToken = $tokenParts[1];
        $hashedToken = hash('sha256', $plainToken);
        $deletedRows = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->delete();

        if ($deletedRows > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Token not found or already invalidated.',
            ], 401);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while logging out.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function show(Request $request,string $id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $input = $request->validate([
                'email' => [Rule::unique('users', 'email')->ignore($user), 'email'],
                'password' => ['min:8']
            ]);

            $user->update($input);

            return response()->json([
                'data' => 'updated',
                'input' => $input,
                'request' => $request->request
            ], 200);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function destroy(string $id, Request $request)
    {
        try {
    
            $user = User::findOrFail($id);
    
            // Prevent the admin from deleting themselves
            if ($authUser->id == $id) {
                return response()->json([
                    'error' => 'You cannot delete yourself.',
                ], 400);
            }
    
            $user->delete();
    
            return response()->json([
                'data' => 'User Deleted',
            ]);
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }   
};