@component('mail::message')
# Reminder

You asked us to remind you about this post. It is due in 12 hours!

**Post:** {{ Str::limit($post->text, 50) }}

@component('mail::button', ['url' => url('/')])
View Post
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent