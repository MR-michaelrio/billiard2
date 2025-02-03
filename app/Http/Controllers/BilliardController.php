<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        // Fetch the main RentalInvoice record by ID
        $meja_rental = RentalInvoice::where('id_rental', $id_rental)->first();
        $invoice = Invoice::where('id_rental', $id_rental)->first();
        $meja_rental2 = RentalInvoice::where('id_rental', $id_rental)->get();
        $rental = RentalInvoice::where('id_rental', $id_rental)->count();
        $no_meja = $meja_rental->no_meja;
        $tanggalmain = Carbon::parse($meja_rental->waktu_akhir)->format('d-m-Y');

        if ($meja_rental) {
            // Make sure to use the correct id reference for Orders
            $makanan = Order::where('id_table', $invoice->id_belanja)
                            ->where('status', 'belum')
                            ->with('items')
                            ->get();

            $idplayer = substr($meja_rental->id_player, 0, 1);

            if ($idplayer == 'M') {
                $mejatotal = 0;
                $lama_waktu = '00:00:00';
            } else {
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $lama_waktu = $meja_rental->lama_waktu ?? '00:00:00'; // Correctly get the lama_waktu

                // Handle case when elapsedSeconds is provided via query parameter
                if (!$lama_waktu || $lama_waktu == '00:00:00') {
                    $elapsedSeconds = request()->query('elapsed');

                    if ($elapsedSeconds !== null) {
                        $hours = floor($elapsedSeconds / 3600);
                        $minutes = floor(($elapsedSeconds % 3600) / 60);
                        $seconds = $elapsedSeconds % 60;

                        $lama_waktu = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    } else {
                        $lama_waktu = '00:00:00';
                    }
                }

                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                // Initialize default per-minute pricing
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;

                if (in_array($no_meja, [1, 2])) {
                    // Harga khusus meja 1 dan 2 (Rp 60.000 per jam)
                    $mejatotal = ($total_minutes / 60) * 50000;
                } else {
                    // Hitung harga berdasarkan per menit atau paket
                    $mejatotal = $total_minutes * $harga_per_menit;
            
                    // Iterasi melalui paket untuk mendapatkan harga terbaik
                    $paket = Paket::orderBy('jam', 'asc')->get();
                    $best_price = null; // Default to calculated per-minute price
                    foreach ($paket as $p) {
                        // Convert package time ($p->jam) to minutes
                        $package_minutes = (substr($p->jam, 0, 2) * 60) + substr($p->jam, 3, 2);
                        
                        // Check if the input time (total_minutes) exceeds or is equal to the package time
                        if ($total_minutes == $package_minutes) {
                            $best_price = $p->harga; // Update the best price to this package's price
                        }
                    }
                    $mejatotal = $best_price !== null ? $best_price : $mejatotal;
                }
            }

            // Calculate the total for all food items
            $total_makanan = $makanan->flatMap(function($order) {
                return $order->items;
            })->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // Total biaya keseluruhan
            $diskon = $meja_rental->diskon;
            $totalmeja = $mejatotal - ($mejatotal * ( $diskon / 100));
            $total = $totalmeja + $total_makanan;
            $total = round($total);
            $invoice->update([
                "harga_table"=>$mejatotal,
                "harga_cafe"=>$total_makanan
            ]);
            return view('invoice.struk', compact("invoice",'meja_rental', 'meja_rental2', 'no_meja', 'rental', 'lama_waktu', 'mejatotal', 'total', 'makanan',"tanggalmain", "diskon"));
        } else {
            return redirect()->back()->with('error', 'No rental found for the specified table.');
        }
    }

    public function printrekap($id_rental)
    {
        // Fetch the main RentalInvoice record by ID
        $meja_rental = RentalInvoice::where('id_rental', $id_rental)->first();
        $invoice = Invoice::where('id_rental', $id_rental)->first();
        $meja_rental2 = RentalInvoice::where('id_rental', $id_rental)->get();
        $rental = RentalInvoice::where('id_rental', $id_rental)->count();
        $no_meja = $meja_rental->no_meja;
        $tanggalmain = Carbon::parse($meja_rental->waktu_akhir)->format('d-m-Y');

        if ($meja_rental) {
            // Make sure to use the correct id reference for Orders
            $makanan = Order::where('id_table', $invoice->id_belanja)
                            ->with('items')
                            ->get();

            $idplayer = substr($meja_rental->id_player, 0, 1);

            if ($idplayer == 'M') {
                $mejatotal = 0;
                $lama_waktu = '00:00:00';
            } else {
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                $lama_waktu = $meja_rental->lama_waktu ?? '00:00:00'; // Correctly get the lama_waktu

                // Handle case when elapsedSeconds is provided via query parameter
                if (!$lama_waktu || $lama_waktu == '00:00:00') {
                    $elapsedSeconds = request()->query('elapsed');

                    if ($elapsedSeconds !== null) {
                        $hours = floor($elapsedSeconds / 3600);
                        $minutes = floor(($elapsedSeconds % 3600) / 60);
                        $seconds = $elapsedSeconds % 60;

                        $lama_waktu = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    } else {
                        $lama_waktu = '00:00:00';
                    }
                }

                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                // Initialize default per-minute pricing
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;

                if (in_array($no_meja, [1, 2])) {
                    // Harga khusus meja 1 dan 2 (Rp 60.000 per jam)
                    $mejatotal = ($total_minutes / 60) * 50000;
                } else {
                    // Hitung harga berdasarkan per menit atau paket
                    $mejatotal = $total_minutes * $harga_per_menit;
            
                    // Iterasi melalui paket untuk mendapatkan harga terbaik
                    $paket = Paket::orderBy('jam', 'asc')->get();
                    $best_price = null; // Default to calculated per-minute price
                    foreach ($paket as $p) {
                        // Convert package time ($p->jam) to minutes
                        $package_minutes = (substr($p->jam, 0, 2) * 60) + substr($p->jam, 3, 2);
                        
                        // Check if the input time (total_minutes) exceeds or is equal to the package time
                        if ($total_minutes == $package_minutes) {
                            $best_price = $p->harga; // Update the best price to this package's price
                        }
                    }
                    $mejatotal = $best_price !== null ? $best_price : $mejatotal;
                }
            }

            // Calculate the total for all food items
            $total_makanan = $makanan->flatMap(function($order) {
                return $order->items;
            })->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // Total biaya keseluruhan
            $diskon = $meja_rental->diskon;
            $totalmeja = $mejatotal - ($mejatotal * ( $diskon / 100));
            $total = $totalmeja + $total_makanan;
            $total = round($total);
            $invoice->update([
                "harga_table"=>$mejatotal,
                "harga_cafe"=>$total_makanan
            ]);
            return view('invoice.struk', compact("invoice",'meja_rental', 'meja_rental2', 'no_meja', 'rental', 'lama_waktu', 'mejatotal', 'total', 'makanan',"tanggalmain", "diskon"));
        } else {
            return redirect()->back()->with('error', 'No rental found for the specified table.');
        }
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
    
        $meja_rental = $meja->map(function($m) use ($rental) {
            $invoice = $rental->firstWhere('no_meja', $m->nomor);
            return [
                'nomor_meja' => $m->nomor,
                'waktu_mulai'=> $invoice && $invoice->waktu_mulai ? $invoice->waktu_mulai->format('Y-m-d H:i:s') : null,
                'waktu_akhir' => $invoice && $invoice->waktu_akhir ? $invoice->waktu_akhir->format('Y-m-d H:i:s') : null,
                'status' => $invoice ? $invoice->status : null // Tambahkan status
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
        $meja_rental = Rental::where('no_meja', $no_meja)->first();
        $meja_rental2 = Rental::where('no_meja', $no_meja)->get();
        $rental = Rental::where('no_meja', $no_meja)->count();
        
        if ($meja_rental) {
            $makanan = Order::where('id_table', $meja_rental->id)
                            ->where('status', 'belum')
                            ->with('items')->get();

            $idplayer = substr($meja_rental->id_player, 0, 1);

            if ($idplayer == 'M') {
                $mejatotal = 0;
                $lama_waktu = '00:00:00';
            } else {
                $hargarental = HargaRental::where('jenis', 'menit')->first();
                if($meja_rental->status == "lanjut"){
                    $lama_waktu = request()->query('lama_main', '00:00:00'); // Get 'lama_main' from URL
                }else{
                    $lama_waktu = $meja_rental->lama_waktu;
                }
                

                // No need to calculate elapsed time since we directly use 'lama_main'
                list($hours, $minutes, $seconds) = sscanf($lama_waktu, '%d:%d:%d');
                $total_minutes = $hours * 60 + $minutes + $seconds / 60;

                // Initialize default per-minute pricing
                $harga_per_menit = $hargarental ? $hargarental->harga : 0;
                
                if (in_array($no_meja, [1, 2])) {
                    // Harga khusus meja 1 dan 2 (Rp 60.000 per jam)
                    $mejatotal = ($total_minutes / 60) * 50000;
                } else {
                    // Hitung harga berdasarkan per menit atau paket
                    $mejatotal = $total_minutes * $harga_per_menit;
            
                    // Iterasi melalui paket untuk mendapatkan harga terbaik
                    $paket = Paket::orderBy('jam', 'asc')->get();
                    $best_price = null; // Default to calculated per-minute price
                    foreach ($paket as $p) {
                        if ($lama_waktu == $p->jam) {
                            $best_price = $p->harga;
                            break;
                        }
                    }
                    $mejatotal = $best_price !== null ? $best_price : $mejatotal;
                }
            }

            // Total biaya keseluruhan
            // Calculate the total for all food items
            $total_makanan = $makanan->flatMap(function($order) {
                return $order->items;
            })->sum(function($item) {
                return $item->price * $item->quantity;
            });

            // Total biaya keseluruhan
            $total = $mejatotal + $total_makanan;
            $total = round($total);
            return view('invoice.stop', compact('meja_rental', 'meja_rental2', 'no_meja', 'rental', 'makanan', 'total', 'lama_waktu', 'mejatotal'));
        }
        
        return abort(404);
    }


    public function bayar(Request $request)
    {
        // Validasi request
        $validated = $request->validate([
            'no_meja' => 'required|string',
            'lama_waktu' => 'required|string',
            'metode' => 'string',
            'diskon' => 'string'
        ]);

        try {
            // Ambil data meja rental berdasarkan no_meja
            $meja_rental = Rental::where('no_meja', $validated['no_meja'])->firstOrFail();
            \Log::info('Processing payment for table', ['no_meja' => $validated['no_meja'], 'lama_waktu' => $validated['lama_waktu']]);

            // Cek dan siapkan variabel
            $lama_waktu = $validated['lama_waktu'];
            $waktu_mulai = $meja_rental->waktu_mulai;

            if(is_null($meja_rental->waktu_akhir)){
                $waktuMulai = new \DateTime($meja_rental->waktu_mulai);
                // Memisahkan lamaWaktu menjadi hours dan minutes
                list($hours, $minutes, $seconds) = explode(':', $lama_waktu);
                $intervalString = 'PT' . $hours . 'H' . $minutes . 'M' . $seconds . 'S';  // Membuat interval waktu dengan format 'PTxHxMxS'
                $interval = new \DateInterval($intervalString);
                
                // Menambahkan interval ke tanggalMain
                $waktuMulai->add($interval);
                $waktu_akhir = $waktuMulai->format('Y-m-d H:i:s');
            }else{
                $waktu_akhir = $meja_rental->waktu_akhir;
            }
            
            // $waktu_akhir = $meja_rental->waktu_akhir jika kosong maka waktu_mulai + dengan lama_waktu;
            $no_meja = $meja_rental->no_meja;
            $id_player = $meja_rental->id_player;

            // Generasikan id_rental unik
            do {
                $id_rental = 'R' . rand(1, 1000000000);
            } while (RentalInvoice::where('id_rental', $id_rental)->exists());

            // Simpan data RentalInvoice
            RentalInvoice::create([
                'id_rental' => $id_rental,
                'lama_waktu' => $lama_waktu,
                'waktu_mulai' => $waktu_mulai,
                'waktu_akhir' => $waktu_akhir,
                'no_meja' => $no_meja,
                'metode' => $validated['metode'],
                'diskon' => $validated['diskon'],
            ]);

            // Update status 
            if (Order::where('id_table', $meja_rental->id)->where('status', 'belum')->exists()) {
                $orders = Order::where('id_table', $meja_rental->id)->where('status', 'belum')->get();
                foreach ($orders as $order) {
                    // Update order status
                    // $order->update(['status' => 'lunas']);
    
                    // Loop through each item in the order to adjust product stock
                    foreach ($order->items as $item) {
                        // Log the product name being processed
                        Log::info('nama produk', ['nama_produk' => $item['product_name']]);
                    
                        // Find the product using the 'nama_produk' field and the item name
                        $produk = Produk::where('nama_produk', $item['product_name'])->first();
                    
                        if ($produk) {
                            // Log the initial stock quantity
                            Log::info('Initial QTY', ['produk' => $produk->qty]);
                    
                            // Decrease stock by the quantity ordered
                            $produk->qty -= $item['quantity'];
                    
                            // Save the updated product quantity to the database
                            $produk->save();
                    
                            // Log the updated stock quantity
                            Log::info('Updated QTY', ['produk' => $produk->qty]);
                        } else {
                            // Log an error if the product is not found
                            Log::error('Product not found', ['nama_produk' => $item['product_name']]);
                        }
                    }
                }
                $orderss = $orders->first()->id_table;
            } else {
                $orderss = NULL;
            }

            // Simpan data Invoice
            Invoice::create([
                'id_player' => $id_player,
                'id_rental' => $id_rental,
                'id_belanja' => $orderss,
                'user_id' => Auth::user()->id
            ]);
            

            // Hapus data meja rental
            $meja_rental->delete();

            // Kembalikan respons sukses dengan no_meja
            return response()->json(['success' => true, 'id_rental' => $id_rental, "id_table" => $validated['no_meja']]);

        } catch (\Exception $e) {
            // Tangkap dan log kesalahan
            \Log::error('Error in bayar function:', ['error' => $e->getMessage(), 'id_rental' => $id_rental]);
            return response()->json(['success' => false, 'error' => 'There was an error processing your request.'], 500);
        }
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
        
        $b = Rental::create([
            'id_player' => $request->nama,
            'lama_waktu' => $request->lama_waktu,
            'waktu_mulai' => now(),
            'waktu_akhir' => $waktuAkhir,
            'no_meja' => $request->no_meja
        ]);
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

        $a = NonMember::create([
            'id' => $id_non,
            'nama' => $request->nama,
            'no_telp' => $request->no_telp
        ]);
        $b = Rental::create([
            'id_player' => $id_non,
            'lama_waktu' => $request->lama_waktu,
            'waktu_mulai' => Carbon::now('Asia/Jakarta'),
            'waktu_akhir' => $waktuAkhir,
            'no_meja' => $request->no_meja
        ]);
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