<?php

return function ()
{
    http_response_code(404);
    echo "Page not found";
    return true;
};