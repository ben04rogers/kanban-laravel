<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(BoardColumn::class)->orderBy('position');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(BoardShare::class);
    }

    public function sharedWith(): HasMany
    {
        return $this->hasManyThrough(User::class, BoardShare::class, 'board_id', 'id', 'id', 'user_id');
    }
}
