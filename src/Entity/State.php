<?php

namespace App\Entity;

enum State: string
{
    case Pending = 'pending';
    case Launched = 'launched';
    case Cancelled = 'cancelled';
}
