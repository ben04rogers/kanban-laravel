<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'board_id',
        'provider',
        'full_name',
        'default_branch',
        'access_token',
        'connected_by_user_id',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
    ];

    // Never expose the token to the frontend.
    protected $hidden = [
        'access_token',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function connectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_by_user_id');
    }

    public function url(): string
    {
        return sprintf('https://github.com/%s', $this->full_name);
    }
}
