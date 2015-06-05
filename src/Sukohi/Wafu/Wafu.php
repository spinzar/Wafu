<?php namespace Sukohi\Wafu;

use Carbon\Carbon;

class Wafu {

    // Week Name

    private $_week_names = [
        '0' => '日',
        '1' => '月',
        '2' => '火',
        '3' => '水',
        '4' => '木',
        '5' => '金',
        '6' => '土'
    ];

    private $_month_names = [
        '1' => '1月',
        '2' => '2月',
        '3' => '3月',
        '4' => '4月',
        '5' => '5月',
        '6' => '6月',
        '7' => '7月',
        '8' => '8月',
        '9' => '9月',
        '10' => '10月',
        '11' => '11月',
        '12' => '12月'
    ];

    public function weekNames($key_flag = true) {

        return ($key_flag) ? $this->_week_names : array_values($this->_week_names);

    }

    public function weekName($week_no) {

        if(is_object($week_no) && get_class($week_no) == 'Carbon\Carbon') {

            $week_no = $week_no->dayOfWeek;

        }

        return array_get($this->_week_names, $week_no, '');

    }

    public function hasWeekName($week_no) {

        $week_name = $this->weekName($week_no);
        return !empty($week_name);

    }
    
    public function monthNames($key_flag = true) {

        return ($key_flag) ? $this->_month_names : array_values($this->_month_names);
        
    }
    
    public function monthName($month_no) {

        if(is_object($month_no) && get_class($month_no) == 'Carbon\Carbon') {

            $month_no = $month_no->month;

        }

        return array_get($this->_month_names, $month_no, '');
        
    }

    public function hasMonthName($month_no) {

        $month_name = $this->monthName($month_no);
        return !empty($month_name);

    }

    // Date

    public function date($format, $time = '') {

        if(!is_object($time) || get_class($time) != 'Carbon\Carbon') {

            $time = new Carbon($time);

        }

        $format = preg_replace_callback('|\{([YymndjGgHhiswaEFf])\}|', function($matches) use($time) {

            $text = '';
            $symbol = $matches[1];

            switch($symbol) {

                case 'Y':
                    $text = 'Y年';
                    break;
                case 'y':
                    $text = 'y年';
                    break;
                case 'm':
                    $text = 'm月';
                    break;
                case 'n':
                    $text = 'n月';
                    break;
                case 'd':
                    $text = 'd日';
                    break;
                case 'j':
                    $text = 'j日';
                    break;
                case 'G':
                    $text = 'G時';
                    break;
                case 'g':
                    $text = 'g時';
                    break;
                case 'H':
                    $text = 'H時';
                    break;
                case 'h':
                    $text = 'h時';
                    break;
                case 'i':
                    $text = 'i分';
                    break;
                case 's':
                    $text = 's秒';
                    break;
                case 'w':
                    $text = $this->weekName($time->dayOfWeek);
                    break;
                case 'a':
                    $text = ($time->format('a') == 'am') ? '午前' : '午後';
                    break;
                case 'E':
                    $text = $this->japaneseEraYear($time->year);
                    break;
                case 'F':
                    $text = 'Y年m月d日（'. $this->weekName($time->dayOfWeek) .'） H時i分';
                    break;
                case 'f':
                    $text = 'Y年m月d日（'. $this->weekName($time->dayOfWeek) .'） H:i';
                    break;

            }

            return $text;

        }, $format);

        return $time->format($format);

    }

    // Convert Japanese Date

