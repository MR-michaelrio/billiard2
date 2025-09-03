<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // pastikan sudah diimport

use App\Models\Meja;
use App\Models\NonMember;
use App\Models\Rental;
use App\Models\Member;
use App\Models\RentalInvoice;
use App\Models\Invoice;
use App\Models\HargaRental;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Paket;
use App\Models\Produk;

use DateTime;
use DateInterval;
use Carbon\Carbon;

class BilliardController extends Controller
{

    public function print($id_rental)
    {
        // Ambil semua rental invoice (detail per meja)
        $rentals = RentalInvoice::where('id_rental', $id_rental)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($rentals->isEmpty()) {
            return redirect()->back()->with('error', 'Rental tidak ditemukan untuk ID ini.');
        }

        $main_rental = $rentals->first();
        $no_meja = $main_rental->no_meja;
        $tanggalmain = Carbon::parse($main_rental->waktu_akhir)->format('d-m-Y');

        // Ambil invoice utama
        $invoice = Invoice::where('id_rental', $id_rental)->first();

        // Ambil makanan berdasarkan id_belanja dari invoice
        $makanan = collect();
        if ($invoice && $invoice->id_belanja) {
            $makanan = Order::where('id_table', $invoice->id_belanja)
                ->with('items')
                ->get();
        }

        // Hitung ulang harga_table berdasarkan lama_waktu di RentalInvoice
        $total_rental_price = 0;

        foreach ($rentals as $rental) {
            if (in_array($rental->status, ["lanjut", "tambahanlanjut"])) {
                $lama_waktu = request()->query('lama_main', '00:00:00');

            } else {
                $lama_waktu = $rental->lama_waktu ?? '00:00:00';
            }

            $lama_waktu = $rental->lama_waktu ?? "00:00:00";
            list($hours, $minutes, $seconds) = sscanf($lama_waktu, "%d:%d:%d");
            $total_minutes = ($hours * 60) + $minutes + ($seconds / 60);

            $hargarental = HargaRental::where('jenis', 'menit')->first();
            $harga_per_menit = $hargarental ? $hargarental->harga : 0;

            if (in_array($rental->no_meja, [1, 2])) {
                $meja_price = ($total_minutes / 60) * 50000;
            } else {
                $meja_price = $total_minutes * $harga_per_menit;

                // cek paket
                $paket = Paket::orderBy('jam', 'asc')->get();
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $meja_price = $p->harga;
                        break;
                    }
                }
            }

