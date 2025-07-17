<?php

namespace App\Livewire;

use App\Helpers\PhoneNumberHelper;
use App\Models\Contact;
use App\Rules\ValidPhoneNumber;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SmsComposer extends Component
{
    use WithFileUploads;

    // Form properties
    public $recipients = [];
    public $search = '';
    public $selectedContacts = [];
    public $customRecipient = '';
    public $subject = '';
    public $message = '';
    public $service = 'twilio';
    public $charCount = 0;
    public $charLimit = 160; // Standard SMS character limit
    public $isSending = false;
    public $sendConfirmation = false;
    public $sendSuccess = false;
    public $sendError = null;
    public $showContactList = false;
    public $importFile;
    public $groups = [];
    public $selectedGroup = '';
    public $scheduledAt;

    protected $listeners = ['contactSelected' => 'addRecipient'];

    protected $rules = [
        'recipients' => 'required|array|min:1',
        'recipients.*.phone' => 'required|string',
        'message' => 'required|string|min:1|max:1600',
        'subject' => 'nullable|string|max:50',
        'service' => 'required|in:twilio,textsms',
        'scheduledAt' => 'nullable|date|after_or_equal:now',
    ];

    protected $messages = [
        'recipients.required' => 'Please add at least one recipient.',
        'recipients.*.phone.required' => 'Phone number is required.',
        'message.required' => 'The message field is required.',
        'message.max' => 'The message is too long. Maximum 10 SMS messages (1600 characters) allowed.',
    ];

    public function mount()
    {
        // Load groups for the current user
        $this->groups = Contact::where('user_id', Auth::id())
            ->whereNotNull('group')
            ->groupBy('group')
            ->pluck('group')
            ->filter()
            ->values()
            ->toArray();
    }

    public function updatedSearch()
    {
        $this->showContactList = !empty($this->search);
    }

    public function updatedMessage()
    {
        $this->charCount = mb_strlen($this->message);
        $this->calculateSmsCount();
    }

    public function calculateSmsCount()
    {
        // This is a simplified version. In a real app, you'd check for GSM vs. Unicode characters
        $length = mb_strlen($this->message);
        $this->smsCount = ceil($length / $this->charLimit);
    }

    public function addRecipient($phone, $name = null)
    {
        // Validate phone number
        $validator = \Validator::make(
            ['phone' => $phone],
            ['phone' => ['required', new ValidPhoneNumber]]
        );

        if ($validator->fails()) {
            $this->addError('customRecipient', 'Invalid phone number format.');
            return;
        }

        // Format the phone number
        $formattedPhone = PhoneNumberHelper::formatE164($phone) ?? $phone;

        // Check if already added
        if (!collect($this->recipients)->contains('phone', $formattedPhone)) {
            $this->recipients[] = [
                'phone' => $formattedPhone,
                'name' => $name ?: $formattedPhone,
                'isCustom' => true,
            ];
        }

        $this->reset('search', 'customRecipient');
        $this->showContactList = false;
    }

    public function addCustomRecipient()
    {
        $this->addRecipient($this->customRecipient);
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    public function addGroupRecipients()
    {
        if (empty($this->selectedGroup)) {
            return;
        }

        $contacts = Contact::where('user_id', Auth::id())
            ->where('group', $this->selectedGroup)
            ->get();

        foreach ($contacts as $contact) {
            $this->addRecipient($contact->phone_number, $contact->name);
        }
    }

    public function importContacts()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $path = $this->importFile->getRealPath();
        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            if (isset($data['phone_number']) || isset($data['phone'])) {
                $phone = $data['phone_number'] ?? $data['phone'];
                $name = $data['name'] ?? null;
                $this->addRecipient($phone, $name);
            }
        }

        fclose($file);
        $this->reset('importFile');
    }

    public function sendSms()
    {
        $this->validate();
        $this->isSending = true;
        $this->sendError = null;

        try {
            $phoneNumbers = collect($this->recipients)->pluck('phone')->toArray();
            
            $response = \Http::withToken(Auth::user()->currentAccessToken()->token)
                ->post(route('api.sms.send'), [
                    'recipients' => $phoneNumbers,
                    'message' => $this->message,
                    'subject' => $this->subject ?: null,
                    'service' => $this->service,
                    'scheduled_at' => $this->scheduledAt,
                ]);

            if ($response->successful()) {
                $this->sendSuccess = true;
                $this->reset(['recipients', 'message', 'subject', 'scheduledAt']);
                $this->emit('smsSent');
            } else {
                $this->sendError = $response->json('message', 'Failed to send SMS. Please try again.');
            }
        } catch (\Exception $e) {
            $this->sendError = 'An error occurred while sending the SMS: ' . $e->getMessage();
        }

        $this->isSending = false;
    }

    public function confirmSend()
    {
        $this->validate();
        $this->sendConfirmation = true;
    }

    public function getContactsProperty()
    {
        if (empty($this->search)) {
            return collect([]);
        }

        return Contact::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            })
            ->limit(10)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone_number,
                    'group' => $contact->group,
                ];
            });
    }

    public function render()
    {
        return view('livewire.sms-composer', [
            'contacts' => $this->contacts,
        ]);
    }
}
