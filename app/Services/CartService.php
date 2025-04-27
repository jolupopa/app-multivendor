<?php

namespace App\Services;


use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Str;
use App\Models\VariationType;
use Illuminate\Support\Facades\DB;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CartService
{
    private ?array $cachedCartItems = null;

    protected const COOKIE_NAME = 'cartItems';

    protected const COOKIE_LIFETIME = 60 * 24 * 365; // 1 year

    public function addItemToCart(Product $product, int $quantity = 1, $optionIds = null)
    {
        if ( $optionIds === null) {
            $optionIds = $product->variationTypes
                        ->mapWithKeys(fn(VariationType $type) => [$type->id => $type->options[0]?->id])
                        ->toArray();
        }

        $price = $product->getPriceForOptions($optionIds);

         // Verify product exists before adding to cart.
         /////////////////////////////
         if (!Product::where('id', $product->id)->exists()) {
            return; // Or throw an exception, or return an error message.
        }
        //////////////////////////////

        if ( Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        if ( Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    public function removeItemFromCart(int $productId, $optionIds = null)
    {
        if ( Auth::check()) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    public function getCartItems(): array
    {
        // We need to put this in try-catch, otherwise if something gues wrong,
        // the website will not open at all.
        try {
            if ($this->cachedCartItems  === null) {

                if (Auth::check()) {
                    // If the user is authenticated, retrieve from the database
                    $cartItems = $this->getCartItemsFromDatabase();
                }
                else {
                    // If the user is a guest, retrieve from cookies
                    $cartItems = $this->getCartItemsFromCookies();
                    // dd($cartItems);
                }

                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);

                $products = Product::whereIn('id', $productIds)->with('user.vendor')->forWebsite()->get()->keyBy('id');

                $cartItemData = [];

                foreach ($cartItems as $key => $cartItem) {
                    $product = data_get($products, $cartItem['product_id']);
                    if ( !$product) continue;

                    $optionInfo = [];
                    $options = VariationTypeOption::with('variationType')->whereIn('id', $cartItem['option_ids'])->get()->keyBy('id');

                    $imageUrl = null;

                    foreach ($cartItem['option_ids'] as $option_id) {
                        $option = data_get($options, $option_id);
                        if (!$imageUrl) {
                            $imageUrl = $option->getFirstMediaUrl('images', 'small' );
                        }
                        $optionInfo[] = [
                            'id' => $option_id,
                            'name' => $option->name,
                            'type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name,
                            ],

                        ];
                    }
                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'],
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('images', 'small'),
                        'user' => [
                            'id' =>$product->created_by,
                            'name' => $product->user->vendor->store_name,
                        ],


                    ];
                }

                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;

        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return [];
    }

    public function getTotalQuantity(): int
    {
        // $totalQuantity = 0;
        // foreach ($this->getCartItems() as $item) {
        //     $totalQuantity += $item['quantity'];
        // }

        // return $totalQuantity;

        $cartItems = $this->getCartItems();
        if (empty($cartItems)) {
            return 0;
        }

        return collect($cartItems)->sum('quantity');
    }

    public function getTotalPrice(): float
    {
        //$total = 0;
        // Assuming $this->getCartItems() return an array of cart items
        // foreach ($this->getCartItems() as $item) {
        //     $total += $item['quantity'] * $item['price'];
        // }

        // return $total;

        $cartItems = $this->getCartItems();
        if (empty($cartItems)) {
            return 0.0;
        }

        return collect($cartItems)->sum(fn($item) => $item['quantity'] * $item['price']);


    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds): void
    {
        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)
                                ->where('product_id', $productId)
                                ->where('variation_type_option_ids', json_encode($optionIds))
                                ->first();
        if( $cartItem ) {
            $cartItem->update([
                'quantity' => $quantity
            ]);
        }
    }

    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort( $optionIds);
        // Use a unique key based on product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        if ( isset($cartItems[$itemKey]) ) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }

        // save update cart items back to the cookie
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds): void
    {
        $userId = Auth::id();
        ksort( $optionIds);

        $cartItem = CartItem::where('user_id', $userId)
                                ->where('product_id', $productId)
                                ->where('variation_type_option_ids', json_encode($optionIds))
                                ->first();

        if( $cartItem ) {
            $cartItem->update([
                'quantity' => DB::raw('quantity + ' . $quantity),
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,

            ]);

        }

    }

    protected function saveItemToCookies(int $productId, int $quantity, $price, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();

        //dd($cartItems, $productId, $quantity, $price, $optionIds);

        ksort( $optionIds);

        //use a unique key based on product ID and option IDs

        $itemKey = $productId . '_' . json_encode($optionIds);

        if ( isset($cartItems[$itemKey]) ) {
            $cartItems[$itemKey]['quantity'] = $quantity;
            $cartItems[$itemKey]['price'] = $price;
        } else {
            $cartItems[$itemKey] = [
                'id' => Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds,
            ];

        }
        // save updated cart items back to the cookie
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);

    }

    protected function removeItemFromDatabase(int $productId, array $optionIds): void
    {
        $userId = Auth::id();
        ksort( $optionIds);
        CartItem::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->where('variation_type_option_ids', json_encode($optionIds))
                    ->delete();
    }

    protected function removeItemFromCookies(int $productId, array $optionIds): void
    {
      //$cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
        $cartItems = $this->getCartItemsFromCookies();

        ksort( $optionIds);
        // define the cart key
        $cartKey = $productId . '_' . json_encode($optionIds);
        // remove the item from the cart
        unset($cartItems[$cartKey]);

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();

        $cartItems = CartItem::where('user_id', $userId)
                                ->get()
                                ->map(function($cartItem) {
                                    return [
                                        'id' => $cartItem->id,
                                        'product_id' => $cartItem->product_id,
                                        'quantity' => $cartItem->quantity,
                                        'price' => $cartItem->price,
                                        'option_ids' => $cartItem->variation_type_option_ids,
                                    ];
                                })
                                ->toArray();
        return $cartItems;
    }

    protected function getCartItemsFromCookies()
    {
        $cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);

        return $cartItems;
    }

    public function getCartItemsGrouped(): array
    {
        $cartItems = $this->getCartItems();

        return collect($cartItems)
            ->groupBy(fn($item) => $item['user']['id'])
            ->map(fn($items, $userId) => [
                'user' => $items->first()['user'],
                'items' => $items->toArray(),
                'totalQuantity' => $items->sum('quantity'),
                'totalPrice' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
            ])
            ->toArray();
    }

    public function moveCartItemsTodatabase($userId): void
    {
        // Get the cart items from the cookie
        $cartItems = $this->getCartItemsFromCookies();

        // Loop through the cart items and insert them into the database
        foreach ($cartItems as $itemKey => $cartItem) {
            
            ///////////////////////
            if (!Product::where('id', $cartItem['product_id'])->exists()) {
                continue; // Skip if product doesn't exist.
            }
            //////////////////////
            //Check if the cart item alrady exist for the user
            $existingItem = CartItem::where('user_id', $userId)
                                ->where('product_id', $cartItem['product_id'])
                                ->where('variation_type_option_ids', json_encode($cartItem['option_ids']))
                                ->first();


            if( $existingItem ) {
                // If the iten exist, update the quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $cartItem['quantity'],
                    'price' => $cartItem['price'], // Optional update price if needed
                ]);
            } else {
                    // If the iten doesn't exist, create a new record
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'variation_type_option_ids' => $cartItem['option_ids'],

                ]);
            }

        }

        // After transferinring the items, delete the cart from the cookies
        Cookie::queue(self::COOKIE_NAME, '', -1); // Delete cookie by setiiing

    }

}
