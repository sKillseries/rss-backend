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
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['error' => 'Article introuvable'], 404);
        }

        if ($article->is_read) {
            return response()->json(['message' => 'Déjà marqué comme lu'], 200);
        }

        $article->is_read = true;
        $article->save();

        return response()->json([
            'message' => 'Article marqué comme lu',
            'article' => $article
        ], 200);
    }

    public function addFavorite($id)
    {
        $article = Article::find($id);


        if (!$article) {
            return response()->json(['error' => 'Article introuvable'], 404);
        }

        if ($article->is_favorite) {
            return response()->json(['message' => 'Déjà présent dans les favoris'], 200);
        }

        $article->is_favorite = true;
        $article->save();

        $favorite = Favorite::firstOrCreate(['article_id' => $id]);

        return response()->json([
            'message' => $favorite->wasRecentlyCreated
                ? 'Ajouté aux favoris'
                : 'Déjà présent dans les favoris',
            'favorite' => $favorite
        ], 200);
    }

    public function listFavorites()
    {
        return Favorite::with('article')->get();
    }

    public function removeFavorite($id)
    {
        $favorite = Favorite::find($id);

        if (!$favorite) {
            return response()->json(['error' => 'Favori introuvable'], 404);
        }

        // Mettre à jour l'article associé
        $article = Article::find($favorite->article_id);
        if ($article) {
            $article->is_favorite = false;
            $article->save();
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Favori supprimé avec succès',
            'article' => $article
        ], 200);
    }
}
