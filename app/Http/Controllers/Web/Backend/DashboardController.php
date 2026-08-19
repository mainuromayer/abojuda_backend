<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use App\Models\ContactSupport;
use App\Models\Ad;
use App\Models\DynamicPage;
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
        $categories = Category::where(function($q) use ($query) {
            $q->where('name->en', 'LIKE', "%{$query}%")
              ->orWhere('name->ar', 'LIKE', "%{$query}%")
              ->orWhere('shot_desc->en', 'LIKE', "%{$query}%")
              ->orWhere('shot_desc->ar', 'LIKE', "%{$query}%");
        })->take(5)->get();

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
        $posts = Post::where(function($q) use ($query) {
            $q->where('title->en', 'LIKE', "%{$query}%")
              ->orWhere('title->ar', 'LIKE', "%{$query}%")
              ->orWhere('description->en', 'LIKE', "%{$query}%")
              ->orWhere('description->ar', 'LIKE', "%{$query}%");
        })->take(5)->get();

        foreach ($posts as $post) {
            $results[] = [
                'type' => __('Posts'),
                'title' => __($post->title),
                'url' => route('post.show', $post->id),
                'icon' => 'ki-abstract-26',
                'sub' => $post->created_at ? $post->created_at->format('d M Y') : ''
            ];
        }

        // Search Ads
        $ads = Ad::where(function($q) use ($query) {
            $q->where('title->en', 'LIKE', "%{$query}%")
              ->orWhere('title->ar', 'LIKE', "%{$query}%");
        })->take(5)->get();

        foreach ($ads as $ad) {
            $results[] = [
                'type' => __('Ads'),
                'title' => __($ad->title),
                'url' => route('ad.index'),
                'icon' => 'ki-badge',
                'sub' => $ad->status ? __($ad->status) : ''
            ];
        }

        // Search Dynamic Pages
        $pages = DynamicPage::where(function($q) use ($query) {
            $q->where('title->en', 'LIKE', "%{$query}%")
              ->orWhere('title->ar', 'LIKE', "%{$query}%")
              ->orWhere('content->en', 'LIKE', "%{$query}%")
              ->orWhere('content->ar', 'LIKE', "%{$query}%");
        })->take(5)->get();

        foreach ($pages as $page) {
            $results[] = [
                'type' => __('Dynamic Pages'),
                'title' => __($page->title),
                'url' => route('dynamic-pages.index'),
                'icon' => 'ki-document',
                'sub' => $page->slug
            ];
        }

        // Search Users
        $usersQuery = User::query();
        $usersQuery->where(function($q) use ($query) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
            $first = true;
            if (in_array('name', $columns)) {
                $q->where('name', 'LIKE', "%{$query}%");
                $first = false;
            }
            if (in_array('first_name', $columns)) {
                if ($first) { $q->where('first_name', 'LIKE', "%{$query}%"); $first = false; }
                else { $q->orWhere('first_name', 'LIKE', "%{$query}%"); }
            }
            if (in_array('last_name', $columns)) {
                if ($first) { $q->where('last_name', 'LIKE', "%{$query}%"); $first = false; }
                else { $q->orWhere('last_name', 'LIKE', "%{$query}%"); }
            }
            if (in_array('email', $columns)) {
                if ($first) { $q->where('email', 'LIKE', "%{$query}%"); }
                else { $q->orWhere('email', 'LIKE', "%{$query}%"); }
            }
        });
        $users = $usersQuery->take(5)->get();

        foreach ($users as $user) {
            $displayName = '';
            if (isset($user->name)) {
                $displayName = $user->name;
            } elseif (isset($user->first_name) || isset($user->last_name)) {
                $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            }
            $results[] = [
                'type' => __('Users'),
                'title' => $displayName ?: $user->email,
                'url' => route('user.show', $user->id),
                'icon' => 'ki-profile-circle',
                'sub' => $user->email
            ];
        }

        return response()->json(['results' => $results]);
    }
}
