<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

class NotifikasiVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyFeature(Features::emailVerification());
    }

    public function test_mengirimkan_notifikasi_verifikasi(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verifikasi.send'))
            ->assertRedirect(); // Controller redirects 'back()' which might be to home, but focus on custom notification class.

        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    public function test_tidak_mengirimkan_notifikasi_verifikasi_jika_email_sudah_diverifikasi(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('verifikasi.send'))
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSent();
    }
}
