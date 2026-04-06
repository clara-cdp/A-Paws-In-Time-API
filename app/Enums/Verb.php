<?php

namespace App\Enums;

enum Verb: string
{
    case LOOK_AT = 'LOOK AT';
    case USE = 'USE';
    case PICK_UP = 'PICK UP';
    case GO_TO = 'GO TO';
    case OPEN = 'OPEN';
    case RESCUE = 'RESCUE';
    case PULL = 'PULL';
    case PUSH = 'PUSH';
}
