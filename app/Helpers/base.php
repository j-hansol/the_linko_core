<?php

use App\Lib\MemberType;
use App\Models\AccessToken as AccessTokenModel;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

if( !function_exists('token_replace') ) {
    /**
     * 텍스트에 포함된 토큰을 지정된 내용으로 대체하여 리턴한다.
     * @param $text
     * @param $replacement
     * @return string
     */
    function token_replace( string $text, array $replacement = [] ) : string {
        if( !empty( $replacement ) ) {
            foreach( $replacement as $key => $val ) {
                $text = str_replace( ':' . $key, $val, $text );
            }
        }

        return $text;
    }
}

if( !function_exists('path_group') ) {
    /**
     * 경로 일치 여부를 판단하여 리턴한다. 지정 라우터 경로가 현재 URL 왼쪽에서 일부 일치하면 참을 그렇지 않으면 거짓을 리턴한다.
     * @param $name
     * @param $param
     * @return bool
     */
    function path_group( string $name, array $param = [] ) : bool {
        $g_url = route( $name, $param );
        $c_url = url()->current();
        return str_contains( $c_url, $g_url ) != false;
    }
}

if( !function_exists('l_view') ) {
    /**
     * 현재 언어의 템플릿을 렌더링하여 리턴한다.
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    function l_view(string $view, array $data = [], array $mergeData = []) {
        $locale = app()->getLocale();
        $fallback_locale = config('app.fallback_locale');
        if(file_exists(resource_path("views/{$locale}"))) $view = "{$locale}.{$view}";
        elseif(file_exists(resource_path("views/{$fallback_locale}"))) $view = "{$fallback_locale}.{$view}";

        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if( !function_exists('display_error_page') ) {
    /**
     * 에러 페이지 내용을 리턴한다.
     * @param string $title
     * @param string $content
     * @param string|null $description
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function display_error_page( string $title, string $content, ?string $description = null ) {
        return l_view('common.error', [
            'title' => $title,
            'content' => $content,
            'description' => $description
        ]);
    }
}

if( !function_exists('gen_random_num') ) {
    /**
     * 숫자 및 대문자를 이용하여 무작위 문자열을 생성하여 리턴한다.
     * @param int $len
     * @return string
     */
    function gen_random_num(int $len) : string {
        $seeds = [
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K',
            'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X', 'Y', 'Z'];

        $str = '';
        for($i = 0 ; $i < $len ; $i++ ) {
            $r = rand(0, 33);
            $str .= $seeds[$r];
        }

        return $str;
    }
}

