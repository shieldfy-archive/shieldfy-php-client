<?php
namespace Shieldfy\Analyze;
use Shieldfy\Shieldfy;
class SoftRules
{
    private static $result = [];

    public static function run($method,$key,$value)
    {
        $rules = json_decode(file_get_contents(Shieldfy::getRootDir().'/data/soft_rules'),1);
        $length = 0;
        $score = 0;
        $keys = [];
        foreach($rules as $key=>$rule){
            if(preg_match('/'.$rule[0].'/isU',$value,$matches)){
                $length += strlen($matches[0]);
                $score += $rule[1];
                $keys[] = $key;
            }
        }



        if($score > 0) {

            if( ($length >= (strlen($value) / 3))){
				$score += 5;
			}

			if( ($method == 'GET') ){
        	    $score += 5;
            }

        }

        self::$result = [
            'score'=>$score,
            'keys'=>$keys
        ];
        if($score > 0){
            return true;
        }

        return false;
    }

    public static function getResult(){
        return self::$result;
    }
}
