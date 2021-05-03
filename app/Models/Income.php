<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Period;

class Income extends Model
{
    use HasFactory;

    protected $table = 'income';

    protected $fillable = ['user_id', 'period_id', 'total' , 'remainder'];

    protected $appends = ['percent_remainder'];

    public function getTotalAttribute($value) {
        return number_format($value);
    }

    public function getRemainderAttribute($value) {
        return number_format($value);
    }

    public function getPercentRemainderAttribute() {
        return number_format(((float)$this->remainder / (float)$this->total) * 100, 2, '.', '') . '%';
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function period() {
        return $this->belongsTo(Period::class);
    }
}
