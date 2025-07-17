<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;
use App\Models\Contact;

class MessageRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'contact_id',
        'phone_number',
        'status',
        'error_message',
        'message_sid',
        'sent_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the message that owns the recipient.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the contact associated with the recipient.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