if( !function_exists('get_date_string') ) {
    /**
     * 회원코드 생성에서 사용할 날짜기반 코드를 생성하여 리턴한다.
     * @param int $year
     * @param int $month
     * @param int $day
     * @return string
     */
    function get_date_string( int $year, int $month, int $day ) : string {
        $sm = ['NON', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

        $y = sprintf('%04d', $year);
        $m = $sm[$month];
        $d = sprintf('%02d', $day);
        return ($y . $m . $d);
    }
}

if( !function_exists('current_user')) {
    /**
     * 현재 로그인 회원 정보를 리턴한다. 운영자에 한하여 전환된 계정 사용 기능을 활성화한다.
     * @param $guard
     * @return User|null
     */
    function current_user($guard = null) : ?User {
        static $logged_user, $switched_user, $target_guard;

        if(!$logged_user) {
            $guards = config('auth.guards');
            if($guard) {$target_guard = $guard; $logged_user = request()->user($guard);}
            elseif($guards)
                foreach($guards as $name => $info) if($logged_user = request()->user($name)) {$target_guard = $name; break;}
            else $logged_user = request()->user();
        }
        if(!$logged_user) return null;

        if($logged_user->isOwnType(\App\Lib\MemberType::TYPE_OPERATOR)) {
            if(!$target_guard || $target_guard == 'web') {
                $switched_user_id = session()->get('switched_user');
                if($switched_user_id) {
                    if(!$switched_user || $switched_user->id != $switched_user_id) {
                        $switched_user = User::find($switched_user_id);
                        $switched_user->setSwitchedUser(true);
                    }

                    if($switched_user) return $switched_user;
                    else session()->forget('switched_user');
                }
            }
            elseif($target_guard == 'api') {
                $token = access_token() ? AccessTokenModel::find(access_token()) : null;
                if($token && $token->switched_user_id) {
                    $switched_user = User::find($token->switched_user_id);
                    $switched_user->setSwitchedUser(true);

                    if($switched_user) return $switched_user;
                    else $token->resetSwitchedUser();
                }
            }
        }

        return $logged_user;
    }
}

if( !function_exists('access_token')) {
    /**
     * 엑세스 토큰을 리턴한다.
     * @param string|null $extern_access_token
     * @return string|null
     */
    function access_token(?string $extern_access_token = null) : ?string {
        static $token;
        if(!$token) {
            if(!$extern_access_token) $token = request()->header('X-ACCESS-TOKEN');
            else $token = $extern_access_token;
        }
        return $token;
    }
}

if( !function_exists('filter_request_input')) {
    /**
     * 입력된 배열에서 특정 키의 값만 추출하여 리턴한다.
     * @param array $keys
     * @param array $inputs
     * @return array
     */
    function filter_request_input(array $keys, array $inputs) :array {
        $ret = array_flip($keys);
        foreach($keys as $key) $ret[$key] = $inputs[$key] ?? null;
        return $ret;
    }
}

if( !function_exists('show_file')) {
    function show_file(string $disk, string $path, ?string $origin_name = null) : StreamedResponse {
        $headers = [];
        $adaptor = Storage::disk($disk);
        $headers['Content-Type'] = $adaptor->mimeType($path);
        $headers['Content-Length'] = $adaptor->size($path);
        if($origin_name) $headers['content-disposition'] = "attachment; filename={$origin_name}";

        $response = new StreamedResponse;
        $response->headers->replace($headers);
        $response->setCallback(function () use ($adaptor, $path) {
            $stream = $adaptor->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });
        return $response;
    }
}

/**
 * 파일 유형을 리턴한다.
 */
if(!function_exists('get_mime_type')) {
    function get_mime_type(string $disk, string $path) : ?string {
        try {
            $adaptor = Storage::disk($disk);
            return $adaptor->mimeType($path);
        } catch (Exception $exception) {
            return 'application/binary';
        }
    }
}

if(!function_exists('get_filter')) {
    /**
     * 검색(필터)관련 파라메터를 리턴한다.
     * @param Request $request
     * @return array
     */
    function get_filter(Request $request) : array {
        $filter_field = $request->get('filter');
        $filter_operator = $request->get('op', 'like');
        $filter_keyword = $request->get('keyword');
        if($filter_operator == 'like') $filter_keyword = "%{$filter_keyword}%";
        return [$filter_field, $filter_operator, $filter_keyword];
    }
}

if(!function_exists('get_page')) {
    /**
     * 페이지관련 파라메터를 리턴한다.
     * @param Request $request
     * @return array
     */
    function get_page(Request $request) : array {
        $page = $request->get('page') ?? 1;
        $page_per_items = $request->get('page_per_items') ?? 50;
        $start_rec_no = ($page - 1) * $page_per_items;
        return [$page, $page_per_items, $start_rec_no];
    }
}

if(!function_exists('get_order')) {
    /**
     * 정렬관련 파라메터를 리턴한다.
     * @param Request $request
     * @param string $field_name
     * @return array
     */
    function get_order(Request $request, string $field_name = 'name') : array {
        $order_field = $request->get('order') ?? $field_name;
        $order_direction = $request->get('dir') ?? 'asc';
        return [$order_field, $order_direction];
    }
}

if(!function_exists('get_foreign_manager')) {
    /**
     * 로그인 계정으로부터 소속 또는 관리 기관 계정을 찾아 리턴한다.
     * @return User|null
     */
    function get_foreign_manager() : ?User {
        static $manager = null;
        if($manager) return $manager;

        $user = current_user();
        if($user->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)) return $manager = $user;
        elseif(($user->isOwnType(MemberType::TYPE_FOREIGN_MANAGER_OPERATOR) || $user->isOwnType(MemberType::TYPE_FOREIGN_PERSON)) && $user->management_org_id) return $manager = User::findMe($user->management_org_id);
        else return null;
    }
}

