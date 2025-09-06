<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Favorite;

class ArticleController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Article::orderBy('pub_date', 'desc');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return $query->get();

        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->q}%")
                  ->orWhere('description', 'like', "%{$request->q}$");
            });
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            if (in_array($sort, ['title', 'source', 'pub_date'])) {
                $query->orderBy($sort, 'asc');
            }
        }

    }

    public function markAsRead($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['is_read' => true]);
        return response()->json(['message' => 'Article marqué comme lu']);
    }

    public function addFavorites($id)
    {
        Favorite::firstOrCreate(['article_id' => $id]);
        return response()->json(['message' => 'Ajouté aux favoris']);
    }

    public function listFavorites()
    {
        return Favorite::with('article')->get();
    }
}
