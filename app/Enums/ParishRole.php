<?php

namespace App\Enums;

enum ParishRole: string
{
    case Member = 'member';
    case Admin = 'admin';
    case AdminNoVisits = 'admin_no_visits';
}
