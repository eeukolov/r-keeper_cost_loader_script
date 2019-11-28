<?php

function logger($message)
{
    $date = new DateTime();
    $d = $date->format('Y-m-d');
    $t = $date->format('Y-m-d H:i:s');
    file_put_contents('./log/log_' . $d . '.txt', $t . ' ' . $message . "\r\n", FILE_APPEND | LOCK_EX);
}