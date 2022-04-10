<?php

namespace Tests\Feature;

use App\Events\CommentWritten;
use App\Events\LessonWatched;
use App\Listeners\RegisterWatchedLesson;
use App\Listeners\UnlockAchievement;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\User;
use App\Options\AchievementOptions;
use App\Options\BadgeOption;
use Database\Seeders\AchievementSeeder;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\LessonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Queue\Listener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserAchievementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var User
     */
    private $user;



    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->seed([AchievementSeeder::class, BadgeSeeder::class]);

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_unlock_first_comment_achievement_for_user()
    {
        Event::fake([CommentWritten::class]);

        $form = [
            'body' => $this->faker->sentence,
        ];

        $this->postJson(route('comment.store', $this->user->id), $form)
            ->assertCreated();

        $comment = $this->user->comments()->first();

        Event::assertDispatched(CommentWritten::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });

        (new UnlockAchievement())->handle(
            new CommentWritten($comment)
        );
        $this->assertEquals(1, $this->user->comments()->count());

        $this->assertEquals(1, $this->user->achievements()->count());

        $this->assertEquals(1, $this->user->badges()->count());
    }

    /** @test */
    public function it_unlock_first_lesson_watch_achievement_for_user()
    {
        Event::fake([LessonWatched::class]);

        $lesson = Lesson::factory()->create()->first();

        $this->postJson(route('lesson.watched',[$lesson->id, $this->user->id]))
            ->assertOk();

        Event::assertDispatched(LessonWatched::class, function ($event) use ($lesson) {
            return $event->lesson->id === $lesson->id;
        });

        (new RegisterWatchedLesson())->handle(
            new LessonWatched($lesson, $this->user)
        );

        $this->assertEquals(1, $this->user->lessons()->count());

        $this->assertEquals(1, $this->user->achievements()->count());
    }

    /** @test */
    public function it_unlock_fith_comment_achievement_for_user()
    {
        Event::fake([CommentWritten::class]);

        Lesson::factory()->count(10)->create()
            ->each(function ($lesson){
                $this->user->lessons()->attach($lesson->id, ['watched' => true]);
            });

        Comment::factory()->count(4)->create(['user_id' => $this->user->id]);

        Achievement::factory()->count(3)->create()
            ->each(function ($achievement){
                $this->user->achievements()->attach($achievement->id);
            });

        $this->user->badges()->attach(Badge::where('name', BadgeOption::STARTER_BADGE)->first()->id);

        $form = [
            'body' => $this->faker->sentence,
        ];

        $this->postJson(route('comment.store', $this->user->id), $form)
            ->assertCreated();

        $comment = $this->user->comments()->skip(4)->first();

        Event::assertDispatched(CommentWritten::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });

        (new UnlockAchievement())->handle(
            new CommentWritten($comment)
        );

        $this->assertEquals(5, $this->user->comments()->count());

        $this->assertEquals(4, $this->user->achievements()->count());

        $this->assertEquals(2, $this->user->badges()->count());
    }

    /** @test */
    public function it_unlock_fith_lesson_watch_achievement_for_user()
    {
        Event::fake([LessonWatched::class]);

        Lesson::factory()->count(20)->create();

        Comment::factory()->count(20)->create(['user_id' => $this->user->id]);

         Achievement::take(2)->orderBy('id', 'asc')
            ->get()
            ->each(function ($achievement){
                $this->user->achievements()->attach([$achievement->id]);
            });

        $this->user->achievements()->attach(Achievement::where('name', AchievementOptions::FIFTH_COMMENT_WRITTEN)->first()->id);

        Lesson::take(4)->orderBy('id', 'asc')
            ->get()
            ->each(function ($lesson){
                $this->user->lessons()->attach($lesson->id, ['watched' => true]);
            });

        $this->user->badges()->attach(Badge::where('name', BadgeOption::STARTER_BADGE)->first()->id);

        $lesson = Lesson::skip(4)->first();

        $this->postJson(route('lesson.watched',[$lesson->id, $this->user->id]))
            ->assertOk();

        Event::assertDispatched(LessonWatched::class, function ($event) use ($lesson) {
            return $event->lesson->id === $lesson->id;
        });

        (new RegisterWatchedLesson())->handle(
            new LessonWatched($lesson, $this->user)
        );

        $this->assertEquals(5, $this->user->lessons()->count());

        $this->assertEquals(4, $this->user->achievements()->count());

        $this->assertEquals(2, $this->user->badges()->count());

        $this->assertDatabaseCount('user_achievement', 4);

        $this->assertDatabaseCount('user_badges', 2);

        $this->assertDatabaseCount('lesson_user', 5);
    }
}
