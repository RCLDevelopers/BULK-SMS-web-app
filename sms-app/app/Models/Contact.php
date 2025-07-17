<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MessageRecipient;

class Contact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'group',
    ];

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the message recipients for the contact.
     */
    public function messageRecipients()
    {
        return $this->hasMany(MessageRecipient::class);
    }
}
