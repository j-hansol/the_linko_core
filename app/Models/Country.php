<?php

namespace App\Models;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class Country extends Model {
    use HasFactory;

    protected $fillable = [
        'name', 'en_name', 'code', 'iso3_code', 'continent', 'en_continent', 'language_code'
    ];

    public $timestamps = false;

    /**
     * 국가코드를 이용하여 국가정보를 검색한다.
     * @param string $code
     * @return Country|null
     */
    public static function findByCode(string $code) : ?Country {
        return static::where('code', Str::upper($code))->get()->first();
    }

    /**
     * ISO 3문자코드를 이용하여 국가정보를 검색한다.
     * @param string $code
     * @return Country|null
     */
    public static function findByISO3Code(string $code) : ?Country {
        return static::where('iso3_code', Str::upper($code))->get()->first();
    }

    /**
     * 국가명(한글)을 이용하여 국가정보를 검색한다.
     * @param string $name
     * @return Country|null
     */
    public static function findByName(string $name) : ?Country {
        return static::where('name', 'like', '%' . Str::upper($name) . '%')
            ->get()->first();
    }

    /**
     * 국가의 영문 이름을 이용하여 국가정보를 검색한다.
     * @param string $name
     * @return Country|null
     */
    public static function findByEnName(string $name) : ?Country {
        return static::where('en_name', 'like', '%' . Str::upper($name) . '%')
            ->get()->first();
    }

    /**
     * 국적명을 이용하여 국가정보를 검색한다.
     * @param string $name
     * @return Country|null
     */
    public static function findByNationality(string $name) : ?Country {
        return static::where('nationality', 'like', '%' . Str::upper($name) . '%')
            ->get()->first();
    }

    /**
     * find 대용, 리턴 타입 설정 문제로 사용
     * @param int|null $id
     * @return Country|null
     */
    public static function findMe(?int $id = null) : ?Country {
        if(!$id) return null;
        return static::find($id);
    }

    /**
     * 데이터를 Json 문자열로 리턴한다.
     * @return JsonResponse
     * @OA\Schema (
     *     schema="country",
     *     title="국가정보",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (
     *          property="name",
     *          type="string",
     *          description="국가명"
     *     ),
     *     @OA\Property (
     *          property="en_name",
     *          type="string",
     *          description="국가명 (영문)"
     *     ),
     *     @OA\Property (
     *          property="code",
     *          type="string",
     *          description="국가코드"
     *     ),
     *     @OA\Property (
     *          property="iso3_code",
     *          type="string",
     *          description="ISO 3문자 국가코드"
     *     ),
     *     @OA\Property (
     *          property="nationality",
     *          type="string",
     *          description="국적"
     *     ),
     *     @OA\Property (
     *          property="continent",
     *          type="string",
     *          description="대륙명"
     *     ),
     *     @OA\Property (
     *          property="en_continent",
     *          type="string",
     *          description="대륙명 (영문)"
     *     ),
     *     @OA\Property (
     *          property="language_code",
     *          type="string",
     *          description="언어코드"
     *     ),
     *     @OA\Property (
     *          property="active",
     *          type="integer",
     *          description="사용여부 (1:사용, 0:미사용)"
     *     )
     * )
     */
    public function response() : JsonResponse {
        return new Data($this->toArray());
    }

    /**
     * 문자열을 이용하여 국가명을 검색한다.
     * @param string $str
     * @return Country|null
     */
    public static function findByString(?string $str) : ?Country {
        if(!$str) return null;
        if(($country = static::findByCode($str))) return $country;
        elseif(($country = static::findByEnName($str))) return $country;
        elseif(($country = static::findByCode($str))) return $country;
        elseif(($country = static::findByEnName($str))) return $country;
        elseif(($country = static::findByISO3Code($str))) return $country;
        elseif(($country = static::findByNationality($str))) return $country;
        return $country;
    }
}
