<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録が成功する
     */
    public function test_会員登録が成功する(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'users',
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
            ]
        );
    }

    /**
     * 名前が未入力の場合はバリデーションエラーを返す
     */
    public function test_名前が未入力の場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => '',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]
        );

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    /**
     * メールアドレスが未入力の場合はバリデーションエラーを返す
     */
    public function test_メールアドレスが未入力の場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => '',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]
        );

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    /**
     * メールアドレス形式が不正な場合はバリデーションエラーを返す
     */
    public function test_メールアドレス形式が不正な場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]
        );

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    /**
     * パスワード確認が一致しない場合はバリデーションエラーを返す
     */
    public function test_パスワード確認が一致しない場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different-password',
            ]
        );

        $response->assertSessionHasErrors([
            'password',
        ]);
    }

    /**
     * パスワード文字数が不足している場合はバリデーションエラーを返す
     */
    public function test_パスワード文字数が不足している場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ]
        );

        $response->assertSessionHasErrors([
            'password',
        ]);
    }

    /**
     * パスワードが未入力の場合はバリデーションエラーを返す
     */
    public function test_パスワードが未入力の場合はバリデーションエラーを返す(): void
    {
        $response = $this->post(
            '/register',
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'password',
        ]);
    }
}
