<?php

namespace App\Http\Controllers;

use App\Board;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
define("JSON",".json");
class BoardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $boards = $request->user()->isModerator() ? Board::all() : $request->user()->boards;
        return response()->json($boards, 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
        Board::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'color' => $request->color,
        ]);
        $user_boards = Board::where('user_id', $request->user()->id);

        return response()->json($user_boards, 200);
    }
    public function update(Request $request)
    {
        $board = Board::find($request->board_id);
        $this->authorize('action', $board);
        $board = $board->update($request->all());
        $response_data['board'] = $board;
        return response()->json($response_data, 200);
    }

    public function destroy(Board $board)
    {
        $this->authorize('action', $board);
        $board->tasks()->delete();
        $board->delete();
        return response(null, 204);
    }

    public function download(Request $request)
    {
        $boards = $request->user()->isModerator() ? Board::all() : $request->user()->boards;
        if (count($boards) == 0)
            return redirect()->route('boards.index');
        $zip_archive = new \ZipArchive();
        $zip_file_name = 'board.zip';
        $zip_archive->open($zip_file_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($boards as $board) {
            $board_serialized['name'] = $board->name;
            $board_serialized['user'] = $board->user->name;
            $board_serialized['tasks'] = $board->tasks;
            Storage::disk('local')->put('board' . $board->id . JSON, json_encode($board_serialized));
            $path_to_json = Storage::disk('local')->path('board' . $board->id . JSON);
            $zip_archive->addFile($path_to_json);
        }
        $zip_archive->close();
        foreach ($boards as $board) {
            Storage::disk('local')->delete('board' . $board->id . JSON);

        }
        return response()->download($zip_file_name);

    }

}
