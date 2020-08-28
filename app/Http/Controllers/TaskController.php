<?php

namespace App\Http\Controllers;

use App\Board;
use App\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Создание нового экземпляра контроллера.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $board = Board::find($request->board_id);
        $tasks = $board->tasks;
        if (!policy(Task::class)->index($request->user(), $board)) {
            abort(403);
        }
        return response()->json($tasks, 200);
    }

    public function store(Request $request)
    {
        $board = Board::find($request->board_id);
        if (!policy(Task::class)->create($request->user(), $board)) {
            abort(403);
        }
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required | max:255',
            'scheduled_date' => 'required'
        ]);
        $board->user->tasks()->create([
            'name' => $request->name,
            'board_id' => $request->board_id,
            'description' => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'real_date' => $request->scheduled_date,
            'status' => $request->status,
        ]);
        $task = Task::where('name', $request->name);
        return response()->json($task, 200);
    }

    public function copy(Request $request)
    {
        $task = Task::find($request->task_id);
        $this->authorize('action', $task);
        $request->user()->tasks()->create([
            'name' => $task->name,
            'board_id' => $request->to_board_id,
            'description' => $task->description,
            'scheduled_date' => $task->scheduled_date,
            'real_date' => $task->scheduled_date,
            'status' => $task->status,
        ]);
        return response(null, 200);
    }

    public function move(Request $request)
    {
        $task = Task::find($request->task_id);
        $this->authorize('action', $task);
        $task->board_id = $request->destination_id;
        $task->save();
        return response(null, 200);
    }

    public function update(Request $request)
    {
        $task = Task::find($request->task_id);
        $this->authorize('action', $task);
        $task->update([
            'name' => $request->name,
            'description' => $request->description,
            'scheduled_date' => $request->scheduled_date,
        ]);
        if ($request->status == 'performed') {
            $task->update([
                'status' => 'performed',
                'real_date' => date('Y-m-d')
            ]);
        }
        return response()->json($task, 200);
    }

    public function destroy(Request $request)
    {
        $task = Task::find($request->task_id);
        $this->authorize('action', $task);
        $task->delete();
        return response(null, 204);
    }
}
