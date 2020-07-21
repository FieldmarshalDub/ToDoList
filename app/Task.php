<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['name','description','status','scheduled_date','real_date','board_id'];
    protected $guarded = ['id','created_at','updated_at','user_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
