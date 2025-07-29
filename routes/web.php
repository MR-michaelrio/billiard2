<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BilliardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\HargaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\AuthController;

use App\Models\RentalInvoice;
use App\Models\Rental;
use App\Models\Order;
use App\Models\Member;
use App\Models\NonMember;

use Carbon\Carbon;

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        date_default_timezone_set('Asia/Jakarta');
        $todayDate = Carbon::today()->toDateString();
        $today_order = RentalInvoice::whereDate('waktu_mulai',$todayDate)->count();
        $member = Member::all()->count();
        $nonmember = NonMember::all()->count();

        return view('index',compact('today_order','member','nonmember'));
    })->name("index");

    Route::get('/print-receipt/{id_rental}', [BilliardController::class, 'print'])->name('print.receipt');
    Route::get('/print-receiptrekap/{id_rental}', [BilliardController::class, 'printrekap'])->name('print.receiptrekap');

    Route::resource('bl', BilliardController::class);
   
    Route::get('bl/menu/{id}', [BilliardController::class, 'menu'])->name('bl.menu');
    Route::get('bl/nonmember/{id}', [BilliardController::class, 'nonmember'])->name('bl.nonmember');
    Route::get('/print', [BilliardController::class, 'print']);

    //member
    Route::get('bl/menumember/{id}', [BilliardController::class, 'menumember'])->name('bl.menumember');
    Route::get('bl/memberlanjutan/{id}', [BilliardController::class, 'memberlanjutan'])->name('bl.memberlanjutan');
    Route::get('bl/memberperwaktu/{id}', [BilliardController::class, 'memberperwaktu'])->name('bl.memberperwaktu');
    Route::post('bl/member/post', [BilliardController::class, 'storemember'])->name('bl.storemember');
    Route::post('bl/member/post2', [BilliardController::class, 'storemember2'])->name('bl.storemember2');

    //non-member
    Route::get('bl/menunonmember/{id}', [BilliardController::class, 'menunonmember'])->name('bl.menunonmember');
    Route::get('bl/nonmemberlanjutan/{id}', [BilliardController::class, 'nonmemberlanjutan'])->name('bl.nonmemberlanjutan');
    Route::get('bl/nonmemberperwaktu/{id}', [BilliardController::class, 'nonmemberperwaktu'])->name('bl.nonmemberperwaktu');
    Route::post('bl/nonmember/post', [BilliardController::class, 'storenonmember'])->name('bl.storenonmember');
    Route::post('bl/nonmember/post2', [BilliardController::class, 'storenonmember2'])->name('bl.storenonmember2');

    Route::get('/stop/{nomor_meja}', [BilliardController::class, 'stop'])->name('bl.stop');

    Route::post('bl/bayar/', [BilliardController::class, 'bayar'])->name('bl.bayar');

    Route::resource('produk', ProdukController::class);
    Route::get('pr/stok', [ProdukController::class, 'stok'])->name('pr.stok');

    Route::get('rekap-table', [BilliardController::class, 'rekaptable'])->name('rekap.table');
    // Route to show the token form and table
    Route::get('/rekaptable', [BilliardController::class, 'showRekapTablePage']);

    // Route to fetch the table data via AJAX
    Route::get('/rekaptable-data', [BilliardController::class, 'getRekapTableData']);

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders2', [OrderController::class, 'store2'])->name('orders.store2');
    Route::post('/orders3', [OrderController::class, 'store3'])->name('orders.store3');
    Route::get('/strukorder/{order_id}/{invoice_id}', [OrderController::class, 'struk'])->name('print.strukorder');
    Route::post('/print-receipt-status', [BilliardController::class, 'status'])->name('print.status');

    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/rekap-order', [OrderController::class, 'rekap'])->name('rekap.order');
    Route::get('/detail-order/{id}', [OrderController::class, 'detailorder'])->name('rekap.detailorder');
    Route::get('/search-names', [BilliardController::class, 'searchNames'])->name('search.names');
    Route::get('/rekap-bulan', [BilliardController::class, "rekapbulan"])->name("rekap.bulan");
    Route::get('/rekap-detailbulan/{bulan}', [BilliardController::class, "rekapdetailbulan"])->name("rekap.detailbulan");

});

Route::middleware(['auth'])->group(function () {
    Route::get('invoice/rekap', [BilliardController::class, 'rekapinvoice'])->name('bl.rekap');
    Route::get('invoice/showrekap/{id}', [BilliardController::class, 'showrekap'])->name('bl.showrekap');
    //member
    Route::resource('member', MemberController::class);

    //harga
    Route::resource('harga', HargaController::class);

    //paket
    Route::resource('paket', PaketController::class);
});
Route::post('logout', [HomeController::class, 'logout'])->name('logout');
