<?php

namespace App\Traits\Common;

use Illuminate\Support\Carbon;

trait DataConvert {
    /**
     * 문자열을 날짜로 변환한다.
     * @param string $date
     * @return Carbon|null
     */
    public function getDateFromString(?string $date) : ?Carbon {
        $tdate = trim($date);
        if(!$tdate) return null;

        $formats = [
            'Y-m-d', 'Y-n-j', 'Y.m.d', 'Y.n.j', 'm.d.Y', 'n.j.Y', 'Y,m,d', 'Y,n,j',
            'Y/m/d', 'Y/n/j', 'm/d/Y', 'n/j/Y', 'M.j.Y', 'j.M.Y', 'F.j.Y', 'j.F.Y',
            'M j, Y', 'j M, Y', 'F j, Y', 'j F, Y', 'M j Y', 'j M Y', 'F j Y', 'j F Y'
        ];

        foreach($formats as $format) {
            try {
                $t = Carbon::createFromFormat($format, $tdate);
                return $t;
            } catch (\Exception $e) {continue;}
        }

        $tdate = preg_replace('/\s*/', '', $tdate);
        if(!$tdate) return null;

        foreach($formats as $format) {
            try {
                $t = Carbon::createFromFormat($format, $tdate);
                return $t;
            } catch (\Exception $e) {continue;}
        }
        return null;
    }

    /**
     * 지정 배열 요소에 값을 설정한다.
     * @param string $target
     * @param mixed $value
     * @return void
     */
    public function setValue(string $target, mixed $value) : void {
        $hlabels = explode('.', $target);
        $cnt = count($hlabels);

        $t = &$this->info;
        for($i = 0; $i < $cnt; $i++) {
            if(array_key_exists($hlabels[$i], $t)) {
                $t = &$t[$hlabels[$i]];
                if($i == ($cnt - 1)) $t = $value;
            }
        }
    }
}
