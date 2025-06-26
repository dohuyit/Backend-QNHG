<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ActivateAccountController extends Controller
{
     public function activate(string $token)
     {
          $user = User::where('activation_token', $token)->first();

          if (!$user) {
               return response()->view('errors.404', [], 404);
          }

          $user->status = User::STATUS_ACTIVE;
          $user->email_verified_at = now();
          $user->activation_token = null;
          $user->save();

          return redirect('http://localhost:5173/admin/login?message=Tài khoản đã được kích hoạt');

     }
}