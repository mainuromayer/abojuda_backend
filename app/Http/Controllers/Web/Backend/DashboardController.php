<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use App\Models\ContactSupport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     *  show data
     */
    public function index()
    {
        $metrics = [
            'total_users' => User::whereNot('role', 'admin')->count(),
            'total_posts' => Post::count(),
            'active_posts' => Post::where('status', 'active')->count(),
            'pending_posts' => Post::where('status', 'pending')->count(),
            'rejected_posts' => Post::where('status', 'rejected')->count(),
            'total_categories' => Category::count(),
            'total_reports' => Report::count(),
            'unread_reports' => Report::where('is_read', false)->count(),
            'total_supports' => ContactSupport::count(),
        ];

        return view('backend.dashboard', compact('metrics'));
    }

    public function globalSearch(Request $request)
    {
        $query = trim($request->input('query'));
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Categories
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->orWhere('shot_desc', 'LIKE', "%{$query}%")
            ->take(5)->get();

        foreach ($categories as $cat) {
            $results[] = [
                'type' => __('Categories'),
                'title' => __($cat->name),
                'url' => route('category.index'),
                'icon' => 'ki-element-plus',
                'sub' => $cat->shot_desc ? __($cat->shot_desc) : __('Category')
            ];
        }

        // Search Posts
        $posts = Post::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->take(5)->get();

        foreach ($posts as $post) {
            $results[] = [
                'type' => __('Posts'),
                'title' => __($post->title),
                'url' => route('post.show', $post->id),
                'icon' => 'ki-abstract-26',
                'sub' => $post->created_at ? $post->created_at->format('d M Y') : ''
            ];
        }

        // Search Users
        $users = User::where('first_name', 'LIKE', "%{$query}%")
            ->orWhere('last_name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->take(5)->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => __('Users'),
                'title' => trim($user->first_name . ' ' . $user->last_name) ?: $user->email,
                'url' => route('user.show', $user->id),
                'icon' => 'ki-profile-circle',
                'sub' => $user->email
            ];
        }

        return response()->json(['results' => $results]);
    }
}
