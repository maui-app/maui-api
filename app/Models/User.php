<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyResetEmailNotification;
use App\Notifications\ChangeEmailNotification;
use App\Notifications\EmailChangedNotification;
use App\Models\Income;
use App\Models\Avatar;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'timezone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'name','latest_income'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'total_income' => 'float'
    ];

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getLatestIncomeAttribute() 
    {
        $income = Income::where('user_id', $this->id)
        ->orderBy('created_at', 'desc')
        ->first();

        return $income;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }

    public function sendVerifyResetEmailNotification($token)
    {
        $this->notify(new VerifyResetEmailNotification($this->last_name, $token));
    }

    public function sendChangeEmailNotification($token)
    {
        $this->notify(new ChangeEmailNotification($this->name, $this->email, $token));
    }

    public function sendEmailChangedNotification()
    {
        $this->notify(new EmailChangedNotification($this->last_name, $this->email));
    }

    public function incomes() {
        return $this->hasMany(Income::class);
    }

    public function avatar() {
        return $this->hasOne(Avatar::class);
    }

    public function telegram() {
        return $this->hasOne(Telegram::class);
    }
}
