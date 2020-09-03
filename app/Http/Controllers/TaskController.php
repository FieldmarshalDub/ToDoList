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
        $this->middleware('auth:api');
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
        $task = new Task;
        $task->name = $request->name;
        $task->user_id = $request->user()->id;
        $task->board_id = $request->board_id;
        $task->description = $request->description;
        $task->scheduled_date = $request->scheduled_date;
        $task->real_date = $request->scheduled_date;
        $task->status = $request->status;
        $task->save();
        return response()->json($task, 200);
    }

    public function copy(Request $request)
    {
        $task = Task::find($request->task_id);
        $this->authorize('action', $task);
        $new_task = $task;
        $new_task->board_id = $request->destination_id;
        $new_task->save();
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
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required | max:255',
            'scheduled_date' => 'required'
        ]);
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
