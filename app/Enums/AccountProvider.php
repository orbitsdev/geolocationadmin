<?php

namespace App\Enums;

enum AccountProvider : string
{
    case CREDENTIALS = 'CREDENTIALS';
    case GOOGLE = 'GOOGLE';
    case FACEBOOK = 'FACEBOOK';
}
