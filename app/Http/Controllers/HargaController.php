<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HargaRental;
class HargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $harga = HargaRental::all();
        return view('harga.index', compact('harga'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $harga = HargaRental::find($id);
        return view('harga.edit', compact('harga'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $harga = HargaRental::find($id);
        $harga->update($request->all());
        return redirect()->route('harga.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $produk = HargaRental::where('id', $id)->get();

        $produk->each->delete();
        return redirect()->route('harga.index');
    }
}