if(!function_exists('convert_int_array')) {
    /**
     * 주어진 문자열을 컴마를 기준으로 분리하고, 정수로 변환하여 배열로 리턴한다.
     * @param string $value
     * @return array|null
     */
    function convert_int_array(?string $value) : ?array {
        if(!$value) return null;
        $values = explode(',', $value);
        $ret = []; foreach($values as $t) $ret[] = (int)$t;
        return $ret;
    }
}

if(!function_exists('get_date_from_format')) {
    /**
     * 전달된 데이터의 포맷에 따라 날짜를 변환하여 리턴한다.
     * @param mixed $date
     * @return Carbon|null
     */
    function get_date_from_format(mixed $date) : ?Carbon {
        $exprs = [
            '/\d{4}\-\d{1,2}\-\d{1,2}/' => 'Y-m-d',
            '/\d{4}\.\d{1,2}\.\d{1,2}/' => 'Y.m.d',
            '/\d{4}\/\d{1,2}\/\d{1,2}/' => 'Y/m/d',
            '/\d{1,2}\-\d{1,2}\-\d{4}/' => 'm-d-Y',
            '/\d{1,2}\.\d{1,2}\.\d{4}/' => 'm.d.Y',
            '/\d{1,2}\/\d{1,2}\/\d{4}/' => 'm/d/Y',
        ];

        if($date) {
            try {
                if(is_numeric($date)) {
                    $date -= 25569;
                    $temp_date = Carbon::create(1970);
                    $temp_date->addDays($date);
                    return $temp_date;
                }
                else {
                    $temp_date = trim($date);
                    $temp_date = preg_replace('/[\.\-]\s+/', '-', $temp_date);
                    $temp_date = preg_replace('/\s+/', '-', $temp_date);
                    foreach($exprs as $expr => $format) {
                        if(preg_match($expr, $temp_date)) return Carbon::createFromFormat($format, $temp_date);
                    }
                }
            }
            catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}


if(!function_exists('str_number')) {
    /**
     *지정 길이의 숫자 문자열을 리턴한다.
     * @param int $nDigit
     * @return string|null
     */
    function str_number(int $nDigit) : ?string {
        $t = '';
        for($i = 0 ; $i < $nDigit ; $i++) $t .= rand(0, 9);
        return $t;
    }
}

if(!function_exists('telegram_message')) {
    /**
     * @param string $message
     * @param array $attach_message
     * @return void
     */
    function telegram_message(string $message, array $attach_message = []) : void {
        $token = config('telegram.bots.mybot.token');
        $chat_id = config('telegram.chat_id');
        if($token && $chat_id) {
            try {
                $telegram = Telegram::bot('mybot');
                if(!empty($attach_message)) {
                    $temp = array_map(function($t) {if(!is_array($t)) return $t; else return reset($t);}, $attach_message);
                    $string = implode("\n", array_values($temp));
                }
                else $string = null;
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $message ."\n" . $string]);
            }
            catch (Exception $e) {
                $t = $e->getMessage();
            }
        }
    }
}

if(!function_exists('is_web_image')) {
    /**
     * 이미지 마입타입 여부를 판별한다.
     * @param string $mime
     * @return bool
     */
    function is_web_image(string $mime) : bool {
        return in_array($mime, ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']);
    }
}
