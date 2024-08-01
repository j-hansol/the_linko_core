<x-mail-layout>
    <h1 style="font-size:1.5rem;font-weight:bold;text-align:center;">{{ $function_name }}를 위한 토큰이 발급되었습니다.</h1>

    <p style="text-align:center;margin-bottom:2rem;">
        {{ $function_name }} 화면에서 아래의 토큰을 입력하세요.
    </p>

    <div style="width:32rem;padding:2rem;font-size:2rem;text-align:center;margin:0 auto; background-color: #ccc">
        {{ $token }}
    </div>
</x-mail-layout>
