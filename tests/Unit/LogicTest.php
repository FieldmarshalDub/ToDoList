<?php

namespace Tests\Unit;

use App\Board;
use App\User;
use App\Task;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class LogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testBoardIndex()
    {
        $user = $this->createUser();
        $board = $this->createBoard($user->id);
        $this->be($user);
        $response = $this->getJson(route('boards.index'));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $board->user_id,
                'name' => $board->name,
                'color' => $board->color,
                'created_at' => $board->created_at,
                'updated_at' => $board->updated_at,
                'id' => $board->id
            ]);


    }

    public function testBoardUpdate()
    {
        $user = $this->createUser();
        $board = $this->createBoard($user->id);
        $this->be($user);
        $response = $this->post(route('boards.update',
            [
                'name' => 'newName',
                $board->id
            ]
        ));
        $boards_info['board'] = $board;
        $this->assertDatabaseHas(
            'boards',
            [
                'name' => 'newName'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson($boards_info);
    }

    public function testBoardDelete()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $this->be($user1);
        $response = $this->delete(route('boards.destroy', [$board1]));
        $response->assertStatus(204);
        $this->assertDatabaseMissing('boards', [
            'name' => $board1->name,
            'user_id' => $user1->id
        ]);
    }

    public function testBoardStore()
    {
        $user1 = $this->createUser();
        $this->be($user1);
        $this->post(route('boards.store', ['name' => 'board_name', 'color' => '#0000FF']));
        $this->assertDatabaseHas('boards', [
            'name' => 'board_name',
            'user_id' => $user1->id,
            'color' => '#0000FF'
        ]);
    }

    public function testTasksIndex()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id, $board1->id);
        $this->be($user1);
        $response = $this->getJson(route('boards.tasks.index', [$board1->id]));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $task1->id,
                'user_id' => $task1->user_id,
                'board_id' => $task1->board_id,
                'name' => $task1->name,
                'status' => $task1->status,
                'description' => $task1->description,
                'scheduled_date' => $task1->scheduled_date,
                'real_date' => $task1->real_date,
                'created_at' => $task1->created_at,
                'updated_at' => $task1->updated_at
            ]);
    }


    public function testTaskStore()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);

        $this->be($user1);
        $response = $this->post(
            route('boards.tasks.store',
                [
                    $board1->id,
                    $user1->id,
                    'name' => 'nm',
                    'description' => 'description',
                    'real_date' => '10.10.20',
                    'scheduled_date' => '10.10.20',
                    'status' => 'in progress'
                ]));

        $this->assertDatabaseHas(
            'tasks',
            [
                'name' => 'nm',
                'user_id' => $user1->id,
                'board_id' => $board1->id,
                'description' => 'description',
                'real_date' => '10.10.20',
                'scheduled_date' => '10.10.20',
                'status' => 'in progress'
            ]
        );
    }

    public function testTaskDelete()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id, $board1->id);

        $this->be($user1);
        $task_id = $task1->id;
        $response = $this->delete(route('boards.tasks.destroy', [$board1->id, $task1->id]));
        $this->assertDatabaseMissing('tasks', ['id' => $task_id]);
    }

    public function testTaskUpdate()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id, $board1->id);
        $this->be($user1);
        $response = $this->post(route('boards.tasks.update', [$board1->id, $task1->id,
            'name' => 'newTaskName',
            'description' => 'newDescription',
            'status' => 'performed',
            'scheduled_date' => '2020-02-10'
        ]));
        $this->assertDatabaseHas('tasks', [
            'name' => 'newTaskName',
            'description' => 'newDescription',
            'status' => 'performed',
            'real_date' => date('Y-m-d'),
            'scheduled_date' => '2020-02-10'
        ]);
    }

    public function testMoveTask()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user1->id);
        $task2 = $this->createTask($user1->id, $board2->id);

        $this->be($user1);
        $response = $this->post(route('boards.tasks.move', ['board_id' => $board2->id, 'task_id' => $task2->id, 'destination_id' => $board1->id]));
        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['name' => $task2->name, 'board_id' => $board2->id]);
    }

    public function testCopyTask()
    {
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user1->id);
        $task2 = $this->createTask($user1->id, $board2->id);

        $this->be($user1);
        $response = $this->post(route('boards.tasks.copy', ['board_id' => $board2->id, 'task_id' => $task2->id, 'to_board_id' => $board1->id]));
        $response->assertOk();
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user1->id,
            'board_id' => $board2->id,
            'name' => $task2->name,
        ]);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user1->id,
            'board_id' => $board1->id,
            'name' => $task2->name,
        ]);
    }


    public function testDownloadBoards()
    {
        $user1 = $this->createUser();
        $board1 = $this->createboard($user1->id);
        $zip_archive = new \ZipArchive();
        $zip_file_name = 'boards.zip';
        $zip_archive->open($zip_file_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $board_serialized['name'] = $board1->name;
        $board_serialized['user'] = $board1->user->name;
        $board_serialized['tasks'] = $board1->tasks;
        Storage::disk('local')->put('board1.json', json_encode($board_serialized));
        $path_to_json = Storage::disk('local')->path('board1.json');
        $zip_archive->addFile($path_to_json);
        $zip_archive->close();
        $hash = sha1($zip_file_name);
        Storage::disk('local')->delete($zip_file_name);
        $this->be($user1);
        $response = $this->get(route('boards.download'));
        $this->assertTrue($hash === sha1($zip_file_name));
    }

    private function createUser()
    {
        $user = factory('App\User')->create();
        $user->moderator = 0;
        return $user;
    }

    private function createBoard(int $user_id)
    {
        $board = factory(Board::class)->create();
        $board->user_id = $user_id;
        $board->save();
        return $board;
    }

    private function createTask($user_id, $board_id)
    {
        $task = factory(Task::class)->create();
        $task->board_id = $board_id;
        $task->user_id = $user_id;
        $task->save();
        return $task;
    }

}
