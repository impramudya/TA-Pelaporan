<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("dashboard.categories.index", [
            "title" => "Categories",
            "categories" => Category::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("dashboard.categories.create", [
            "title" => "Categories",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            "name" => ["required", "max:255"],
            "slug" => ["required", "unique:categories"],
        ]);

        try {
            Category::create($credentials);
            return redirect('/dashboard/categories/')->with('success', 'Kategori berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect('/dashboard/categories')->withErrors('Kategori gagal dibuat.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view("dashboard.categories.edit", [
            "title" => $category->name,
            "category" => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $rules = [
            "name" => ["required", "max:255"],
        ];

        if ($request->slug !== $category->slug) {
            $rules["slug"] = ["required", "unique:categories"];
        }

        $credentials = $request->validate($rules);

        try {
            // $category = Category::where("id", $category->id)->update($credentials);
            // Get the new and old of $category
            $categoryOld = $category->fresh();
            $category->update($credentials);
            $categoryNew = $category->fresh();

            // Get the old and new versions of the model as arrays
            $oldAttributes = $categoryOld->getAttributes();
            $newAttributes = $categoryNew->getAttributes();

            // Compare the arrays to see if any attributes have changed
            if ($oldAttributes === $newAttributes) {
                // The instance of the $category record has not been updated
                return redirect('/dashboard/categories')->with('info', 'Kamu tidak melakukan editing pada kategori.');
            }

            // The instance of the $category record has been updated
            return redirect('/dashboard/categories/')->with('success', 'Kategori kamu berhasil di-edit!');
        } catch (\Exception $e) {
            // If something was wrong ...
            return redirect('/dashboard/categories')->withErrors('Kategori kamu gagal di-edit.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            if (!Category::destroy($category->id)) {
                throw new \Exception('Error deleting category.');
            }
        } catch (QueryException $e) {
            // On delete, then restrict
            if ($e->errorInfo[1] === 1451) {
                return response()->json([
                    "message" => "Tidak bisa menghapus kategori karena masih ada keluhan yang menggunakan kategori ini.",
                ], 422);
            }
        } catch (\PDOException | ModelNotFoundException | \Exception $e) {
            return response()->json([
                "message" => "Gagal menghapus kategori.",
                "error" => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            // Catch all exceptions here
            return response()->json([
                "message" => "An error occurred: " . $e->getMessage()
            ], 500);
        }

        return response()->json([
            "message" => "Kategori telah dihapus!",
        ], 200);
    }

    public function checkSlug(Request $request)
    {
        $slug = SlugService::createSlug(Category::class, "slug", $request->name);

        return response()->json(["slug" => $slug]);
    }
}