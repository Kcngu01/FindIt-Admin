<?php 

namespace App\Services;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\Auth;

class CustomDatabaseSessionHandler extends DatabaseSessionHandler
{
    protected function addUserInformation(&$data)
    {
        if ($this->container->bound('auth')) {
            // $user = Auth::user();
        //    if($user){
        //     $data['admin_id'] = $user->id;
        //    }
        //     return $this;

            $user = Auth::guard('admin')->user() ?: Auth::guard('student')->user();
            if ($user) {
                if ($user instanceof \App\Models\Admin) {
                    $data['admin_id'] = $user->id;
                    // $data['student_id'] = null;
                } elseif ($user instanceof \App\Models\Student) {
                    // $data['student_id'] = $user->id;
                    // $data['admin_id'] = null;
                }
            }

            return $this;
        }
    }
}


