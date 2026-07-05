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
}
