<x-guest-layout>
    <x-slot:title>Enums for Api Documentation</x-slot:title>
    <h1 class="text-3xl font-bold">근로자 정보 임시 저장 엑셀 파일 규격 (Excel column rule for PreSavedWorkerInfo)</h1>
    <p class="mt-4 mb-2">아래와 같은 칼럼과 규격의 엑셀파일 업로드</p>

    <table class="border w-full">
        <thead>
            <tr class="bg-black border-b">
                <th class="text-white p-2 border-r">family_name</th>
                <th class="text-white p-2 border-r">given_names</th>
                <th class="text-white p-2 border-r">identity_no</th>
                <th class="text-white p-2 border-r">sex</th>
                <th class="text-white p-2 border-r">birthday</th>
                <th class="text-white p-2 border-r">cell_phone</th>
                <th class="text-white p-2 border-r">email</th>
                <th class="text-white p-2">address</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b">
                <td class="text-center p-2 border-r">required</td>
                <td class="text-center p-2 border-r">required</td>
                <th class="text-white p-2 border-r">optional</th>
                <td class="text-center p-2 border-r">required</td>
                <td class="text-center p-2 border-r">optional</td>
                <td class="text-center p-2 border-r">required</td>
                <td class="text-center p-2 border-r">required</td>
                <td class="text-center p-2">optional</td>
            </tr>
            <tr>
                <td class="text-center p-2 border-r">string</td>
                <td class="text-center p-2 border-r">string</td>
                <th class="text-white p-2 border-r">num_string</th>
                <td class="text-center p-2 border-r">string (M/F)</td>
                <td class="text-center p-2 border-r">date</td>
                <td class="text-center p-2 border-r">num_string</td>
                <td class="text-center p-2 border-r">string</td>
                <td class="text-center p-2">string</td>
            </tr>
        </tbody>
    </table>
</x-guest-layout>
