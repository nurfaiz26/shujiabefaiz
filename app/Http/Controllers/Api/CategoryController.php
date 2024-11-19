<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\CategoryApiResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query();

        if(request()->has('limit')) {
            $categories->limit(request()->input('limit'));
        }

        return CategoryApiResource::collection($categories->get());
    }

    public function show(Category $category)
    {
        $category->load(['homeSercvice']);

        return new CategoryApiResource($category);
    }
}
