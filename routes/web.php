<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


// Routes Stripe Connect
Route::get('return', function () {
    //$account = auth()->user()->retrieveStripeAccount();
    //auth()->user()->setStripeAccountStatus($account->details_submitted)->save();

    // Accede al usuario autenticado correctamente con auth()->user()
    /** @var User $user */
     $id = Auth::user()->id;
     $user = User::find($id);
      // Asegúrate de que el usuario esté autenticado antes de proceder
    if ( $user ) {
        $user->stripe_account_active = true;
        $user->save();

         return Route::has(Config::get('stripe_connect.routes.account.complete'))
            ? Response::redirectToRoute(Config::get('stripe_connect.routes.account.complete'))
            : Response::redirectTo('/');
    } else {
        // Manejar el caso en que el usuario no está autenticado (opcional)
        return Response::redirectTo('/login'); // Ejemplo de redirección
    }
})->name('stripe-connect.return');
    

Route::get('refresh', function () {
     /** @var User $user */
    $user = Auth::user();
    return Response::redirectTo($user->getStripeAccountLink());
})->name('stripe-connect.refresh');
   

// Guest routes
Route::get('/',[ProductController::class, 'home'])->name('dashboard');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::controller(CartController::class)->group(function() {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/add/{product}', 'store')->name('cart.store');
    Route::put('/cart/{product}', 'update')->name('cart.update');
    Route::delete('/cart/{product}', 'destroy')->name('cart.destroy');
});

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');


// Auth routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['verified'])->group(function () {
        Route:: post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('/stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');

        Route::post('/become-a-vendor', [VendorController::class, 'store'])->name('vendor.store');

         Route::post('/stripe/connect', [StripeController::class, 'connect'])
            ->name('stripe.connect')
            ->middleware(['role:' . \App\Enums\RolesEnum::Vendor->value]);

    });

   

});

require __DIR__.'/auth.php';