    public function convertJapaneseDate($date) {

        $date = trim(mb_convert_kana($date, 'ns'));
        $week_name_pattern = implode('|', $this->_week_names);
        $replacements = [
            '!（('. $week_name_pattern .')）!',
            '!\(('. $week_name_pattern .')\）!'
        ];
        $date = preg_replace($replacements, '', $date);
        $patterns = [
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日 ([\d]+)時([\d]+)分([\d]+)!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日 ([\d]+)時([\d]+)分!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日 ([\d]+)時!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日 ([\d]+):([\d]+):([\d]+)!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日 ([\d]+):([\d]+)!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月([\d]+)日!',
            '!((明治|大正|昭和|平成)[\d]+年)([\d]+)月!',
            '!(明治|大正|昭和|平成[\d]+)年!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+) ([\d]+)時([\d]+)分([\d]+)!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+) ([\d]+)時([\d]+)分!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+) ([\d]+)時!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+) ([\d]+):([\d]+):([\d]+)!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+) ([\d]+):([\d]+)!',
            '!((M|T|S|H)[\d]+).([\d]+).([\d]+)!',
            '!((M|T|S|H)[\d]+).([\d]+)!',
            '!((M|T|S|H)[\d]+)!',
        ];

        foreach ($patterns as $index => $pattern) {

            if(preg_match($pattern, $date, $matches)) {

                $year = $this->commonEraYear($matches[1]);
                $matches_count = count($matches);

                if($matches_count == 8) {

                    return Carbon::create($year, $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]);

                } else if($matches_count == 7) {

                    return Carbon::create($year, $matches[3], $matches[4], $matches[5], $matches[6], 0);

                } else if($matches_count == 6) {

                    return Carbon::create($year, $matches[3], $matches[4], $matches[5], 0, 0);

                } else if($matches_count == 5) {

                    return Carbon::create($year, $matches[3], $matches[4], 0, 0, 0);

                } else if($matches_count == 4) {

                    return Carbon::create($year, $matches[3], 1, 0, 0, 0);

                } else if($matches_count == 3 || $matches_count == 2) {

                    return Carbon::create($year, 1, 1, 0, 0, 0);

                }

            }

        }

