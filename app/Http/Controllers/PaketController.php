<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;

class PaketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $paket = Paket::all();
        return view("paket.index", compact("paket"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("paket.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        Paket::create($request->all());
        return redirect()->route('paket.index');

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
        $paket = Paket::find($id);
        return view('paket.edit', compact('paket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $paket = Paket::find($id);
        $paket->update($request->all());
        return redirect()->route('paket.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $produk = Paket::where('id_paket', $id)->get();

        $produk->each->delete();
        return redirect()->route('paket.index');
    }
}
