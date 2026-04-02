<?php

namespace App\Enums;

enum PermissionsEnum : string{
   case ViewUsers = 'View_users';      
   case EditUsers = 'Edit_users';      
   case DeleteUsers = 'Delete_users';  
   case BlockUsers = 'Block_users';    
   
}