<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request)
    {
        $query = Contact::where('user_id', Auth::id())
            ->with('groups')
            ->latest();

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply group filter
        if ($request->has('group')) {
            $group = $request->input('group');
            $query->whereHas('groups', function($q) use ($group) {
                $q->where('groups.id', $group);
            });
        }

        $contacts = $query->paginate(20);
        $groups = Group::where('user_id', Auth::id())->get();

        return view('contacts.index', compact('contacts', 'groups'));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        $groups = Group::where('user_id', Auth::id())->get();
        return view('contacts.create', compact('groups'));
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id,user_id,' . Auth::id()
        ]);

        $contact = Auth::user()->contacts()->create($validated);

        // Sync groups if any
        if (isset($validated['groups'])) {
            $contact->groups()->sync($validated['groups']);
        }

        return redirect()->route('contacts.index')
            ->with('status', 'Contact created successfully!');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        $contact->load('groups');
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact)
    {
        $this->authorize('update', $contact);
        $groups = Group::where('user_id', Auth::id())->get();
        $contact->load('groups');
        return view('contacts.edit', compact('contact', 'groups'));
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id,user_id,' . Auth::id()
        ]);

        $contact->update($validated);

        // Sync groups if any
        $contact->groups()->sync($validated['groups'] ?? []);

        return redirect()->route('contacts.show', $contact)
            ->with('status', 'Contact updated successfully!');
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);
        
        $contact->delete();
        
        return redirect()->route('contacts.index')
            ->with('status', 'Contact deleted successfully!');
    }

    /**
     * Show the import contacts form
     */
    public function showImportForm()
    {
        $groups = Group::where('user_id', Auth::id())->get();
        return view('contacts.import', compact('groups'));
    }

    /**
     * Process the imported contacts file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id,user_id,' . Auth::id(),
            'has_headers' => 'boolean'
        ]);

        $file = $request->file('file');
        $hasHeaders = $request->boolean('has_headers', true);
        $groups = $request->input('groups', []);
        
        try {
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            
            if ($hasHeaders) {
                $csv->setHeaderOffset(0);
                $headers = $csv->getHeader();
            } else {
                $csv->setHeaderOffset(null);
                $headers = ['name', 'phone', 'email', 'company', 'address', 'notes'];
            }
            
            $records = (new Statement())->process($csv);
            $imported = 0;
            $skipped = 0;
            
            foreach ($records as $record) {
                // Skip if required fields are missing
                if (empty($record['name']) || empty($record['phone'])) {
                    $skipped++;
                    continue;
                }
                
                // Create or update contact
                $contact = Auth::user()->contacts()->updateOrCreate(
                    ['phone' => $record['phone']],
                    [
                        'name' => $record['name'],
                        'email' => $record['email'] ?? null,
                        'company' => $record['company'] ?? null,
                        'address' => $record['address'] ?? null,
                        'notes' => $record['notes'] ?? null,
                    ]
                );
                
                // Sync groups if any
                if (!empty($groups)) {
                    $contact->groups()->syncWithoutDetaching($groups);
                }
                
                $imported++;
            }
            
            $message = "Successfully imported {$imported} contacts.";
            if ($skipped > 0) {
                $message .= " {$skipped} rows were skipped due to missing data.";
            }
            
            return redirect()->route('contacts.index')
                ->with('status', $message);
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing contacts: ' . $e->getMessage());
        }
    }

    /**
     * Export contacts to CSV
     */
    public function export(Request $request)
    {
        $query = Contact::where('user_id', Auth::id())
            ->with('groups');

        // Apply filters same as index method
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('group')) {
            $group = $request->input('group');
            $query->whereHas('groups', function($q) use ($group) {
                $q->where('groups.id', $group);
            });
        }

        $contacts = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($contacts) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Name', 'Phone', 'Email', 'Company', 'Address', 'Groups', 'Notes', 'Created At'
            ]);
            
            // Add data rows
            foreach ($contacts as $contact) {
                $groupNames = $contact->groups->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $contact->name,
                    $contact->phone,
                    $contact->email,
                    $contact->company,
                    $contact->address,
                    $groupNames,
                    $contact->notes,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
