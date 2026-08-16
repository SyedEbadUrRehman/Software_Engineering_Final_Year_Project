<x-mail::message>
# New Contact Form Submission

You've received a new message from the SiteClip contact form.

**Name:** {{ $contact->name }}
**Email:** {{ $contact->email }}
@if($contact->subject)
**Subject:** {{ $contact->subject }}
@endif

**Message:**

{{ $contact->message }}

<x-mail::button :url="'mailto:' . $contact->email">
Reply to {{ $contact->name }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>