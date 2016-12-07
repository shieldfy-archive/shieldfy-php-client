<?php

namespace Shieldfy;

class Action
{
    const BLOCKSTATUS = 403;
    const BLOCKMESSAGE = 'Unauthorize Action :: Shieldfy Web Shield';
    const BLOGKHTML = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Access Denied</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"><!--[if lt IE 9]><script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]--></head><body><div class="container"><div class="row"><div class="col-sm-8 col-sm-offset-2"><div class="well" style="margin-top:80px;padding:40px;"><div class="row"><div class="col-sm-4"><img src="http://shieldfy.com/assets/img/block-sign.png" class="img-responsive"></div><div class="col-sm-8"><h1>Whooops!</h1><h4>Your request blocked for security reasons</h4><p>if you believe that your request shouldn\'t be blocked contact the administrator <br /><br /><br /> Incident ID : {incidentId} </p><hr/>Protected By <a href="http://shieldfy.com" target="_blank">Shieldfy</a> &trade; Web Shield </div></div></div></div></div></div></body></html>';

    /**
     * block if threat happens.
     *
     * @param string $incidentId
     *
     * @return void
     */
    public function block($incidentId = '')
    {
        @header($_SERVER['SERVER_PROTOCOL'].' '.self::BLOCKSTATUS.' '.self::BLOCKMESSAGE);
        @die(str_replace('{incidentId}', $incidentId, self::BLOGKHTML));
        exit; //caution if die failed for any reasons
    }
}
