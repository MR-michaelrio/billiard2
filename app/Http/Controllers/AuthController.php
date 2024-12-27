<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    function register(){
        return view('register');
    }
    public function registerPost(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->save();
        return back()->with('success', 'Register successfully');
    }
    public function login()
    {
        return view('login');
    }
    
    public function loginPost(Request $request)
    {
        $credetials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        if (Auth::attempt($credetials)) {
            return redirect('/')->with('success', 'Login berhasil');
        }
        return back()->with('error', 'Email or Password salah');
    }

    // public function loginPost(Request $request)
    // {
    //     // Validate email, password, and coordinates
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //     ]);

    //     $credentials = [
    //         'email' => $request->email,
    //         'password' => $request->password,
    //     ];

    //     // Attempt login
    //     if (Auth::attempt($credentials)) {
    //         // User is authenticated
    //         $user = Auth::user();

    //         // Check if the user is an admin
    //         if ($user->role === 'admin') {
    //             // Skip coordinate validation for admins
    //             return redirect('/')->with('success', 'Login berhasil');
    //         }

    //         // Proceed with coordinate validation for employees
    //         if ($user->role === 'employee') {
    //             // User's current coordinates
    //             $userLatitude = $request->latitude;
    //             $userLongitude = $request->longitude;

    //             // User's allowed coordinates from the database
    //             $storedLatitude = $user->latitude;
    //             $storedLongitude = $user->longitude;

    //             // Calculate the distance between the user's location and stored location (in kilometers)
    //             $distance = $this->calculateDistance($userLatitude, $userLongitude, $storedLatitude, $storedLongitude);

    //             // Define a maximum allowed distance (e.g., 5 km)
    //             $maxDistance = 5;

    //             if ($distance <= $maxDistance) {
    //                 return redirect('/')->with('success', 'Login berhasil');
    //             } else {
    //                 Auth::logout();
    //                 return back()->with('error', 'Login denied. You are too far from the allowed location.');
    //             }
    //         }

    //         // If no role match (just in case)
    //         return back()->with('error', 'Role not recognized.');
    //     }

    //     // If login fails
    //     return back()->with('error', 'Email or Password salah');
    // }

    // private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    //     $earthRadius = 6371; // Radius of the earth in kilometers
    //     $latDiff = deg2rad($lat2 - $lat1);
    //     $lonDiff = deg2rad($lon2 - $lon1);
    //     $a = sin($latDiff / 2) * sin($latDiff / 2) +
    //         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
    //         sin($lonDiff / 2) * sin($lonDiff / 2);
    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    //     return $earthRadius * $c; // Distance in kilometers
    // }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    

}
