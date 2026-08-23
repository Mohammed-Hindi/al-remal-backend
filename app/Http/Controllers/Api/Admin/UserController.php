<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Mail\NewEmployeeCredentials;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'must_change_password', 'created_at']);

        return response()->json(['data' => $users]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $plainPassword = Str::password(10);

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'password' => $plainPassword,
            'must_change_password' => true,
        ]);

        Mail::to($user->email)->send(new NewEmployeeCredentials($user, $plainPassword));

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح، وتم إرسال بيانات الدخول إلى بريد الموظف',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'role', 'must_change_password', 'created_at']),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->role === \App\Enums\UserRole::Admin) {
            return response()->json([
                'message' => 'لا يمكن حذف حساب المدير',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'تم حذف حساب الموظف بنجاح',
        ]);
    }
}
