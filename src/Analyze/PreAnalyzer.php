<?php
namespace Shieldfy\Analyze;
use Shieldfy\Shieldfy;
class PreAnalyzer
{
    public static function run($key,$value){

        if($value === '') return false; //dont test against empty values
        if(is_numeric($value)) return false; //dont test against integer values
        $rules = json_decode(file_get_contents(Shieldfy::getRootDir().'/data/pre_rules'),1);
        foreach($rules as $key=>$rule){
            if(preg_match('/'.$rule[0].'/isU',$value)){
                return true;
            }
        }
        return false;
    }
}
