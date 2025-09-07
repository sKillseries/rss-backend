<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Favorite;

class ArticleController extends Controller
{
    // Listes des articles avec filtres (catégorie, recherche, tri).

    public function index(Request $request)
    {
        $query = Article::query();

        // Filtre par catégorie
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Recherche par mots-clés (dans titre + description)
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}$");
            });
        }

        // Tri dynamique
        if ($request->has('sort') && !empty($request->sort)) {
            $sort = $request->sort;
            if (in_array($sort, ['title', 'source', 'pub_date'])) {
                $query->orderBy($sort, 'asc');
            }
        } else {
            // Tri par défaut : date DESC
            $query->orderBy('pub_date', 'desc');
        }

        // Récupération
        $articles = $query->get();
        return response()->json($articles);

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
