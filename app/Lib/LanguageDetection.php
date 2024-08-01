<?php

namespace App\Lib;

class LanguageDetection {
    private ?string $request_language = null;
    private ?string $browser_language = null;
    private ?string $session_language = null;
    private ?string $app_language = null;

    private $language_label_groups = [
        'ko-KR' => 'ko', 'ko' => 'ko', 'en-US' => 'en', 'en' => 'en',
        'fr-CH' => 'fr', 'fr' => 'fr',
    ];

    /**
     * 언어를 검출하고 설정한다.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct() {
        $this->app_language = app()->getLocale();
        $this->browser_language = $this->getBrowserLanguage();
        $this->session_language = session()->get('language');
        $this->request_language = $this->getRequestLanguage();

        if($this->request_language) {
            app()->setLocale($this->request_language);
            $this->saveLanguageToSession($this->request_language);
        }
        elseif($this->session_language) app()->setLocale($this->session_language);
        elseif($this->browser_language) app()->setLocale($this->browser_language);
    }

    /**
     * 브라우즈 언어를 리턴한다.
     * @return string|null
     */
    public function getBrowserLanguage() : ?string {
        $request = request();
        if(!$request) return null;

        $languages = $request->server('HTTP_ACCEPT_LANGUAGE');
        $language = trim(explode(',', $languages)[0]);
        if(strpos($language, ';') >= 0) $language = explode(';', $language)[0];
        return isset($this->language_label_groups[$language]) ? $this->language_label_groups[$language] : app()->getLocale();
    }

    /**
     * 요청 언언를 리턴한다.
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getRequestLanguage() : ?string {
        return request()->get('language');
    }

    /**\
     * 세션에 언어 정보를 저장한다.
     * @param string $locale
     * @return void
     */
    public function saveLanguageToSession(string $locale) : void {
        session()->put('language', $locale);
    }
}
