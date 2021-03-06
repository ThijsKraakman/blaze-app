<?php

namespace Tests\Feature;

use App\User;
use App\Sound;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SoundTest extends TestCase
{
    use RefreshDatabase;

    public function tests_a_sound_can_be_created_without_image()
    {
        $this->withoutExceptionHandling();
        $this->signIn();
        $this->get('/sounds/create')
            ->assertStatus(200);

        $sound = factory(Sound::class)->make([
            'file' => 'sounds/sound.mp3',
            'owner_id' => 1]
        );

        $file = $this->createSoundFile('sound.mp3');

        $data =  [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => $file,
            'owner_id' => $sound->owner_id
        ];

        $this->post('/sounds', $data)->assertRedirect('/sounds');

        $this->assertFileExists($file);
        $this->assertDatabaseHas('sounds', [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => 'sounds/sound.mp3',
            'image' => 'images/default.jpg',
            'owner_id' => $sound->owner_id
        ]);

        $this->deleteSoundFile();
    }

    public function tests_a_sound_can_be_created_with_image()
    {
        $this->withoutExceptionHandling();
        $this->signIn();
        $this->get('/sounds/create')
            ->assertStatus(200);

        $sound = factory(Sound::class)->make([
            'file' => 'sounds/sound.mp3',
            'owner_id' => 1]
        );

        $file = $this->createSoundFile('sound.mp3');
        $image = $this->createImage('image.jpg');

        $data =  [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => $file,
            'image' => $image,
            'owner_id' => $sound->owner_id
        ];

        $this->post('/sounds', $data)->assertRedirect('/sounds');

        $this->assertFileExists($file);
        $this->assertFileExists($image);

        $this->assertDatabaseHas('sounds', [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => 'sounds/sound.mp3',
            'image' => 'images/image.jpg',
            'owner_id' => $sound->owner_id
        ]);

        $this->deleteSoundFile();
        $this->deleteImageFile();
    }
    public function tests_a_sound_can_be_retrieved()
    {
        $this->signIn();
        $sound = factory(Sound::class)->make();

        $file = $this->createSoundFile('sound.mp3');

        $data =  [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => $file,
            'owner_id' => $sound->owner_id
        ];

        $this->post('/sounds', $data)->assertRedirect('/sounds');

        $this->get('/sounds')
            ->assertSee($data['title'])
            ->assertSee($data['description']);

        $this->deleteSoundFile();
    }

    public function tests_only_audio_files_can_be_stored()
    {
        $this->signIn();

        $this->get('/sounds/create')
            ->assertStatus(200);

        $sound = factory(Sound::class)->make();

        $file = $this->createSoundFile('file.pdf');

        $this->post('/sounds', [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => $file,
            'owner_id' => $sound->owner_id
        ])->assertSessionHasErrors();

        $this->assertDatabaseMissing('sounds', [
            'title' => $sound->title,
            'description' => $sound->description,
            'file' => 'sounds/sound.mp3',
            'owner_id' => $sound->owner_id
        ]);

        $this->deleteSoundFile();
    }

    public function tests_title_is_required()
    {
        $this->signIn();

        $this->get('/sounds/create')
            ->assertStatus(200);

        $sound = factory(Sound::class)->make();
        Storage::fake('sounds');
        $file = UploadedFile::fake()->create('sound.mp3', 100);

        $data =  [
            'title' => null,
            'description' => $sound->description,
            'file' => $file,
            'owner_id' => $sound->owner_id
        ];

        $this->post('/sounds', $data)->assertSessionHasErrors('title');

        $this->deleteSoundFile();
    }
}
