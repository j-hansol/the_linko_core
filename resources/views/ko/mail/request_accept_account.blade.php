<x-mail-layout>
    <h1 style="font-size:1.5rem;font-weight:bold;text-align:center;">{{ $account->name }}의 이름으로 회원가입이 이루어졌습니다.</h1>

    <p style="text-align:center;margin-bottom:2rem;">
        아래 내용을 확하고 관리 페이지에서 회원 승인 절차를 진행해주시기 바랍니다.
    </p>

    <div style="width:100%; border: 1px solid black;margin-top: 2rem;">
        <div style="border-bottom: 1px solid black;display: flex">
            <div style="width:8rem;padding:1rem;background-color:#eee;">단체명</div>
            <div style="padding:1rem;">{{ $account->name }}</div>
            <div style="width:8rem;padding:1rem;background-color:#eee;">소속 국가(국적)</div>
            <div style="padding:1rem;">{{ $account->getCountryName() }}</div>
        </div>
        <div style="border-bottom: 1px solid black;display: flex">
            <div style="width:8rem;padding:1rem;background-color:#eee;">회원 유형</div>
            <div style="padding:0.5rem 1rem;width: 100%;">
                @foreach($account->getTypes() as $type)
                    <span style="padding:0.5rem 1rem;border: 1px solid black;border-radius: 10px;vertical-align: middle;">
                            {{ __('member_type.' . $type->type, [], 'ko') }}
                        </span>
                @endforeach
            </div>
        </div>
        <div style="border-bottom: 1px solid black;display: flex">
            <div style="width:8rem;padding:1rem;background-color:#eee;">이메일 주소</div>
            <div style="padding:1rem;">{{ $account->email }}</div>
            <div style="width:8rem;padding:1rem;background-color:#eee;">휴대전화 번호</div>
            <div style="padding:1rem;">{{ $account->cell_phone }}</div>
        </div>
        @if($account->is_organization == 1)
            <div style="border-bottom: 1px solid black;display: flex">
                <div style="width:8rem;padding:1rem;background-color:#eee;">대표자 이름</div>
                <div style="padding:1rem;">{{ $account->boss_name }}</div>
                <div style="width:8rem;padding:1rem;background-color:#eee;">담당자 이름</div>
                <div style="padding:1rem;">{{ $account->manager_name }}</div>
            </div>
            <div style="border-bottom: 1px solid black;display: flex">
                <div style="width:8rem;padding:1rem;background-color:#eee;">전화번호</div>
                <div style="padding:1rem;">{{ $account->telephone }}</div>
                <div style="width:8rem;padding:1rem;background-color:#eee;">팩스번호</div>
                <div style="padding:1rem;">{{ $account->fax }}</div>
            </div>
        @else
            <div style="border-bottom: 1px solid black;display: flex">
                <div style="width:8rem;padding:1rem;background-color:#eee;">성별</div>
                <div style="padding:1rem;">{{ $account->sex }}</div>
                <div style="width:8rem;padding:1rem;background-color:#eee;">생년월일</div>
                <div style="padding:1rem;">{{ $account->birthday }}</div>
            </div>
        @endif
        <div style="border-bottom: 1px solid black;display: flex">
            <div style="width:8rem;padding:1rem;background-color:#eee;">주소</div>
            <div style="padding:1rem;">{{ $account->address }}</div>
        </div>
    </div>
</x-mail-layout>
