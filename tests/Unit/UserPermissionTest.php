<?php

namespace Tests\Unit;

use App\Board;
use App\User;
use App\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use DatabaseTransactions;

    public function testUserCantSeeOtherUserBoard()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->get(route('boards.tasks.index', [$board2->id]));
        $response->assertStatus(403);
    }

    public function testUserCantEditOtherUserBoard()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.update', [$board2->id,'name'=>$board2->name,'color'=>$board2->color]));
        $response->assertStatus(403);
    }

    public function testUserCantDeleteOtherUserBoard()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->delete(route('boards.destroy', [$board2]));
        $response->assertStatus(403);
    }

    public function testUserCantCreateTaskOtherUserBoard()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.store', [$board2->id, $task2->id]));
        $response->assertStatus(403);
    }

    public function testUserCantEditOtherUserTask()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.update', [$board2->id,$task2->id,
            'name' => $task2->name,
            'description' => $task2->description,
            'scheduled_date' =>$task2->scheduled_date ]));
        $response->assertStatus(403);
    }

    public function testUserCantMoveOtherUserTask()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.move', [$board2->id, $task2->id]));
        $response->assertStatus(403);
    }

    public function testUserCantCopyOtherUserTask()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.copy', [$board2->id, $task2]));
        $response->assertStatus(403);
    }

    public function testUserCantDestroyOtherUserTask()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->delete(route('boards.tasks.destroy', [$board2->id, $task2]));
        $response->assertStatus(403);
    }

    public function testModerCanSeeOtherUserBoard()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->get(route('boards.tasks.index', [$board2->id]));

        $response->assertOk();
    }

    public function testModerCanEditOtherUserBoard()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.update', [
            $board2->id,
            'name' => 'newName',
            'color' => $board2->color
        ]));
        $response->assertOk();
    }

    public function testModerCanDeleteOtherUserBoard()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        Passport::actingAs($user1);
        $response = $this->delete(route('boards.destroy', [$board2]));

        $response->assertStatus(204);
    }


    public function testModerCanMoveOtherUserTask()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.move', ['board_id' => $board2->id, 'task_id' => $task2->id, 'destination_id' => $board1->id]));
        $response->assertOk();
    }

    public function testModerCanCopyOtherUserTask()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board1 = $this->createBoard($user1->id);
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->post(route('boards.tasks.copy', ['board_id' => $board2->id, 'task_id' => $task2->id, 'destination_id' => $board1->id]));
        $response->assertOk();
    }

    public function testModerCanDestroyOtherUserTask()
    {
        $user1 = $this->createModerator();
        $user2 = $this->createUser();
        $board2 = $this->createBoard($user2->id);
        $task2 = $this->createTask($user2->id, $board2->id);
        Passport::actingAs($user1);
        $response = $this->delete(route('boards.tasks.destroy', [$board2->id, $task2]));
        $response->assertStatus(204);
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
