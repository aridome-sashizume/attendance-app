<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者がログインできる
     */
    public function test_管理者がログインできる(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'admin_status' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/attendance/list');

        $this->assertAuthenticatedAs($admin);
    }

    /**
     * メールアドレスが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_メールアドレスが未入力の場合はバリデーションメッセージが表示される(): void
    {
        User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $this->assertGuest();
    }

    /**
     * パスワードが未入力の場合はバリデーションメッセージが表示される
     */
    public function test_パスワードが未入力の場合はバリデーションメッセージが表示される(): void
    {
        User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $this->assertGuest();
    }

    /**
     * 登録内容と一致しない場合はエラーメッセージが表示される
     */
    public function test_登録内容と一致しない場合はエラーメッセージが表示される(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'admin_status' => true,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest();
    }

    /**
     * 一般ユーザーは管理者ログインできない
     */
    public function test_一般ユーザーは管理者ログインできない(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'admin_status' => false,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest();
    }
}
