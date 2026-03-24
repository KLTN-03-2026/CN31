<?php

namespace App\Http\Controllers;

use App\Enums\TrangThaiPhieu;
use App\Models\PhieuYeuCau;
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

    // on_tap
    // public function index(){


    // $user =Auth::user();


    // $query = PhieuYeuCau::Acssess($user);

    // $status = [
    //     'total' => (clone $query) ->count(),
    //     'cho_duyet' => (clone $query)->where('trang_thai','cho_duyet')->count(),
    //     'da_duyet'  => (clone $query)->where('trang_thai','da_duyet')->count(),
    //     'tu_choi' => (clone $query)->where('trang_thai','tu_choi')->count(),

    // ];

    // // phieu gan day
    // $phieuGanDay = (clone $query)->with('nguoiTao')
    //            ->latest('created_at')
    //            ->take(10)
    //            ->get()
    //            ->map( function ($phieu){
    //             return [

    //             ];
    //            });
    // }

    // public function register(Request $request){
    //     // Tao bien kiem tra du lieu
    //     $check = $request->validate([

    //     'name'      => ['required','string','max:255'],
    //     'avatar'    => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
    //     'email'     => ['required','email','unique:users'],
    //     'password'  => ['required','min:3','confirmed']
    //     ]);
    //     if($check){
    //         $user = User::created($check);
    //         Auth::login($user);
    //         return redirect()->intended('dashboard');
    //     }
    //     return back()->withErrors($check)->onlyInput();
    // }

    // public function login(Request $request){
    //     $check = $request->validate([
    //         'email'    => ['required','email'],
    //         'password' => ['required']
    //     ]);
    //     if(Auth::attempt($check)){
    //         $request->session()->regenerate();
    //         return redirect()->intended('dashboard');

    //     }
    //     return back()->withErrors([
    //         'email' => 'Mat khau hoac email khong chinh xac !'
    //     ])->onlyInput('email');
    // }

    // public function logout(Request $request){
    //     Auth::logout();
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();
    //     return redirect()->route('login');
    // }

public function index(){
// Lay thong tin nguoi dung hien tai
$user = Auth::user();
$query = PhieuYeuCau::forUserAccess($user);

$stats = [
    'total'     => (clone $query)->count(),
    'cho_xuly'  => (clone $query)->whereIn('trang_thai',[
        TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
        TrangThaiPhieu::CHO_GIAM_DOC_DUYET,
        TrangThaiPhieu::CHO_THANH_TOAN
    ])->count(),
    'hoan_tat' => (clone $query)->where('trang_thai',TrangThaiPhieu::DA_THANH_TOAN)->count(),
    'tha_bai' => (clone $query)->where('trang_thai',TrangThaiPhieu::TU_CHOI)->count(),

];

$rphieuGanDay = (clone $query)->with('nguoiTao')
             ->orderBy('created_at','desc')
             ->paginate(6)
             ->through(function ($phieu){
                return [
                    'id' => $phieu->id,
                    'ma_phieu'  => $phieu->ma_phieu,
                    'tieu_de'   => $phieu->tieu_de,
                    'nguoi_tao' =>$phieu->nguoiTao->name,
                    'ngay_tao' => $phieu->created_at->format('d/m/Y H:i'),
                ];
             });

       return Inertia::render('Dashboard/Dashboard',[
        'stats' => $stats,
        'recentRequests' => $rphieuGanDay
       ]);

}

}




