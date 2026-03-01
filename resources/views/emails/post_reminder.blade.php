<x-mail::message>
# Reminder

You asked us to remind you about this post. It is due in 12 hours!

**Post:** {{ Str::limit($post->text, 50) }}

<x-mail::button :url="config('app.url') . ':8000/#' . $post->id">
View Post
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>