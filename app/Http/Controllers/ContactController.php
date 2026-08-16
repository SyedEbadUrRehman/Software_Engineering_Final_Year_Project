<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\NewContactMail;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Contact/Index', []);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::create($request->validated());

        try {
            Mail::to(config('app.admin_email'))->send(new NewContactMail($contact));
        } catch (\Throwable $e) {
            // Don't block the user's success flow if mail fails — just log it
            Log::error('Failed to send contact notification email: ' . $e->getMessage());
        }

        return back()->with('success', 'Thanks for reaching out! We\'ve received your message and will get back to you soon.');
    }
}