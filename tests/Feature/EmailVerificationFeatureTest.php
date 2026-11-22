<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーは認証必須画面へアクセスできる
     */
    public function test_認証済みユーザーは認証必須画面へアクセスできる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
    }

    /**
     * 未認証ユーザーは認証必須画面へアクセスできない
     */
    public function test_未認証ユーザーは認証必須画面へアクセスできない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    /**
     * メール認証リンクからメール認証できる
     */
    public function test_メール認証リンクからメール認証できる(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this
            ->actingAs($user)
            ->get($verificationUrl);

        $response->assertRedirect();

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );
    }

    /**
     * 不正な認証URLではメール認証できない
     */
    public function test_不正な認証URLではメール認証できない(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => 'invalid-hash',
            ]
        );

        $response = $this
            ->actingAs($user)
            ->get($verificationUrl);

        $response->assertForbidden();

        $this->assertNull(
            $user->fresh()->email_verified_at
        );
    }
}