<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Session;

class AuthController extends Controller
{
    public function viewlogin()
    {
        return view('auth.login');
    }
    public function viewregister()
    {
        return view('auth.register');
    }
    public function login()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        } else {
            return redirect('/login');
        }
    }
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'confirm-password' => 'required|same:password'
        ]);
        $data = $request->except('confirm-password', 'password');
        $data['password'] = Hash::make($request->password);
        User::create($data);
        return redirect('/login');
    }
    public function loginPost(Request $request)
    {

        $email = $request->email;
        $password = $request->password;
        if (Auth::attempt($request->only('email', 'password'))) {
            Session::flash('sukses', 'Berhasil Login');
            return redirect('/dashboard');
        } else {
            Session::flash('alert', 'Email atau password salah'); //memasang
            return redirect('/login');
        }
    }

    public function logout()
    {
        Session::flash('alert', 'Kamu sudah logout');
        return redirect('/login');
        Auth::logout();
    }
}
