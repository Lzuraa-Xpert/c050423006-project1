<?php

namespace App\Http\Controllers;

// Import model Product
use App\Models\Product;

// Import return type View
use Illuminate\View\View;

// Import return type RedirectResponse
use Illuminate\Http\RedirectResponse;

// Import Request
use Illuminate\Http\Request;

// Import Storage
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * index
     * 
     * @return View
     */
    public function index(): View
    {
        // get all products
        $products = Product::latest()->paginate(10);

        // render view with products
        return view('products.index', compact('products'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * store
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // validate form
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        // upload imageaz
        $image = $request->file('image');
        $image->storeAs('products', $image->hashName(), 'public');

        // create product
        Product::create([
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock
        ]);

        // redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id): View
    {
        //get product by ID
        $product = Product::findOrFail($id);

        //render view with product
        return view('products.show', compact('product'));
    }

    public function edit(string $id): View // Pastikan tipe data sesuai
    {
        // Dapatkan produk berdasarkan ID
        $product = Product::findOrFail($id);

        // Tampilkan view edit dengan data produk
        return view('products.edit', compact('product'));
    }

    /**
     * update
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse // Pastikan tipe data sesuai
    {
        // Validasi form
        $request->validate([
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // 'nullable' untuk image
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        // Dapatkan produk berdasarkan ID
        $product = Product::findOrFail($id);

        // Cek apakah gambar diunggah
        if ($request->hasFile('image')) {

            // Upload gambar baru
            $image = $request->file('image');
            $image->storeAs('products', $image->hashName(), 'public');

            // Hapus gambar lama
            Storage::delete('public/products/'.$product->image);

            // Update produk dengan gambar baru
            $product->update([
                'image'         => $image->hashName(),
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);

        } else {

            // Update produk tanpa gambar
            $product->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);
        }

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Diubah!']);

    }

    public function destroy(string $id): RedirectResponse
    {

        //get product by ID
        $product = Product::findOrFail($id);

        //delete image
        Storage::delete('public/products/'. $product->image);

        //delete product 
        $product->delete();

        //redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

}