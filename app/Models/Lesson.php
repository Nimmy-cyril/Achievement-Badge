<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /*protected $fillable = [
        'title'
    ];*/
    protected $fillable = ['user_id', 'title'];
    
    public function users() {
        return $this->belongsToMany(User::class, 'lessons_user')->withPivot('watched');

    }
}
