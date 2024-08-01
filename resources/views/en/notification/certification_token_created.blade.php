<x-mail-layout>
    <h1 style="font-size:1.5rem;font-weight:bold;text-align:center;">Certification Token created for {{ $function_name }}</h1>

    <p style="text-align:center;margin-bottom:2rem;">
        In {{ $function_name }} screen, Enter the authentication token below.
    </p>

    <div style="width:32rem;padding:2rem;font-size:2rem;text-align:center;margin:0 auto; background-color: #ccc">
        {{ $token }}
    </div>
</x-mail-layout>
