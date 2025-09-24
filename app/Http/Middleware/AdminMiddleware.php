<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifie si un utilisateur est connecté ET si cet utilisateur est un administrateur.
        // La logique 'is_admin' est définie dans le modèle User.
        if (!auth()->check() || !auth()->user()->is_admin) {
            // Si l'utilisateur n'est pas admin, il est redirigé ou reçoit une erreur 403.
            // Pour une expérience utilisateur plus fluide, vous pourriez rediriger vers le dashboard ou une page d'erreur spécifique.
            abort(403, 'Accès administrateur non autorisé.');
        }

        return $next($request);
    }
}
