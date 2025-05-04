<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $todos = $user->todos()
                ->orderBy('order')
                ->get();

            return response()->json([
                'status' => 'success',
                'todos' => $todos,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve todos: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve todos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // not needed for API now
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_completed' => 'boolean',
            ]);

            $data['user_id'] = auth()->id();
            $data['order'] = Todo::where('user_id', auth()->id())->max('order') + 1;

            $todo = Todo::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Todo created successfully.',
                'todo' => $todo,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create Todo: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create Todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        try {
            $this->authorizeUser($todo);

            return response()->json([
                'status' => 'success',
                'todo' => $todo,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Todo: ' . $e->getMessage(), [
                'exception' => $e,
                'todo_id' => $todo->id,
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve Todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Todo $todo)
    {
        // not needed for API now
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        try {
            $this->authorizeUser($todo);

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'is_completed' => 'boolean',
            ]);

            $todo->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Todo updated successfully.',
                'todo' => $todo,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update Todo: ' . $e->getMessage(), [
                'exception' => $e,
                'todo_id' => $todo->id,
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update Todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        try {
            $this->authorizeUser($todo);

            $todo->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Todo deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete Todo: ' . $e->getMessage(), [
                'exception' => $e,
                'todo_id' => $todo->id,
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete Todo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function authorizeUser(Todo $todo)
    {
        abort_if($todo->user_id !== auth()->id(), 403, 'Unauthorized');
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'todos' => 'required|array',
                'todos.*.id' => 'required|exists:todos,id',
                'todos.*.order' => 'required|integer',
            ]);

            foreach ($request->todos as $todoData) {

                $todo = Todo::find($todoData['id']);

                if ($todo && $todo->user_id === auth()->id()) {
                    $todo->update(['order' => $todoData['order']]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Todo not found or unauthorized.',
                    ], 403);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Order updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reorder Todos: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->todos,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
