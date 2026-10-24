<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use App\Models\User;
class AuthController extends Controller {
 public function loginForm(){return view('auth.login');}
 public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required|string']);if(!Auth::attempt($d,$r->boolean('remember'))){return back()->withErrors(['email'=>'ایمیل یا گذرواژه صحیح نیست.'])->onlyInput('email');}$r->session()->regenerate();return redirect()->intended(route('home'));}
 public function registerForm(){return view('auth.register');}
 public function register(Request $r){$d=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users,email','phone'=>'nullable|string|max:20','password'=>'required|string|min:12|confirmed']);$u=User::create($d);Auth::login($u);$r->session()->regenerate();return redirect()->route('home');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');}
}
