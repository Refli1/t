<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //cari data dan tampilkan pagination
        $products = DB::table('products')
            ->when($request->input('name'), function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('pages.product.index', compact('products'));
    }

    //hapus data
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Product deleted successfully');
    }

    //tampilan create product
    public function create()
    {
        return view('pages.product.create');
    }

    //membuat data
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stok' => 'required|integer',
            'category' => 'required|in:food,drinks,snacks',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stok' => $request->stok,
            'category' => $request->category,
            'image' => $imagePath,
        ]);

        return redirect()->route('product.index')->with('success', 'Product berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        return view('pages.product.edit', compact('product'));
    }


    // Menyimpan perubahan data produk
    public function update(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stok' => 'required|integer',
            'category' => 'required|in:food,drinks,snacks',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Temukan produk berdasarkan ID
        $product = Product::findOrFail($id);

        // Update gambar jika ada file baru yang diupload
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Upload gambar baru dan simpan path-nya
            $imagePath = $request->file('image')->store('products', 'public');
        } else {
            // Gunakan gambar lama jika tidak ada gambar baru yang diupload
            $imagePath = $product->image;
        }

        // Update data produk
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stok' => $request->stok,
            'category' => $request->category,
            'image' => $imagePath,
        ]);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('product.index')->with('success', 'Product berhasil diperbarui!');
    }

}
