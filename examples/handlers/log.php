<?php

return function ()
{
    $url = $_SERVER['REQUEST_URI'] ?? "";
    $moment = date("Y-m-d H:i:s");
    
    // show in console log
    echo "<script>console.log('{$moment} - {$url}');</script>";
    return true;
};