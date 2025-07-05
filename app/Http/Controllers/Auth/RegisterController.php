<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * 회원가입 폼 표시
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * 새로운 사용자 계정 생성
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => '이름은 필수 입력 항목입니다.',
            'name.max' => '이름은 최대 255자까지 입력 가능합니다.',
            'email.required' => '이메일은 필수 입력 항목입니다.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'password.required' => '비밀번호는 필수 입력 항목입니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'author', // 기본 역할
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }

    /**
     * 사용자명 중복 확인 (AJAX)
     */
    public function checkUsername(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $exists = User::where('name', $request->name)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? '이미 사용 중인 사용자명입니다.' : '사용 가능한 사용자명입니다.'
        ]);
    }

    /**
     * 이메일 중복 확인 (AJAX)
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? '이미 사용 중인 이메일입니다.' : '사용 가능한 이메일입니다.'
        ]);
    }
}