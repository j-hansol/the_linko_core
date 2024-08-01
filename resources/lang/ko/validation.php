<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute 항목은 반드시 수용해야 합니다.',
    'accepted_if' => ':other 이 :value 인경우 :attribute 필드는 수용되어야 합니다.',
    'active_url' => ':attribute 항목의 URL이 올바르지 않습니다.',
    'after' => ' :attribute 항목은 :date 보다 후로 입력되어야 합니다.',
    'after_or_equal' => ':attribute 항목은 :date 이후여야 합니다.',
    'alpha' => ':attribute 항목은 영문자만 입력되어야 합니다.',
    'alpha_dash' => ':attribute 항목은 영문자, 숫자, -, _ 만 가능합니다.',
    'alpha_num' => ':attribute 항목은 영문자와 숫자만 가능합니.',
    'array' => ':attribute 항목은 배이어야 합니다.',
    'ascii' => ':attribute 알파벳이나 심불(1바이트)이어야 합니다.',
    'before' => ':attribute 항목은 :date 전으로 입력되어야 합니다.',
    'before_or_equal' => ':attribute 항목은 :date 이전이어야 합니다.',
    'between' => [
        'numeric' => ':attribute 항목은 :min과 :max 사이의 값만 가능합니다.',
        'file' => ':attribute 항목의 파일 용량은 :min ~ :max킬로바이트 사이여야 합니다.',
        'string' => ':attribute 항목의 내용의 길이는 :min ~ :max 사이로 제한됩니다.',
        'array' => ':attribute 항목은 배열 요소가 :min ~ :max 사이로 제한됩니다.',
    ],
    'boolean' => ':attribute 항목은 논리값(True, False, 1, 0)만 가능합니다.',
    'confirmed' => ':attribute 항목이 확인용 내용과 일치하지 않습니다.',
    'current_password' => '비밀번호가 잘 못되었습니다.',
    'date' => ':attribute 항목은 올바른 날짜면 가능합니다.',
    'date_equals' => ':attribute 항목은 :date와 같아야 합니다.',
    'date_format' => ':attribute 항목은 :format 형식과 같아야 합니다.',
    'decimal' => 'The :attribute 필드는 :decimal 자리의 숫자여야 합니다.',
    'declined' => ':attribute 필드는 거절(부정)이어야 합니다.',
    'declined_if' => ':other 가 :value인 경우 :attribute 필드는 거절(부정)이어야 합니다.',
    'different' => ':attribute와 :other 서로 달라야 합니다.',
    'digits' => ':attribute 항목은 :digits 자리수로 입력되어야 합니다.',
    'digits_between' => ':attribute :min ~ :max 사이의 자릿수로 제한됩니다.',
    'dimensions' => ':attribute 항목의 이미지 해상도가 지정 해상도에 부합하지 않습니다.',
    'distinct' => ':attribute 항목에 중복된 값이 입력되었습니다.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => ':attribute 항목은 올바른 전자우편 주소가 입력되어야 합니다.',
    'ends_with' => ':attribute 항목은 다음 값들 중 하나로 끝나야 합니다. (값 : :values)',
    'enum' => '선택된 :attribute는 올바르지 않습니다..',
    'exists' => ':attribute 항목의 값이 올바르지 않습니다.',
    'file' => ':attribute 항목은 파일이어야 합니다.',
    'filled' => ':attribute 항목은 반드시 값을 가지고 있어야 합니다.',
    'gt' => [
        'numeric' => ':attribute 항목은 :value보다 크야 합니다.',
        'file' => ':attribute 항목의 파일 용량은 :valueKB보다 크야 합니다.',
        'string' => ':attribute 항목은 문자 길이가 :value보다 크야 합니다.',
        'array' => ':attribute 항목은 배열 요소 수가 :value 크야 합니다.',
    ],
    'gte' => [
        'numeric' => ':attribute 항목은 :value보다 크거나 같아야 합니다.',
        'file' => ':attribute 항목의 파일 용량은 :valueKB보다 크거나 같아야 합니다.',
        'string' => ':attribute 항목은 문자 길이가 :value보다 크거나 같아야 합니다.',
        'array' => ':attribute 항목은 배열 요소 수가 :value 크거나 같아야 합니다.',
    ],
    'image' => ':attribute 항목은 이미지만 가능합니다.',
    'in' => ':attribute 올바른 값이 아닙니다.',
    'in_array' => ':attribute 항목의 값이 :other 에 존재하지 않습니다.',
    'integer' => ':attribute 항목은 정수만 가능ㄴ합니다.',
    'ip' => ':attribute m항목은 올바른 IP 주소여야 합니다.',
    'ipv4' => ':attribute 항목은 올바른 IPv4 주소여야 합니다.',
    'ipv6' => ':attribute 항목은 올바른 IPv6 주소여야 합니다.',
    'json' => ':attribute 항목은 JSON 문자열 형식어야 합니다.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'numeric' => ':attribute 항목은 :value 보다 작야 합니다.',
        'file' => ':attribute 항목의 파일 용량은 :valueKB 보다 작아 합니다.',
        'string' => ':attribute 항목은 문자 길이가 :value 보다 작아야 합니다.',
        'array' => ':attribute 항목은 배열 요소 수가 :value 작아야 합니다.',
    ],
    'lte' => [
        'numeric' => ':attribute 항목은 :value 보다 작거나 같아야 합니다.',
        'file' => ':attribute 항목의 파일 용량은 :valueKB 보다 작거나 같아야 합니다.',
        'string' => ':attribute 항목은 문자 길이가 :value 보다 작거나 같아야 합니다.',
        'array' => ':attribute 항목은 배열 요소 수가 :value 작거나 같아야 합니다.',
    ],
    'mac_address' => ':attribute 필드는 올바른 MAC Address 여야 합니다.',
    'max' => [
        'numeric' => ':attribute 항목은 최대 :max를 초과할 수 없습니다.',
        'file' => ':attribute 항목은 최대 용량 :maxKB를 초과할 수 없습니다.',
        'string' => ':attribute 항목은 문자열 최대 길이 :max문자를 초과할 수 없습니다.',
        'array' => ':attribute 항목은 최대 :max개를 초과할 수 없습니다.',
    ],
    'max_digits' => ':attribute 필드는 :max 자리를 넘지 않아야 합니다.',
    'mimes' => ':attribute 파일 형식이 :values이어야 합니다.',
    'mimetypes' => ':attribute 파일 형식이 :values이어야 합니다.',
    'min' => [
        'numeric' => ':attribute 항목은 최소 :min 이상이어야 합니다.',
        'file' => ':attribute 항목은 최소 용량 :minKB 이상이어야 합니다.',
        'string' => ':attribute 항목은 최소 길이 :min 문자 이상이어야 합니다..',
        'array' => 'attribute 항목은 최소 :min개 이상이어야 합니다.',
    ],
    'min_digits' => ':attribute 필드는 :min 자리 이상이어야 합니다.',
    'missing' => 'The :attribute 필드의 값이 없어야 합니다.',
    'missing_if' => ':other가 :value인 경우 :attribute 필드의 값은 없어야 합니다.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => '선택된 :attribute 항목은 올바르지 않습니다.',
    'not_regex' => ':attribute 항목은 형식이 올바르지 않습니다.',
    'numeric' => ':attribute 항목은 숫자만 가능합니다.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => ':attribute 항목은 존재해야 합니다.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => ':attribute 항목은 형식이 올바르지 않습니다.',
    'required' => ':attribute 항목은 반드시 입력되어야 합니다.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => ':attribute :other의 값이 :value인 경우 반드시 입력되어야 합니다.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => ':attribute 항목은 :other의 값이 :values에 없는 경우 반드시 입력되어야 합니다.',
    'required_with' => ':attribute 항목은 :values가 있는 경우 반드시 입력되어야 합니다.',
    'required_with_all' => ':attribute 항목은 :values가 모두 있는 경우 반드시 입력되어야 합니다.',
    'required_without' => ':attribute 항목은 :values 가 없는 경우 반드시 입력되어야 합니다.',
    'required_without_all' => ':attribute 항목은 :values 중 하나가 표시된 경우 반드시 입력해야 합니다.',
    'same' => ':attribute와 :other는 서로 일치해야 합니다.',
    'size' => [
        'numeric' => ':attribute 항목은 :size 이어야 합니다.',
        'file' => ':attribute 항목은 :sizeKB 이어야 합니다.',
        'string' => ' :attribute 항목은 :size 문자 이어야 합니다.',
        'array' => ':attribute 항목은 :size개여야 합니다.',
    ],
    'starts_with' => ':attribute 항목은 :values로 시작해야 합니다.',
    'string' => ':attribute 항목은 문자열이어야 합니다.',
    'timezone' => ':attribute 항목은 올바른 타임존이어야 합니다.',
    'unique' => ':attribute 항목의 값은 유일해야 합니다.',
    'uploaded' => ':attribute 항목의 파일 업로드 실폐했습니다.',
    'uppercase' => ':attribute 필드는 대문자여야 합니다.',
    'url' => ':attribute 항목은 올바른 URL 형식이 아닙니다.',
    'ulid' => 'The :attribute 필드는 올바른 ULID 이어야 합니다.',
    'uuid' => ':attribute 항목은 올바른 UUID 형식이 아닙니다.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
