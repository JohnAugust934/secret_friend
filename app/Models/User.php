<?php

namespace App\Models;

use App\Notifications\VerifyEmailQueued;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Envia o e-mail de verificação de forma SÍNCRONA (imediata).
     *
     * Usamos notifyNow() em vez de notify() para contornar a limitação de
     * hospedagem compartilhada (Hostinger), onde não há worker de fila
     * persistente. notifyNow() ignora o ShouldQueue e entrega via SMTP
     * no momento do cadastro/reenvio.
     *
     * O try/catch no RegisteredUserController e no
     * EmailVerificationNotificationController garante que uma falha de SMTP
     * nunca resulte em erro 500 para o usuário.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notifyNow(new VerifyEmailQueued);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('wishlist')
            ->withTimestamps();
    }
}
