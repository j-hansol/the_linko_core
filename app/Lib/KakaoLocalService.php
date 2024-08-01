<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;

class KakaoLocalService {
    private ?string $api_key = null;
    private string $request_url = 'https://dapi.kakao.com/v2/local/search/address.json';

    function __construct() {
        $this->api_key = env('KAKAO_REST_API_KEY');
    }

    /**
     * 입력한 주소의 경위도 자표를 리턴한다.
     * @param string $address
     * @return array|null
     */
    public function query(string $address) : ?array {
        if(!$this->api_key) return null;

        $response = Http::withHeaders(['Authorization' => "KakaoAK {$this->api_key}"])
            ->get($this->request_url, ['query' => $address]);
        if($response->ok()) {
            $body = $response->json();
            if($body['meta']['total_count'] > 0) return [
                'longitude' => $body['documents'][0]['x'],
                'latitude' => $body['documents'][0]['y']
            ];
            else return null;
        }
        else return null;
    }
}
