<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Test extends Controller
{

    // public function Login(Request $request){
    //     $check = $request->validate([
    //         'email' => ['required','email'],
    //         'password' => ['required','min:3']
    //     ]);
    //     if(Auth::attempt($check) ){
    //         $request->session()->regenerate();
    //         return redirect() ->intended('dashboard');

    //     }
    //     return back()->withErrors([
    //         'email' => 'The provided credentials do not match our records.',
    //     ])->onlyInput();
    // }
    // public function Register(Request $request){
    //     $check = $request->validate([
    //         'name' => ['required','string','max:255'],
    //         'avatar' => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
    //         'email' => ['required','email','unique:users,email'],
    //         'password' => ['required','min:3','confirmed']
    //     ]);
    //     if($check){
    //         $user = User::create($check);
    //         Auth::login($user);
    //         return redirect()->intended('dashboard');
    //     }
    // }
    // public function Logout(Request $request){
    //     Auth::logout();
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();
    //     return redirect(route('login'));
    // }



}




