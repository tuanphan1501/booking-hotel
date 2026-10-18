<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * AD-19 Personal settings: profile fields and profile picture of the signed-in administrator.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $administrator;

    /**
     * Seed roles, create a signed-in administrator and fake the public disk.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('public');

        $this->administrator = AdminUser::factory()->create(['full_name' => 'Lê Hạnh']);
        $this->actingAs($this->administrator, 'admin');
    }

    public function test_settings_screen_shows_the_profile(): void
    {
        $this->get('/admin/profile')
            ->assertOk()
            ->assertSee('Cài đặt cá nhân')
            ->assertSee($this->administrator->email);
    }

    public function test_administrator_updates_name_phone_and_language(): void
    {
        $this->put('/admin/profile', ['full_name' => 'Lê Thị Hạnh', 'phone' => '0901 234 567', 'language' => 'ja'])
            ->assertRedirect('/admin/profile');

        $this->administrator->refresh();
        $this->assertSame('Lê Thị Hạnh', $this->administrator->full_name);
        $this->assertSame('+84901234567', $this->administrator->phone);
        $this->assertSame('ja', $this->administrator->language);

        $this->get('/admin/profile')->assertSee('個人設定');
    }

    public function test_invalid_phone_number_is_rejected(): void
    {
        $this->from('/admin/profile')
            ->put('/admin/profile', ['full_name' => 'Lê Hạnh', 'phone' => '12ab', 'language' => 'vi'])
            ->assertSessionHasErrors('phone');
    }

    public function test_administrator_uploads_and_replaces_the_avatar(): void
    {
        $this->post('/admin/profile/avatar', ['avatar' => UploadedFile::fake()->image('first.png', 200, 200)])
            ->assertRedirect('/admin/profile');
        $firstAvatarPath = $this->administrator->refresh()->avatar_path;
        Storage::disk('public')->assertExists($firstAvatarPath);

        $this->post('/admin/profile/avatar', ['avatar' => UploadedFile::fake()->image('second.jpg', 300, 300)]);
        $secondAvatarPath = $this->administrator->refresh()->avatar_path;

        Storage::disk('public')->assertExists($secondAvatarPath);
        Storage::disk('public')->assertMissing($firstAvatarPath);
        $this->get('/admin')->assertSee(Storage::disk('public')->url($secondAvatarPath), false);
    }

    public function test_non_image_and_tiny_images_are_rejected(): void
    {
        $this->from('/admin/profile')
            ->post('/admin/profile/avatar', ['avatar' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml')])
            ->assertSessionHasErrors('avatar');

        $this->from('/admin/profile')
            ->post('/admin/profile/avatar', ['avatar' => UploadedFile::fake()->image('tiny.png', 40, 40)])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($this->administrator->refresh()->avatar_path);
    }

    public function test_english_is_available_as_display_language(): void
    {
        $this->put('/admin/profile', ['full_name' => 'Lê Hạnh', 'phone' => '', 'language' => 'en'])
            ->assertRedirect('/admin/profile');

        $this->get('/admin/profile')
            ->assertSee('Personal settings')
            ->assertSee('Skip to main content')
            ->assertSee('Super Admin');
    }

    public function test_administrator_removes_the_avatar(): void
    {
        $this->post('/admin/profile/avatar', ['avatar' => UploadedFile::fake()->image('me.png', 200, 200)]);
        $avatarPath = $this->administrator->refresh()->avatar_path;

        $this->delete('/admin/profile/avatar')->assertRedirect('/admin/profile');

        $this->assertNull($this->administrator->refresh()->avatar_path);
        Storage::disk('public')->assertMissing($avatarPath);
    }
}
