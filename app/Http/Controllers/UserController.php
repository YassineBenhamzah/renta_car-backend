<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Prevent deleting self?
        if (auth()->id() == $id) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }
        $user->delete();
        return response()->noContent();
    }
}
