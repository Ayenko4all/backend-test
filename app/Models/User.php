<?php

namespace App\Models;

use App\Models\Comment;
use App\Options\AchievementOptions;
use App\Options\BadgeOption;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    private $remaining_number;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The comments that belong to the user.
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }


    public function achievements(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievement', 'user_id', 'achievement_id')
            ->withTimestamps();
    }

    /**
     * The lessons that a user has access to.
     */
    public function lessons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Lesson::class);
    }

    /**
     * The lessons that a user has watched.
     */
    public function watched()
    {
        return $this->belongsToMany(Lesson::class)->wherePivot('watched', true);
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges',  'user_id', 'badge_id')->withTimestamps();
    }

    public function availableAchievements()
    {
        $ids = DB::table('user_achievement')->where('user_id', '=', $this->id)->pluck('achievement_id')->all();

       return Achievement::select(['name'])->whereNotIn('id', $ids)->get();
    }

    public function nextAvailableBadge()
    {
        $ids = DB::table('user_badges')->where('user_id',  $this->id)->pluck('badge_id')->all();
       return Badge::whereNotIn('id', $ids)
            ->orderBy('id', 'asc')
            ->first()->name;
    }

    public function remainingToUnlockNextBadge (): int
    {
        $remaining_number = 0;

        $currentBadge = null;

        $badge = $this->badges()->orderByDesc('id')->first();

        $badge ? $currentBadge = $badge->name : $remaining_number = 1;

        $achievements = $this->achievements()->get()->count() ;

        if ($currentBadge == BadgeOption::STARTER_BADGE){
            $remaining_number = 4 - $achievements ;
        }

        if ($currentBadge == BadgeOption::INTERMEDIATE_BADGE){
            $remaining_number = 8 - $achievements;
        }

        if ($currentBadge == BadgeOption::ADVANCED_BADGE){
            $remaining_number = 0;
        }

        return  $remaining_number;
    }

    public function unLockIntermediateBadge(): void
    {
        if($this->lessons()->count()  >= 5 && $this->comments()->count()  >= 5
            && ! in_array(BadgeOption::INTERMEDIATE_BADGE, (array)$this->badges()->select(['name'])->get(), true)){
            $this->badges()->attach(Badge::where('name', BadgeOption::INTERMEDIATE_BADGE)->first()->id);
        }
    }
}
