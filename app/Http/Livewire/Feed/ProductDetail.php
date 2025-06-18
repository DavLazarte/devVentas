<?php

namespace App\Http\Livewire\Feed;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Servicio;
use Illuminate\Support\Facades\Storage;
use TCG\Voyager\Facades\Voyager;

class ProductDetail extends Component
{
    public $product;
    public $type;
    public $isFavorite = false;
    public $currentImageIndex = 0;
    public $showFullDescription = false;
    public $quantity = 1;


    protected $listeners = ['$refresh'];

    public function mount($type, $id)
    {
        if ($type === 'articulo') {
            $this->product = Articulo::with(['categoria', 'local'])->findOrFail($id);
        } else {
            $this->product = Servicio::with(['categoria', 'local'])->findOrFail($id);
        }
        $this->type = $type;
    }

    protected function getProductImages()
    {
        $images = [];

        // Imagen principal
        if ($this->product->imagen) {
            $images[] = Storage::url($this->product->imagen);
        }

        // Imágenes adicionales
        if ($this->product->fotos_adicionales) {
            $additionalImages = json_decode($this->product->fotos_adicionales, true);
            if (is_array($additionalImages)) {
                foreach ($additionalImages as $image) {
                    $images[] = Storage::url($image);
                }
            }
        }

        return $images;
    }

    public function toggleFavorite()
    {
        // TODO: Implementar lógica de favoritos
        $this->isFavorite = !$this->isFavorite;
    }

    public function setCurrentImage($index)
    {
        $this->currentImageIndex = $index;
    }

    public function toggleDescription()
    {
        $this->showFullDescription = !$this->showFullDescription;
    }

    public function contactSeller()
    {
        // TODO: Implementar lógica de contacto
    }

    public function updateQuantity($newQuantity)
    {
        if ($newQuantity > 0) {
            $this->quantity = $newQuantity;
        }
    }

    public function addToCart($id, $quantity)
    {
        $cart = session()->get('cart', []);
        $itemKey = $this->type === 'articulo' ? 'product_' . $id : 'service_' . $id;

        // Verificar si el carrito está vacío
        if (empty($cart)) {
            $cart[$itemKey] = [
                'id' => $id,
                'type' => $this->type,
                'name' => $this->product->nombre,
                'price' => $this->product->precio_unitario,
                'quantity' => $quantity,
                'image' => $this->product->imagen_url,
                'shop' => $this->product->local->nombre,
                'shop_id' => $this->product->local->id
            ];
            session()->put('cart', $cart);
            $this->emit('cartItemAdded', count($cart));
            return;
        }

        // Obtener el ID del local del primer producto en el carrito
        $firstItem = reset($cart);
        $currentShopId = $firstItem['shop_id'];

        // Verificar si el producto que se intenta agregar es del mismo local
        if ($this->product->local->id !== $currentShopId) {
            $this->dispatchBrowserEvent('showAlert', [
                'type' => 'warning',
                'title' => '¡Atención!',
                'message' => 'Por favor, finaliza el pedido actual antes de agregar productos de otro local.'
            ]);
            return;
        }

        // Si es del mismo local, proceder con la adición
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'id' => $id,
                'type' => $this->type,
                'name' => $this->product->nombre,
                'price' => $this->product->precio_unitario,
                'quantity' => $quantity,
                'image' => $this->product->imagen_url,
                'shop' => $this->product->local->nombre,
                'shop_id' => $this->product->local->id
            ];
        }

        session()->put('cart', $cart);
        $this->emit('cartItemAdded', count($cart));
    }

    public function render()
    {
        return view('livewire.feed.product-detail')->layout('layouts.app');
    }
}
