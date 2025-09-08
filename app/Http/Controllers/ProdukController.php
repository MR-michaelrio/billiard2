<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Cart;
use App\Models\Rental;
use App\Models\OrderItem;
use App\Models\Order;
class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = Produk::where('qty', '>', 0)->get();
        $rental = Rental::all();
        return view('produk.index', compact('products','rental'));
    }

    public function stok()
    {
        //
        $produk = Produk::all();
        return view('produk.stok', compact('produk'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('produk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        do {
            $id_produk = 'P' . rand(1,1000000000);
        } while (Produk::where('id_produk', $id_produk)->exists());
        Produk::create([
            'id_produk' => $id_produk,
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'qty' => $request->qty
        ]);
        return redirect()->route('pr.stok');
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
        $produk = Produk::where('id_produk',$id)->first();
        return view('produk.edit',compact('produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $produk = Produk::where('id_produk', $id)->firstOrFail();
        $produk->update([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'qty' => $request->qty
        ]);
        return redirect()->route('pr.stok');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $produk = Produk::where('id_produk', $id)->get();

        $produk->each->delete();
        return redirect()->route('pr.stok');
    }

    public function deleteitem(Request $request,string $id)
    {
        //
        $orderitem = OrderItem::where("id",$id)->first();
        $order = Order::where("id",$orderitem->order_id)->first();
        $rental = Rental::where("id_player", $order->id_table)->first();
        $orderitem->delete();
        return redirect()->route("bl.stop",$rental->no_meja . "?lama_main=".$request->lama_main );
    }
}
