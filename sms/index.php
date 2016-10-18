<?php

require ("../common.php");

$smsReq = new smsRequest();

if (isset($_REQUEST['source']))
    $smsReq->setSender($_REQUEST['source']);
else
    $smsReq->setSender($_REQUEST["sender"]);

if (isset($_REQUEST["message"]))
    $smsReq->setMessage($_REQUEST["message"]);
else
    $smsReq->setMessage($_REQUEST["content"]);


if ($smsReq->sender->validMobile)
{
    $firstword = $smsReq->messageWords[0];
    error_log("*".$firstword."*");
    switch ($firstword) {
        case "security":
            $smsReq->security();
            break;

        case "new":
            $smsReq->newPassword();
            break;

        case "help":
            $smsReq->help();
            break;

        default:
            if (preg_match('/^[0-9]{4}$/', $firstword)) {
                $smsReq->dailycode();
            } else if (preg_match('/^[0-9]{6}$/', $firstword)) {
                $smsReq->verify();
            } else {
                $smsReq->other();
            }
            break;

    }


} else
{
    error_log("SMS: Invalid number " . $smsReq->sender->text);
}

?>
