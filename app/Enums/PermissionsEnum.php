<?php

namespace App\Enums;

enum PermissionsEnum : string{
   case CreatePlayers = 'CreatePlayers';
   case SeeUsers = 'SeeUsers';
   case EditUser = 'EditUser';
   case DeleteUser = 'DeleteUser';
   case BlockUser = 'Blockuser';
   
}