        return false;

    }

    // Gender

    private $_genders = [
        '1' => '男性',
        '2' => '女性',
        '0' => 'その他',
    ];

    public function genders($other_flag = false) {

        $genders = $this->_genders;

        if(!$other_flag) {

            unset($genders[0]);

        }

        return $genders;

    }

    public function gender($gender_id) {

        if($this->hasGender($gender_id)) {

            return $this->_genders[$gender_id];

        }

        return '';

    }

    public function hasGender($gender_id) {

        return isset($this->_genders[$gender_id]);

    }

    // Prefectures

    private $_prefectures = [
        '1' => '北海道',
        '2' => '青森県',
        '3' => '岩手県',
        '4' => '宮城県',
        '5' => '秋田県',
        '6' => '山形県',
        '7' => '福島県',
        '8' => '茨城県',
        '9' => '栃木県',
        '10' => '群馬県',
        '11' => '埼玉県',
        '12' => '千葉県',
        '13' => '東京都',
        '14' => '神奈川県',
        '15' => '新潟県',
        '16' => '富山県',
        '17' => '石川県',
        '18' => '福井県',
        '19' => '山梨県',
        '20' => '長野県',
        '21' => '岐阜県',
        '22' => '静岡県',
        '23' => '愛知県',
        '24' => '三重県',
        '25' => '滋賀県',
        '26' => '京都府',
        '27' => '大阪府',
        '28' => '兵庫県',
        '29' => '奈良県',
        '30' => '和歌山県',
        '31' => '鳥取県',
        '32' => '島根県',
        '33' => '岡山県',
        '34' => '広島県',
        '35' => '山口県',
        '36' => '徳島県',
        '37' => '香川県',
        '38' => '愛媛県',
        '39' => '高知県',
        '40' => '福岡県',
        '41' => '佐賀県',
        '42' => '長崎県',
        '43' => '熊本県',
        '44' => '大分県',
        '45' => '宮崎県',
        '46' => '鹿児島県',
        '47' => '沖縄県'
    ];

    public function prefectures() {

        return $this->_prefectures;

    }

    public function prefecture($prefecture_id) {

        if($this->hasPrefecture($prefecture_id)) {

            return $this->_prefectures[$prefecture_id];

        }

        return '';

    }

    public function hasPrefecture($prefecture_id) {

        return (isset($this->_prefectures[$prefecture_id]));

    }

    public function prefectureId($prefecture_name) {

        $id = array_search($prefecture_name, $this->_prefectures);

        if($id === false) {

            return -1;

        }

        return $id;

    }

    // Regions

    private $_regions = [
        '1' => '北海道',
        '2' => '東北',
        '3' => '関東',
        '4' => '中部',
        '5' => '関西',
        '6' => '中国',
        '7' => '四国',
        '8' => '九州'
    ];

    private $_region_prefecture_ids = [
        '1' => [1],
        '2' => [2, 3, 4, 5, 6, 7],
        '3' => [8, 9, 10, 11, 12, 13, 14],
        '4' => [15, 16, 17, 18, 19, 20, 21, 22, 23],
        '5' => [24, 25, 26, 27, 28, 29, 30],
        '6' => [31, 32, 33, 34, 35],
        '7' => [36, 37, 38, 39],
        '8' => [40, 41, 42, 43, 44, 45, 46, 47]
    ];

    public function regions() {

        return $this->_regions;

    }

    public function region($region_id) {

        if($this->hasRegion($region_id)) {

            return $this->_regions[$region_id];

        }

        return '';

    }

    public function hasRegion($region_id) {

        return (isset($this->_regions[$region_id]));

    }

    public function regionId($name) {

        $id = array_search($name, $this->_regions);

        if($id === false) {

            return -1;

        }

        return $id;

    }

    public function regionPrefectureIds() {

        return $this->_region_prefecture_ids;

    }

    public function japaneseEra($year) {

        $era_name = '';
        $era_year = 0;

        if ($year >= 1989) {

            $era_name = '平成';
            $era_year = $year - 1988;

        } elseif ($year >= 1926) {

            $era_name = '昭和';
            $era_year = $year - 1925;

        } elseif ($year >= 1912) {

            $era_name = '大正';
            $era_year = $year - 1911;

        } else {

            $era_name = '明治';
            $era_year = $year - 1867;

        }

        $era_year_corrected = ($era_year == 1) ? '元' : $era_year;

        return [
            'era_name' => $era_name,
            'era_year' => $era_year,
            'era_full' => $era_name . $era_year_corrected .'年'
        ];

    }

    public function japaneseEraYear($year) {

        $era_values = $this->japaneseEra($year);
        return $era_values['era_full'];

    }

    public function commonEraYear($japanese_era_year) {

        $year = -1;
        $japanese_era_year = mb_convert_kana($japanese_era_year, 'n');

        if(preg_match('!(明治|大正|昭和|平成|M|T|S|H)([0-9]{1,2}|元)[年]?!', $japanese_era_year, $matches)) {

            $era_name = $matches[1];
            $era_year = ($matches[2] == '元') ? 1 : $matches[2];

            if($era_name == '平成' || $era_name == 'H') {

                $year = $era_year + 1988;

            } else if($era_name == '昭和' || $era_name == 'S') {

                $year = $era_year + 1925;

            } else if($era_name == '大正' || $era_name == 'T') {

                $year = $era_year + 1911;

            } else if($era_name == '明治' || $era_name == 'M') {

                $year = $era_year + 1867;

            }

        }

        return $year;

    }

    public function nationalDays($start_date = '', $end_date = '', $cache_flag = true) {

        $date_correct = function($date) {

            if(gettype($date) =='object' && get_class($date) == 'Carbon\Carbon') {

                return $date->format('Y-m-d');

            }
            return $date;

        };

        if(empty($start_date)) {

            $start_date = date('Y-01-01');

        }

        if(empty($end_date)) {

            $end_date = date('Y-12-31');

        }

        $start_date = $date_correct($start_date);
        $end_date = $date_correct($end_date);
        $cache_key = 'japanese_national_days_'. $start_date .'_'. $end_date;

        if(!$cache_flag && \Cache::has($cache_key)) {

            \Cache::forget($cache_key);

        }

        return \Cache::rememberForever($cache_key, function() use($start_date, $end_date) {

            $national_days = [];
            $url = 'https://www.google.com/calendar/feeds/'. urlencode('japanese__ja@holiday.calendar.google.com') .'/public/basic'.
                '?start-min='. date($start_date .'\T00:00:00\Z') .
                '&start-max='. date($end_date .'\T00:00:00\Z') .'&max-results=100&alt=json';

            if($json = file_get_contents($url)) {

                $json_data = json_decode($json, true);

                if(!empty($json_data['feed']['entry'])) {

                    foreach ($json_data['feed']['entry'] as $value) {

                        $title = $value['title']['$t'];
                        $date = preg_replace('#\A.*?(2\d{7})[^/]*\z#i', '$1', $value['id']['$t']);
                        $date2 = preg_replace('/\A(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $date);
                        $national_days[$date2] = $title;

                    }

                    ksort($national_days);
                    return $national_days;

                }

            }

            return $national_days;

        });

    }

}