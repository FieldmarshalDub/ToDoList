<?php

namespace Tests\Unit;
use App\Board;
use App\User;
use App\Task;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
class LogicTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testBoardIndex()
    {
        $this->clearDatabase();
        $user = $this->createUser();
        $boards = $this->createBoard($user->id);
        $this->be($user);
        $response = $this->get(route('boards.index'));
        $response->assertStatus(200);
        $boards_info['boards'] =$user->boards;
        //$response->assertJson($boards_info);
        $response->assertSee(json_encode($boards_info));

    }

    public function testBoardEdit()
    {
        $this->clearDatabase();
        $user = $this->createUser();
        $board = $this->createBoard($user->id);
        $this->be($user);
        $response = $this->get(route('boards.edit', [$board->id]));
        $response->assertStatus(200);
    }

    public function testBoardUpdate()
    {
        //$this->clearDatabase();
        $user = $this->createUser();
        $board = $this->createBoard($user->id);
        $this->be($user);
        $response = $this->post(route('boards.update',
            [
                'name' => 'newName',
                $board->id
            ]
        ));
        $this->assertDatabaseHas(
            'boards',
            [
                'name' => 'newName'
            ]
        );
        $response->assertStatus(200);
        $boards_info['boards']=$board;
        $response->assertJson($boards_info);
    }

    public function testBoardDelete()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $this->be($user1);
        $response = $this->delete(route('boards.destroy', [$board1->id]));
        $this->assertDatabaseMissing(
            'boards',
            [
                'name' => 'board1',
                'user_id' => $user1->id
            ]
        );

        $this->clearDatabase();
    }

    public function testBoardCreate()
    {
        $this->clearDatabase();
        $user1 = $this->createUser();

        $this->be($user1);
        $response = $this->get(route('boards.create'));
        $response->assertStatus(200);
    }


    public function testBoardStore()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();

        $this->be($user1);
        $this->post(route('boards.store', ['name' => 'board_name', 'color' => '#0000FF']));
        $this->assertDatabaseHas('boards',
            [
                'name' => 'board_name',
                'user_id' => $user1->id,
                'color' => '#0000FF'
            ]
        );
    }

    public function TasksIndex()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id,$board1->id);

        $this->be($user1);
        $response = $this->get(route('boards.tasks.index', [$board1->id]));
        $response->assertStatus(200);
        $task1_info['tasks'] = $task1;
        $response->assertJson($task1_info);
    }

    public function testTaskCreate()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);

        $this->be($user1);
        $response = $this->get(route('boards.tasks.create', [$board1->id]));
        $response->assertStatus(200);
    }

    public function testTaskStore()
    {
        $this->clearDatabase();
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
                    'status' => 'a'
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
                'status' => 'a'
            ]
        );
    }

    public function testTaskDelete()
    {
        $this->clearDatabase();
        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id,$board1->id);

        $this->be($user1);
        $task_id = $task1->id;
        $response = $this->delete(route('boards.tasks.destroy', [$board1->id, $task1->id]));
        $this->assertDatabaseMissing('tasks', ['id' => $task_id]);
    }

    public function testTaskUpdate()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $task1 = $this->createTask($user1->id,$board1->id);

        $this->be($user1);
        $response = $this->post(route('boards.tasks.update', [$board1->id, $task1->id,
            'name' => 'newTaskName',
            'description' => 'newDescription',
            'status' => 'b',
            'real_date' => '2020-07-10',
            'scheduled_date' => '2020-02-10'
        ]));
        $this->assertDatabaseHas('tasks', [
            'name' => 'newTaskName',
            'description' => 'newDescription',
            'status' => 'b',
            'real_date' => '2020-07-10',
            'scheduled_date' => '2020-02-10'
        ]);
    }

    public function testMoveTask()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user1->id);
        $task2 = $this->createTask($user1->id,$board2->id);

        $this->be($user1);
        $response = $this->post(route('boards.tasks.move', [$board2->id, $task2, $board1->id]));
        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['name' => $task2->name, 'board_id' => $board1->id]);
    }

    public function testCopyTask()
    {
        $this->clearDatabase();

        $user1 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user1->id);
        $task2 = $this->createTask($user1->id,$board2->id);

        $this->be($user1);
        $response = $this->post(route('boards.tasks.copy', [$board2->id, $task2,$board1->id]));
        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['name' => $task2->name, 'board_id' => $board1->id]);
        $this->assertDatabaseHas('tasks', ['name' => $task2->name, 'board_id' => $board2->id]);
    }


    public function testDownloadBoards()
    {
        $this->clearDatabase();
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
        $sha1 = sha1($zip_file_name);
        Storage::disk('local')->delete($zip_file_name);
        $this->be($user1);
        $response = $this->get(route('boards.download'));
        $this->assertTrue($sha1 === sha1($zip_file_name));
    }


    private function clearDatabase()
    {
        Task::truncate();
        Board::truncate();
        User::truncate();
    }

    private function createUser()
    {
        $user = factory('App\User')->create();
        $user->moderator = 0;
        return $user;
    }

    private function createModerator()
    {

        $moderator = factory(User::class)->create();
        $moderator->moderator = 1;
        return $moderator;
    }

    private function createBoard(int $user_id)
    {
        /*$board = factory(Board::class)->make([
            'user_id' => $user_id,
        ])->save();*/
        $board = factory(Board::class)->create();
        $board->user_id = $user_id;
        $board->save();
        return $board;
    }

    private function createTask($user_id,$board_id)
    {
        $task = factory(Task::class)->create();
        $task->board_id = $board_id;
        $task->user_id = $user_id;
        $task->save();
        return $task;
    }

}