            $rental->harga_dihitung = round($meja_price, 0);
            $total_rental_price += $rental->harga_dihitung;
            $rental->lama_waktu_hitung = $lama_waktu;
        }

        // Hitung total makanan
        $total_makanan = $makanan->flatMap(function ($order) {
            return $order->items;
        })->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Diskon
        $diskon = $main_rental->diskon ?? 0;
        $total_rental_diskon = $total_rental_price - ($total_rental_price * ($diskon / 100));
        $total = round($total_rental_diskon + $total_makanan);

        // Update invoice supaya sync
        if ($invoice) {
            $invoice->update([
                'harga_table' => round($total_rental_price, 0),
                'harga_cafe'  => round($total_makanan, 0),
            ]);
        }
        return view('invoice.struk', [
            'invoice'             => $invoice,
            'rentals'             => $rentals,
            'no_meja'             => $no_meja,
            'makanan'             => $makanan,
            'total_rental_price'  => $total_rental_price,
            'total_makanan'       => $total_makanan,
            'total'               => $total,
            'diskon'              => $diskon,
            'tanggalmain'         => $tanggalmain
        ]);
    }


    public function printrekap($id_rental)
    {
        // Ambil semua rental invoice terkait
        $rentals = RentalInvoice::where('id_rental', $id_rental)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($rentals->isEmpty()) {
            return redirect()->back()->with('error', 'Rental tidak ditemukan.');
        }

        $main_rental = $rentals->first();
        $no_meja = $main_rental->no_meja;
        $tanggalmain = Carbon::parse($main_rental->waktu_akhir)->format('d-m-Y');

        // Ambil invoice utama
        $invoice = Invoice::where('id_rental', $id_rental)->first();

        // Ambil semua makanan/minuman berdasarkan id_belanja
        $makanan = collect();
        if ($invoice && $invoice->id_belanja) {
            $makanan = Order::where('id_table', $invoice->id_belanja)
                ->with('items')
                ->get();
        }

        // Hitung harga table dari semua RentalInvoice (berdasarkan lama_waktu)
        $total_rental_price = 0;
        foreach ($rentals as $rental) {
            $lama_waktu = $rental->lama_waktu ?? "00:00:00";
            list($hours, $minutes, $seconds) = sscanf($lama_waktu, "%d:%d:%d");
            $total_minutes = ($hours * 60) + $minutes + ($seconds / 60);

            $hargarental = HargaRental::where('jenis', 'menit')->first();
            $harga_per_menit = $hargarental ? $hargarental->harga : 0;

            if (in_array($rental->no_meja, [1, 2])) {
                $meja_price = ($total_minutes / 60) * 50000;
            } else {
                $meja_price = $total_minutes * $harga_per_menit;

                // cek paket
                $paket = Paket::orderBy('jam', 'asc')->get();
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $meja_price = $p->harga;
                        break;
                    }
                }
            }

            $rental->harga_dihitung = round($meja_price, 0);
            $total_rental_price += $rental->harga_dihitung;
        }

        // Hitung total makanan/minuman
        $total_makanan = $makanan->flatMap(function ($order) {
            return $order->items;
        })->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Diskon
        $diskon = $main_rental->diskon ?? 0;
        $total_rental_diskon = $total_rental_price - ($total_rental_price * ($diskon / 100));
        $total = round($total_rental_diskon + $total_makanan);

        // Update invoice supaya sinkron
        if ($invoice) {
            $invoice->update([
                'harga_table' => round($total_rental_price, 0),
                'harga_cafe'  => round($total_makanan, 0),
            ]);
        }

        return view('invoice.struk', [
            'invoice'             => $invoice,
            'rentals'             => $rentals,
            'no_meja'             => $no_meja,
            'makanan'             => $makanan,
            'total_rental_price'  => $total_rental_price,
            'total_makanan'       => $total_makanan,
            'total'               => $total,
            'diskon'              => $diskon,
            'tanggalmain'         => $tanggalmain
        ]);
    }


    public function status(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_table' => 'required|string'
            ]);

            $orders = Order::where('id_table', $validated['id_table'])->where('status', 'belum')->get();
            
            // Ensure there are orders to update
            if ($orders->isEmpty()) {
                return response()->json(['success' => true]);
            }

            foreach ($orders as $order) {
                $order->update(['status' => 'lunas']);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Error in status method:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    public function index()
    {
        $meja = Meja::all();
        $rental = Rental::all();
    
        $meja_rental = $meja->map(function($m) {
            $invoice = Rental::where('no_meja', $m->nomor)
                ->latest('created_at') // atau latest('id')
                ->first();
        
            return [
                'nomor_meja' => $m->nomor,
                'waktu_mulai'=> $invoice && $invoice->waktu_mulai ? $invoice->waktu_mulai->format('Y-m-d H:i:s') : null,
                'waktu_akhir' => $invoice && $invoice->waktu_akhir ? $invoice->waktu_akhir->format('Y-m-d H:i:s') : null,
                'status' => $invoice ? $invoice->status : null
            ];
        });
        return view('billiard.index', compact('meja_rental'));
    }
    
    public function create()
    {
        //
        return view('billiard.nonmember');
    }

    public function menu($no_meja)
    {
        //
        return view('billiard.menu', ['no_meja' => $no_meja]);

        // return view('billiard.menu');
    }

    public function nonmember($no_meja)
    {
        //
        return view('billiard.nonmember', ['no_meja' => $no_meja]);
    }

    public function stop($no_meja)
    {
        $rentals = Rental::where('no_meja', $no_meja)->orderBy('created_at', 'asc')->get();
        $rental_count = $rentals->count();

        if ($rentals->isEmpty()) {
            return abort(404);
        }

        $total_rental_minutes = 0;

        // Ambil player dari rental pertama
        $id_player = $rentals->first()->id_player;

        // Ambil semua makanan untuk player ini (hanya sekali)
        $makanan = Order::where('id_table', $id_player)
            ->where('status', 'belum')
            ->with('items')
            ->get();

        foreach ($rentals as $rental) {
            // Hitung lama waktu sesuai status
            if (in_array($rental->status, ["lanjut", "tambahanlanjut"])) {
                $lama_waktu = request()->query('lama_main', '00:00:00');

            } else {
                $lama_waktu = $rental->lama_waktu ?? '00:00:00';
            }
            // Konversi ke menit
            list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
            $total_minutes = $hours * 60 + $minutes + $seconds / 60;
            $total_rental_minutes += $total_minutes;

            // Hitung harga
            $hargarental = HargaRental::where('jenis', 'menit')->first();
            $harga_per_menit = $hargarental ? $hargarental->harga : 0;

            if (in_array($no_meja, [1, 2])) {
                $meja_price = ($total_minutes / 60) * 50000;
            } else {
                $meja_price = $total_minutes * $harga_per_menit;

                // Cek paket
                $paket = Paket::orderBy('jam', 'asc')->get();
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $meja_price = $p->harga;
                        break;
                    }
                }
            }

            // Simpan hasil ke model rental
            $rental->harga_per_rental = round($meja_price, 0);
            $rental->lama_waktu_hitung = $lama_waktu; // bisa ditampilkan di view
        }

        // Total makanan
        $total_makanan = $makanan->flatMap(function ($order) {
            return $order->items;
        })->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Total keseluruhan
        $total_rental_price = $rentals->sum('harga_per_rental');
        $total = round($total_rental_price + $total_makanan);

        $meja_rental = $rentals;
        // dd($meja_rental);
        return view('invoice.stop', compact(
            'meja_rental',
            'rental_count',
            'no_meja',
            'makanan',
            'total',
            'total_rental_minutes',
            'total_rental_price'
        ));
    }


    public function bayar(Request $request)
    {
        try {
            $no_meja = $request->input('no_meja', '');
            $metode = $request->input('metode', '');
            $diskon = $request->input('diskon', '0');
            $lama_waktu = $request->input('lama_waktu', '');
            // Ambil semua rental di meja ini
            $rentals = Rental::where('no_meja', $no_meja)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($rentals->isEmpty()) {
                return response()->json(['success' => false, 'error' => 'Meja tidak ditemukan'], 404);
            }

            $id_player = $rentals->first()->id_player;
            $id_rental = 'R' . rand(1, 1000000000); // Sama untuk semua invoice di meja ini

            // ====== Hitung total harga table (sama seperti stop) ======
            foreach ($rentals as $rental) {

                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                // Hitung harga
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;

                if (in_array($no_meja, [1, 2])) {
                    $meja_price = ($total_minutes / 60) * 50000;
                } else {
                    $meja_price = $total_minutes * $harga_per_menit;

                    // cek paket
                    $paket = Paket::orderBy('jam', 'asc')->get();
                    $best_price = null;
                    foreach ($paket as $p) {
                        if ($lama_waktu == $p->jam) {
                            $best_price = $p->harga;
                            break;
                        }
                    }
                    $meja_price = $best_price !== null ? $best_price : $meja_price;
                }

                // Simpan harga di property baru
                $rental->harga_per_rental = round($meja_price, 0);

                // Simpan ke RentalInvoice
                RentalInvoice::create([
                    'id_rental'   => $id_rental,
                    'lama_waktu'  => $lama_waktu,
                    'waktu_mulai' => $rental->waktu_mulai,
                    'waktu_akhir' => $rental->waktu_akhir ?? now("Asia/Jakarta"),
                    'no_meja'     => $rental->no_meja,
                    'metode'      => $metode,
                    'diskon'      => $diskon,
                    'harga'       => round($meja_price, 0)
                ]);

                // Update order & stok
                $orders = Order::where('id_table', $rental->id_player)->where('status', 'belum')->get();
                foreach ($orders as $order) {
                    foreach ($order->items as $item) {
                        $produk = Produk::where('nama_produk', $item['product_name'])->first();
                        if ($produk) {
                            $produk->qty -= $item['quantity'];
                            $produk->save();
                        }
                    }
                    $order->update(['status' => 'lunas']);
                }

                // Hapus rental
                $rental->delete();
            }

            // Jumlahkan harga semua rental → total harga table
            $total_rental_price = $rentals->sum('harga_per_rental');

            // ====== Hitung total makanan (cafe) ======
            $makanan = Order::where('id_table', $id_player)
                            ->where('status', 'lunas') // sudah dibayar di atas
                            ->with('items')
                            ->get();

            $total_makanan = $makanan->flatMap(function($order) {
                return $order->items;
            })->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // Simpan Invoice total per meja
            $invoice = Invoice::create([
                'id_player'   => $id_player,
                'id_rental'   => $id_rental,
                'id_belanja'  => $id_player,
                'user_id'     => 1,
                'harga_table' => $total_rental_price,
                'harga_cafe'  => $total_makanan,
            ]);

            // Emit ke WebSocket
            try {
                Http::post("http://127.0.0.1:3001/emit", [
                    "event" => "mejaUpdate",
                    "data" => [
                        "nomor_meja" => $no_meja,
                        "status" => "closed"
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::error("Gagal kirim ke WebSocket: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'id_table' => $no_meja,
                'id_rental' => $id_rental,
                'harga_table' => round($total_rental_price, 0),
                'harga_cafe' => round($total_makanan, 0),
                'total' => round($total_rental_price + $total_makanan, 0),
                "lama" => $lama_waktu
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in bayar function:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function tambahwaktu($no_meja)
    {
        return view('billiard.tambahwaktu', compact('no_meja'));
    }
    
    public function storemember(Request $request)
    {
        //
        if (!preg_match('/^\d{2}:\d{2}$/', $request->lama_waktu)) {
            return response()->json(['error' => 'Invalid time format'], 400);
        }
    
        // Mengambil waktu saat ini di timezone Jakarta
        $tanggalMain = Carbon::now('Asia/Jakarta');
        $lamaWaktu = $request->lama_waktu;
    
        // Memisahkan lamaWaktu menjadi hours dan minutes
        list($hours, $minutes) = explode(':', $lamaWaktu);
        $intervalString = 'PT' . $hours . 'H' . $minutes . 'M';
        $interval = new \DateInterval($intervalString);
    
        // Menambahkan interval ke tanggalMain
        $tanggalMain->add($interval);
        $waktuAkhir = $tanggalMain->format('Y-m-d H:i:s');

        // $member = Member::where('id_member',$request->nama);
        // return $request->nama;
        $existingRental = Rental::where("no_meja", $request->no_meja)
        ->latest('id') // ambil yang paling baru
        ->first();
        if ($existingRental) {
            // Kalau sudah ada, tambahkan waktu
            $existingRental->update([
                'status' => 'tambah'
            ]);
    
            // Buat data baru dengan status kosong
            Rental::create([
                'id_player'   => $existingRental->id_player, // ambil dari data lama
                'lama_waktu'  => $request->lama_waktu,
                'waktu_mulai' => now(),
                'waktu_akhir' => $waktuAkhir,
                'no_meja'     => $request->no_meja,
                'status'      => '' // kosong
            ]);
        } else {
            // Kalau belum ada, buat baru
            Rental::create([
                'id_player' => $request->nama, // pastikan ini memang id_player
                'lama_waktu' => $request->lama_waktu,
                'waktu_mulai' => now(),
                'waktu_akhir' => $waktuAkhir,
                'no_meja' => $request->no_meja,
                'status' => ''
            ]);
        }
        return redirect()->route('bl.index');
    }
    public function storemember2(Request $request)
    {
        // Mengambil waktu saat ini di timezone Jakarta
        $tanggalMain = Carbon::now('Asia/Jakarta');
        
        $b = Rental::create([
            'id_player' => $request->nama,
            'waktu_mulai' => $tanggalMain,
            'no_meja' => $request->no_meja,
            'status' => 'lanjut'
        ]);
        return redirect()->route('bl.index');
    }
    public function menumember($no_meja)
    {
        //
        $member = Member::all();
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.menumember', compact('meja_rental', 'no_meja', 'member'));
    }
    public function memberlanjutan($no_meja)
    {
        //
        $member = Member::all();
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.memberlanjutan', compact('meja_rental', 'no_meja', 'member'));
    }
    public function memberperwaktu($no_meja)
    {
        //
        $member = Member::all();
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.memberperwaktu', compact('meja_rental', 'no_meja', 'member'));
    }

    public function storenonmember(Request $request)//lanjutan
    {
        //
        $id_non = rand(1,1000000000);
        $tanggalMain = Carbon::now('Asia/Jakarta');
        
        $a = NonMember::create([
            'id' => $id_non,
            'nama' => $request->nama,
            'no_telp' => $request->no_telp
        ]);
        $b = Rental::create([
            'id_player' => $id_non,
            'waktu_mulai' => $tanggalMain,
            'no_meja' => $request->no_meja,
            'status' => 'lanjut'
        ]);
        $payload = [
            'nomor_meja' => $request->no_meja,
            'status' => 'lanjut',
            'start_time' => $tanggalMain->toDateTimeString()
        ];
        try {
            Http::post('http://127.0.0.1:3001/meja/update', $payload);
        } catch (\Exception $e) {
            \Log::error("Gagal kirim socket update: " . $e->getMessage());
        }
        return redirect()->route('bl.index');
    }
    public function storenonmember2(Request $request)//perwaktu
    {
        $id_non = rand(1,1000000000);

        if (!preg_match('/^\d{2}:\d{2}$/', $request->lama_waktu)) {
            return response()->json(['error' => 'Invalid time format'], 400);
        }
    
        // Mengambil waktu saat ini di timezone Jakarta
        $tanggalMain = Carbon::now('Asia/Jakarta');
        $lamaWaktu = $request->lama_waktu;
        list($hours, $minutes) = explode(':', $lamaWaktu);
        $intervalString = 'PT' . $hours . 'H' . $minutes . 'M';

        $interval = new DateInterval($intervalString);
        $tanggalMain->add($interval);
        $waktuAkhir = $tanggalMain->format('Y-m-d H:i:s');

        $existingRental = Rental::where("no_meja", $request->no_meja)
        ->latest('id')
        ->first();
        
        if ($existingRental) {
            // --- MODE TAMBAH WAKTU ---
            // Ubah data lama jadi selesai
            $existingRental->update([
                'status' => 'selesai'
            ]);
    
            // Pakai id_player lama
            $idPlayer = $existingRental->id_player;
    
            // Buat data baru dengan status tambahan
            $b = Rental::create([
                'id_player'   => $idPlayer,
                'lama_waktu'  => $request->lama_waktu,
                'waktu_mulai' => Carbon::now('Asia/Jakarta'),
                'waktu_akhir' => $waktuAkhir,
                'no_meja'     => $request->no_meja,
                'status'      => 'tambahan'
            ]);
    
        } else {
            // --- MODE BARU ---
            // Buat NonMember
            $nonMember = NonMember::create([
                'id'      => $id_non,
                'nama'    => $request->nama,
                'no_telp' => $request->no_telp
            ]);
    
            // Buat rental baru
            $b = Rental::create([
                'id_player'   => $nonMember->id,
                'lama_waktu'  => $request->lama_waktu,
                'waktu_mulai' => Carbon::now('Asia/Jakarta'),
                'waktu_akhir' => $waktuAkhir,
                'no_meja'     => $request->no_meja,
                'status'      => 'baru'
            ]);
        }
        $payload = [
            'nomor_meja' => $request->no_meja,
            'status' => 'jalan',
            'waktu_mulai' => $b->waktu_mulai,
            'waktu_akhir' => $waktuAkhir,
            'lama_waktu' => $request->lama_waktu
        ];
        try {
            Http::post('http://127.0.0.1:3001/meja/update', $payload);
        } catch (\Exception $e) {
            \Log::error("Gagal kirim socket update: " . $e->getMessage());
        }
        return redirect()->route('bl.index');
    }
    public function menunonmember($no_meja)
    {
        //
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.menunonmember', compact('meja_rental', 'no_meja'));
    }
    public function nonmemberlanjutan($no_meja)
    {
        //
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.nonmemberlanjutan', compact('meja_rental', 'no_meja'));
    }
    public function nonmemberperwaktu($no_meja)
    {
        //
        $meja_rental = Rental::where('no_meja', $no_meja)->get();

        return view('billiard.nonmemberperwaktu', compact('meja_rental', 'no_meja'));
    }

    public function rekapinvoice()
    {
        // with('order.items')->
        $invoices = Invoice::with('rentalinvoice')->get();
        // return $invoices;
        return view('invoice.rekap',compact('invoices'));
    }

    public function showrekap(string $id)
    {
        $query = "
            SELECT
                i.id AS invoice_id, 
                i.id_player, 
                i.id_rental, 
                i.id_belanja, 
                o.id AS order_id, 
                o.id_table, 
                o.status AS order_status, 
                oi.id AS item_id, 
                oi.product_name, 
                oi.quantity, 
                oi.price,
                ri.lama_waktu,
                ri.waktu_mulai,
                ri.waktu_akhir,
                ri.no_meja
            FROM 
                invoice AS i
            LEFT JOIN 
                `orders` AS o ON i.id_belanja = o.id_table
            LEFT JOIN 
                order_items AS oi ON o.id = oi.order_id
            JOIN
                rental_invoice AS ri ON i.id_rental = ri.id_rental
            WHERE
                i.id = :id
        ";

        $invoices = DB::select($query, ['id' => $id]);

        return view('invoice.showrekap',compact('invoices'));
    }
    
    public function rekaptable1() {
        $timezone = 'Asia/Jakarta'; // Set the timezone to Asia/Jakarta (UTC+7)
    
        // Set the start and end time for the report in Asia/Jakarta timezone
        $startTime = Carbon::yesterday($timezone)->setTime(11, 0, 0);
        $endTime = Carbon::today($timezone)->setTime(3, 0, 0);
    
        // Query RentalInvoice between the given time range using waktu_mulai
        $rentalinvoices = RentalInvoice::whereBetween('waktu_mulai', [$startTime, $endTime])->get();
    
        // Check if any records found
        if ($rentalinvoices->isEmpty()) {
            return view('invoice.rekap-table')->withErrors('No rental records found for the given time range.');
        }
    
        // Fetch packages for the best pricing
        $paket = Paket::orderBy('jam', 'asc')->get();
    
        // Initialize the array to store data for each member
        $data = [];
    
        // Loop through each rental invoice
        foreach ($rentalinvoices as $rental) {
            $id_rental = $rental->id_rental;
            $tanggalmain = $rental->waktu_mulai;
    
            // Fetch all invoices matching this rental
            $invoices = Invoice::where("id_rental", $rental->id_rental)->get();
    
            // Loop through all the invoices
            foreach ($invoices as $invoice) {
                // Strictly reset total_makanan to ensure no carryover values
                 // Always start with 0 for total food price
    
                // Fetch orders (makanan) for this rental
                // Only proceed if id_belanja is not 0
                if ($invoice->id_belanja != 0) {
                    $makanan = Order::where('id_table', $invoice->id_belanja)
                                    ->where('status', 'lunas')
                                    ->with('items')
                                    ->get();

                    if (!$makanan->isEmpty()) {
                        // Calculate total food price if food orders exist
                        $total_makanan = $makanan->flatMap(function($order) {
                            return $order->items;
                        })->sum(function($item) {
                            return $item->price * $item->quantity;
                        });
                    } else {
                        // No food orders, set total to 0
                        $total_makanan = 0;
                    }
                } else {
                    // If id_belanja is 0, ignore this invoice and set total_makanan to 0
                    $total_makanan = 0;
                }

    
                // Calculate rental price for the table
                $lama_waktu = $rental->lama_waktu ?? '00:00:00'; // Default if null
    
                // Convert the duration to total minutes
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;
    
                // Fetch per-minute rate
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;
    
                // Calculate table rental price
                $mejatotal = $total_minutes * $harga_per_menit;
    
                // Check if there is a package deal
                $best_price = null;
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $best_price = $p->harga;
                        break;
                    }
                }
                $mejatotal = $best_price !== null ? $best_price : $mejatotal;
    
                // Sum total price (food + table rental)
                $total = $mejatotal + $total_makanan;

                // Add this data to the array (store for each invoice)
                $data[] = [
                    'id_rental' => $rental->id_rental,
                    'tanggal' => $tanggalmain,
                    'lama_waktu' => $lama_waktu,
                    'mejatotal' => $mejatotal,
                    'total_makanan' => $total_makanan,
                    'total' => $total,
                    'no_meja' => $rental->no_meja,
                ];
            }
        }
    
        // Return the view with the summarized data
        return view('invoice.rekap-table', compact('data'));
    }

    public function showRekapTablePage() {
        $timezone = 'Asia/Jakarta';
        $startTime = Carbon::yesterday($timezone)->setTime(11, 0, 0);
        $endTime = Carbon::today($timezone)->setTime(3, 0, 0);

        $rentalinvoices = RentalInvoice::whereBetween('waktu_mulai', [$startTime, $endTime])->get();
        $paket = Paket::orderBy('jam', 'asc')->get();
        $data = [];
    
        foreach ($rentalinvoices as $rental) {
            $id_rental = $rental->id_rental;
            $tanggalmain = $rental->waktu_mulai;
            
            $invoices = Invoice::where("id_rental", $rental->id_rental)->get();
    
            foreach ($invoices as $invoice) {
                $total_makanan = 0;
    
                if ($invoice->id_belanja != 0) {
                    $makanan = Order::where('id_table', $invoice->id_belanja)
                        ->where('status', 'lunas')
                        ->with('items')
                        ->get();
    
                    if (!$makanan->isEmpty()) {
                        $total_makanan = $makanan->flatMap(function ($order) {
                            return $order->items;
                        })->sum(function ($item) {
                            return $item->price * $item->quantity;
                        });
                    }
                }
    
                $lama_waktu = $rental->lama_waktu ?? '00:00:00';
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;
    
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;
    
                $mejatotal = $total_minutes * $harga_per_menit;
                $best_price = null;
                $tanggalmainakhir = $tanggalmain->copy()->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $best_price = $p->harga;
                        break;
                    }
                }
                $mejatotal = $best_price !== null ? $best_price : $mejatotal;
    
                $total = $mejatotal + $total_makanan;
    
                $data[] = [
                    'id_rental' => $rental->id_rental,
                    'tanggal' => $tanggalmain,
                    'tanggalakhir' => $tanggalmainakhir,
                    'lama_waktu' => $lama_waktu,
                    'mejatotal' => $mejatotal,
                    'total_makanan' => $total_makanan,
                    'total' => $total,
                    'no_meja' => $rental->no_meja,
                ];
            }
        }
    
        return view('invoice.rekap-table', compact('data'));
    }

    public function getRekapTableData()
    {
        $timezone = 'Asia/Jakarta';

        // Set the start and end time for the report in Asia/Jakarta timezone
        $startTime = Carbon::yesterday($timezone)->setTime(11, 0, 0);
        $endTime = Carbon::today($timezone)->setTime(3, 0, 0);

        // Query RentalInvoice between the given time range using waktu_mulai
        $rentalinvoices = RentalInvoice::whereBetween('waktu_mulai', [$startTime, $endTime])->get();

        $paket = Paket::orderBy('jam', 'asc')->get();
        $data = [];

        // Loop through each rental invoice
        foreach ($rentalinvoices as $rental) {
            $id_rental = $rental->id_rental;
            $tanggalmain = $rental->waktu_mulai;

            // Fetch all invoices matching this rental
            $invoices = Invoice::where("id_rental", $rental->id_rental)->get();

            foreach ($invoices as $invoice) {
                $total_makanan = 0;

                // Ignore invoices with id_belanja == 0
                if ($invoice->id_belanja != 0) {
                    $makanan = Order::where('id_table', $invoice->id_belanja)
                                    ->where('status', 'lunas')
                                    ->with('items')
                                    ->get();

                    if (!$makanan->isEmpty()) {
                        $total_makanan = $makanan->flatMap(function ($order) {
                            return $order->items;
                        })->sum(function ($item) {
                            return $item->price * $item->quantity;
                        });
                    }
                }

                $lama_waktu = $rental->lama_waktu ?? '00:00:00';
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;

                $mejatotal = $total_minutes * $harga_per_menit;
                $best_price = null;

                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $best_price = $p->harga;
                        break;
                    }
                }
                $mejatotal = $best_price !== null ? $best_price : $mejatotal;

                $total = $mejatotal + $total_makanan;

                $data[] = [
                    'id_rental' => $rental->id_rental,
                    'tanggal' => $tanggalmain,
                    'lama_waktu' => $lama_waktu,
                    'mejatotal' => $mejatotal,
                    'total_makanan' => $total_makanan,
                    'total' => $total,
                    'no_meja' => $rental->no_meja,
                ];
            }
        }

        return response()->json($data);
    }

    public function rekaptable() {
        $timezone = 'Asia/Jakarta'; // Set the timezone to Asia/Jakarta (UTC+7)
    
        // Get the current time
        $currentTime = Carbon::now($timezone);
        // Set the allowed time range (1 AM to 3 AM)
        $allowedStartTime = Carbon::today($timezone)->setTime(1, 0, 0);
        $allowedEndTime = Carbon::today($timezone)->setTime(3, 0, 0);
    
        // Check if the current time is within the allowed time range
        if ($currentTime->lt($allowedStartTime) || $currentTime->gt($allowedEndTime)) {
            return redirect()->back()->withErrors('Access to the report is only allowed between 1 AM and 3 AM.');
        }
    
        // Set the start and end time for the report in Asia/Jakarta timezone
        $startTime = Carbon::yesterday($timezone)->setTime(11, 0, 0);
        $endTime = Carbon::today($timezone)->setTime(3, 0, 0);
    
        // Query RentalInvoice between the given time range using waktu_mulai
        $rentalinvoices = RentalInvoice::whereBetween('waktu_mulai', [$startTime, $endTime])->get();
    
        // Check if any records found
        if ($rentalinvoices->isEmpty()) {
            return view('invoice.rekap-table')->withErrors('No rental records found for the given time range.');
        }
    
        // Fetch packages for the best pricing
        $paket = Paket::orderBy('jam', 'asc')->get();
    
        // Initialize the array to store data for each member
        $data = [];
    
        // Loop through each rental invoice
        foreach ($rentalinvoices as $rental) {
            $id_rental = $rental->id_rental;
            $tanggalmain = $rental->waktu_mulai;
    
            // Fetch all invoices matching this rental
            $invoices = Invoice::where("id_rental", $rental->id_rental)->get();
    
            // Loop through all the invoices
            foreach ($invoices as $invoice) {
                // Strictly reset total_makanan to ensure no carryover values
                $total_makanan = 0; // Always start with 0 for total food price
    
                // Fetch orders (makanan) for this rental
                if ($invoice->id_belanja != 0) {
                    $makanan = Order::where('id_table', $invoice->id_belanja)
                                    ->where('status', 'lunas')
                                    ->with('items')
                                    ->get();
    
                    if (!$makanan->isEmpty()) {
                        // Calculate total food price if food orders exist
                        $total_makanan = $makanan->flatMap(function($order) {
                            return $order->items;
                        })->sum(function($item) {
                            return $item->price * $item->quantity;
                        });
                    }
                }
    
                // Calculate rental price for the table
                $lama_waktu = $rental->lama_waktu ?? '00:00:00'; // Default if null
    
                // Convert the duration to total minutes
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;
    
                // Fetch per-minute rate
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;
    
                // Calculate table rental price
                $mejatotal = $total_minutes * $harga_per_menit;
    
                // Check if there is a package deal
                $best_price = null;
                foreach ($paket as $p) {
                    if ($lama_waktu == $p->jam) {
                        $best_price = $p->harga;
                        break;
                    }
                }
                $mejatotal = $best_price !== null ? $best_price : $mejatotal;
    
                // Sum total price (food + table rental)
                $total = $mejatotal + $total_makanan;
    
                // Add this data to the array (store for each invoice)
                $data[] = [
                    'id_rental' => $rental->id_rental,
                    'tanggal' => $tanggalmain,
                    'lama_waktu' => $lama_waktu,
                    'mejatotal' => $mejatotal,
                    'total_makanan' => $total_makanan,
                    'total' => $total,
                    'no_meja' => $rental->no_meja,
                ];
            }
        }
    
        // Return the view with the summarized data
        return view('invoice.rekap-table', compact('data'));
    }

    // YourController.php
    public function searchNames(Request $request)
    {
        $search = $request->get('term'); // Mengambil query dari parameter 'term'
        
        // Ambil nama dari tabel, sesuaikan dengan nama tabel dan kolom yang Anda miliki
        $results = DB::table('non_member')
                    ->select('id', 'nama', 'no_telp')
                    ->where('nama', 'LIKE', '%' . $search . '%')
                    ->get();

        // Mengembalikan hasil dalam format JSON yang dapat digunakan untuk autocomplete
        return response()->json($results);
    }

    public function rekapbulan()
    {
        $rekaps = Invoice::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
        // return $rekaps;
        return view('invoice.rekap-bulan',compact('rekaps'));
    }

    public function rekapdetailbulan($bulan)
    {
        $hargarental = HargaRental::where('jenis', 'menit')->first();
        $harga_per_menit = $hargarental ? $hargarental->harga : 0;
    
        // Mengambil data invoice dengan join ke rental_invoice menggunakan LEFT JOIN
        $rekaps = DB::table('invoice')
            ->leftJoin('rental_invoice', 'invoice.id_rental', '=', 'rental_invoice.id_rental')
            ->where(DB::raw('MONTH(invoice.created_at)'), $bulan)
            ->select(
                'invoice.*', // Data dari tabel invoice
                'rental_invoice.lama_waktu', // Data dari rental_invoice
                'rental_invoice.no_meja',    // Data dari rental_invoice
                'rental_invoice.metode'      // Data dari rental_invoice
            )
            ->get();

        // Iterasi melalui hasil query dan hitung harga total meja
        foreach ($rekaps as $rekap) {
            $lama_waktu = $rekap->lama_waktu ?? '00:00:00';
    
            // Konversi lama_waktu (HH:MM:SS) ke total menit
            list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
            $total_minutes = $hours * 60 + $minutes + $seconds / 60;
    
            // Hitung harga rental menggunakan harga per menit
            $mejatotal = $total_minutes * $harga_per_menit;
    
            // Cek harga terbaik berdasarkan paket yang tersedia
            $paket = Paket::orderBy('jam', 'asc')->get();
            $best_price = null;
    
            foreach ($paket as $p) {
                // Konversi waktu paket ($p->jam) ke menit
                $package_minutes = (substr($p->jam, 0, 2) * 60) + substr($p->jam, 3, 2);
    
                // Jika total_minutes sama dengan atau melebihi waktu paket
                if ($total_minutes == $package_minutes) {
                    $best_price = $p->harga; // Update ke harga paket ini
                }
            }
    
            // Jika harga paket ditemukan, gunakan; jika tidak, tetap gunakan harga per menit
            $rekap->mejatotal = $best_price !== null ? $best_price : $mejatotal;
        }
    
        // return $rekaps;
        // Kirim data ke view
        return view('invoice.rekap-detailbulan', compact('rekaps'));
    }
    



}